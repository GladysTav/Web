<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Sellacious\Cart;
use Sellacious\Cart\Item\Internal;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Form\CheckoutQuestionsFormHelper;
use Sellacious\User\UserHelper;

/**
 * Sellacious user plugin
 *
 * @since  1.5
 */
class PlgSystemSellacious extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 *
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var    JApplicationCms
	 *
	 * @since  3.1
	 */
	protected $app;

	/**
	 * @var    JDatabaseDriver
	 *
	 * @since  3.1
	 */
	protected $db;

	/**
	 * Remember me method to run onAfterInitialise
	 * Only purpose is to initialise the login authentication process if a cookie is present
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @throws  Exception
	 */
	public function onAfterInitialise()
	{
		$this->override();

		$token   = $this->app->input->getString('_');
		$support = $this->app->input->getCmd('_c', '');
		$uid     = $this->app->input->getInt('_u');
		$parts   = explode(':', $token);

		if ($support === 'support' && class_exists('SellaciousHelper'))
		{
			$helper = SellaciousHelper::getInstance();

			if ($helper->config->get('support_mode.enable'))
			{
				$passwd = $helper->config->get('support_mode.passwd', '');
				$uid    = $uid ?: $helper->config->get('support_mode.userid');

				if ($passwd === $token)
				{
					$sess = JFactory::getSession();
					$user = $uid ? JFactory::getUser($uid) : new JUser;

					$sess->set('user', $user);

					$this->app->redirect('index.php');
				}
				else
				{
					$this->app->enqueueMessage('Invalid password.', 'error');
				}
			}
		}
		elseif (count($parts) === 4)
		{
			list($ts, $uid, $salt, $hashed) = $parts;

			if (is_numeric($uid) && is_numeric($ts) && $uid > 0 && (time() - $ts) < 180)
			{
				$sess  = JFactory::getSession();
				$user  = JFactory::getUser($uid);
				$hash  = sha1($user->password . $salt . $ts);
				$match = strcmp($hash, $hashed) === 0;

				$sess->set('user', $match ? $user : new JUser);

				$this->app->redirect('index.php');
			}
		}
	}

	/**
	 * Adds user registration template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() == 'com_modules.module')
		{
			$arr    = (array) $data;
			$module = ArrayHelper::getValue($arr, 'module');
			$cid    = ArrayHelper::getValue($arr, 'client_id');
			$client = JApplicationHelper::getClientInfo($cid);

			if ($module)
			{
				$this->loadModuleLanguage($module, $client->path);
				$this->loadModuleLanguage($module . '.sys', $client->path);
			}

			return true;
		}

		if ($form->getName() != 'com_sellacious.emailtemplate')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchEmailContext('com_sellacious.emailtemplate', $contexts);

		if ($contexts)
		{
			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				if (strpos($array['context'], 'password_reset') !== false)
				{
					$form->loadFile(__DIR__ . '/forms/password_reset.xml', false);
				}
				else
				{
					$form->loadFile(__DIR__ . '/forms/user_activation.xml', false);

					if ($array['context'] == 'user_activation.admin')
					{
						$form->setFieldAttribute('short_codes', 'description', 'PLG_SYSTEM_SELLACIOUS_USER_ACTIVATION_FIELDSET_ADMIN_SHORTCODES_NOTE');
					}
					elseif ($array['context'] == 'user_activation.self')
					{
						$form->setFieldAttribute('short_codes', 'description', 'PLG_SYSTEM_SELLACIOUS_USER_ACTIVATION_FIELDSET_SELF_SHORTCODES_NOTE');
					}
				}

				$form->removeField('send_attachment');
			}
		}

		return true;
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['user_activation.admin'] = JText::_('COM_SELLACIOUS_EMAILTEMPLATE_USER_ACTIVATION_RECIPIENT_ADMIN');
			$contexts['user_activation.self']  = JText::_('COM_SELLACIOUS_EMAILTEMPLATE_USER_ACTIVATION_RECIPIENT_USER');
			$contexts['password_reset.self']   = JText::_('COM_SELLACIOUS_EMAILTEMPLATE_USER_PASSWORD_RESET_RECIPIENT_USER');
		}
	}

	/**
	 * This method sends a reminder email for non-activated users.
	 *
	 * @return  void
	 *
	 * @since   1.3.3
	 */
	public function onAfterRoute()
	{
		// How frequently to run this?
		jimport('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			try
			{
				$this->registerClientApp();

				$this->checkPendingActivation();

				$this->checkMediaDownloadQueue();
			}
			catch (Exception $e)
			{
			}
		}

		// Joomla 3.8 does not offer a onBeforeRenderModule event, so we are forced to do it here.
		if (JFactory::getDocument()->getType() === 'html')
		{
			$query = $this->db->getQuery(true);
			$query->select('DISTINCT module')
				  ->from('#__modules')
				  ->where('published = 1')
				  ->where('client_id = ' . (int) $this->app->getClientId());

			try
			{
				$modules = $this->db->setQuery($query)->loadColumn();

				foreach ($modules as $module)
				{
					$this->loadModuleLanguage($module);
				}
			}
			catch (Exception $e)
			{
			}
		}

		// Load component language in override order
		if ($option = $this->app->input->get('option'))
		{
			$lang = JFactory::getLanguage();

			$lang->load($option, JPATH_BASE . '/components/' . $option, null, true, true);
			$lang->load($option, JPATH_BASE, null, true, true);
		}
	}

	/**
	 * This method executes right before the application head is about to be rendered
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function onBeforeCompileHead()
	{
		// External item buttons need JS handler, and they can be in any page, not just sellacious
		if (JFactory::getDocument()->getType() === 'html')
		{
			try
			{
				$this->addHtmlHead();
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}
	}

	/**
	 * Method to fake the Active menu id right before the modules are to be loaded
	 *
	 * @param   stdClass[]  $modules  The modules list
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function onPrepareModuleList(&$modules)
	{
		$option = $this->app->input->getCmd('option');
		$view   = $this->app->input->getCmd('view');

		if ($option == 'com_sellacious' && $view == 'product')
		{
			$item      = null;
			$lang      = $this->app->input->getString('lang');
			$component = JComponentHelper::getComponent($option);

			$urls = array(
				'index.php?option=' . $option . '&view=product',
				'index.php?option=' . $option . '&view=sellacious',
				'index.php?option=' . $option . '',
			);

			foreach ($urls as $url)
			{
				$keys = array('component_id' => $component->id, 'link' => $url, 'language' => array($lang, '*'));
				$item = $this->app->getMenu()->getItems(array_keys($keys), array_values($keys), true);

				if (is_object($item))
				{
					break;
				}
			}

			if (!is_object($item))
			{
				$item = $this->app->getMenu()->getDefault();
			}

			if (is_object($item))
			{
				// We have menu for this view, lets use it
				$this->Itemid = $this->app->input->getInt('Itemid');

				$this->app->input->set('Itemid', $item->id);
			}
		}
	}

	/**
	 * Method to fake the Active menu id right before the modules are to be loaded
	 *
	 * @param   stdClass[]  $modules  The modules list
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function onAfterModuleList(&$modules)
	{
		if (isset($this->Itemid))
		{
			$this->app->input->set('Itemid', $this->Itemid);

			unset($this->Itemid);
		}
	}

	/**
	 * Method called after an item is added/modified to cart
	 *
	 * @param   string    $context  The calling context
	 * @param   Internal  $oldItem  Old version of the cart item object
	 * @param   Internal  $item     The cart item object (latest)
	 * @param   Cart      $cart     The cart object
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onAfterAddCartItem($context, $oldItem, $item, $cart)
	{
		if ($context == 'com_sellacious.cart')
		{
			$formData = $this->app->input->post->get('jform', array(), 'array');

			// Save checkout form data for product (if there is any)
			if ($formData)
			{
				CheckoutQuestionsFormHelper::saveForm('product', $formData, $cart, $item);
			}
		}
	}

	/**
	 * Send the email for the given user object using given email template object
	 *
	 * @param   JTable  $template  The template table object
	 * @param   object  $user      The user object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.3
	 */
	protected function addUserMail($template, $user)
	{
		$base = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$link = JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $user->activation);

		$helper      = SellaciousHelper::getInstance();
		$emailParams = $helper->config->getEmailParams();

		$replacements = array(
			'sitename'          => JFactory::getConfig()->get('sitename'),
			'site_url'          => rtrim(JUri::root(), '/'),
			'email_header'      => $emailParams->get('header', ''),
			'email_footer'      => $emailParams->get('footer', ''),
			'activation_link'   => $base . $link,
			'full_name'         => $user->name,
			'email_address'     => $user->email,
			'registration_date' => JHtml::_('date', $user->registerDate, 'F d, Y h:i A T'),
			'days_passed'       => $user->days,
		);

		$recipients = explode(',', $template->get('recipients'));

		if ($template->get('send_actual_recipient'))
		{
			array_unshift($recipients, $user->email);
		}

		CommunicationHelper::addMailToQueue($template, $replacements, $recipients);
	}

	/**
	 * Send the email to the administrators for the given user objects using given email template object
	 *
	 * @param   JTable    $template  The template table object
	 * @param   object[]  $users     The user object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.3
	 */
	protected function addAdminMail($template, $users)
	{
		$helper = SellaciousHelper::getInstance();

		// Load recipients
		$recipients = explode(',', $template->get('recipients'));

		if ($template->get('send_actual_recipient'))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			try
			{
				$uids = UserHelper::getSuperUsers();

				$query->select('DISTINCT  u.email')
					->from($db->q('#__users', 'u'))
					->where('u.block = 0')
					->where('u.id IN (' . implode(', ', $uids) . ')');

				$db->setQuery($query);

				$admins     = $db->loadColumn();
				$recipients = array_merge($admins, $recipients);
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		if (count($recipients))
		{
			// Prepare users list
			$list   = array();
			$list[] = '<table style="width: 100%; white-space: nowrap; border: none;">';

			foreach ($users as $user)
			{
				$rDate  = JHtml::_('date', $user->registerDate, 'F d, Y h:i A T');
				$list[] = "<tr><td>$user->name</td><td>$user->email</td><td>$rDate</td><td>$user->days Days</td></tr>";
			}

			$list[] = '</table>';

			$emailParams = $helper->config->getEmailParams();

			$replacements = array(
				'sitename'     => JFactory::getConfig()->get('sitename'),
				'site_url'     => rtrim(JUri::root(), '/'),
				'email_header' => $emailParams->get('header', ''),
				'email_footer' => $emailParams->get('footer', ''),
				'user_list'    => implode($list),
			);

			CommunicationHelper::addMailToQueue($template, $replacements, $recipients);
		}
	}

	/**
	 * Get a list of users that have not yet activated their account
	 *
	 * @param   int[]  $days  Number of days since registration
	 *
	 * @return  array
	 *
	 * @since   1.3.3
	 */
	protected function getInactiveUsers($days)
	{
		$users = array();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.name, a.username, a.email, a.block, a.registerDate, a.activation')
			->from($db->qn('#__users', 'a'))
			->where('a.block = 1')
			->where('a.activation != ' . $db->q(''));

		sort($days);

		foreach ($days as $day)
		{
			// If the job is missed for a whole day, no mails would be sent for that day on another day.
			$then  = JFactory::getDate()->modify("-{$day} days");
			$start = $then->format('Y-m-d 00:00:00');
			$end   = $then->format('Y-m-d 23:59:59');

			try
			{
				$sql = clone $query;
				$sql->select($db->q($day) . ' AS days')
					->where(sprintf('a.registerDate BETWEEN %s AND %s', $db->q($start), $db->q($end)));

				$db->setQuery($sql);

				if ($results = $db->loadObjectList())
				{
					foreach ($results as $result)
					{
						$users[$result->id] = $result;
					}
				}
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		return array_values($users);
	}

	/**
	 * This method sends a reminder email for non-activated users.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function checkPendingActivation()
	{
		// If we've already processed today, skip.
		$table = JTable::getInstance('MailQueue', 'SellaciousTable');
		$db    = JFactory::getDbo();
		$now   = JFactory::getDate();
		$query = $db->getQuery(true);

		$query->select('COUNT(1)')->from($db->qn($table->getTableName(), 'a'))
			->where('a.context LIKE ' . $db->q('user_activation.%', false))
			->where('a.created > ' . $db->q($now->format('Y-m-d 00:00:00')));

		try
		{
			$cnt = $db->setQuery($query)->loadResult();

			if ($cnt)
			{
				return;
			}
		}
		catch (Exception $e)
		{
			return;
		}

		// Send to the user
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => 'user_activation.self'));

		$params = new Registry($table->get('params'));
		$days   = ArrayHelper::toInteger(preg_split('/[^\d]+/', $params->get('days')));
		$days   = array_filter($days);

		if ($table->get('state'))
		{
			$users = $this->getInactiveUsers($days);

			foreach ($users as $user)
			{
				$this->addUserMail($table, $user);
			}
		}

		// Send to administrators
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => 'user_activation.admin'));

		$params = new Registry($table->get('params'));
		$days   = ArrayHelper::toInteger(preg_split('/[^\d]+/', $params->get('days')));
		$days   = array_filter($days);

		if ($table->get('state'))
		{
			$users = $this->getInactiveUsers($days);

			if ($users)
			{
				$this->addAdminMail($table, $users);
			}
		}
	}

	/**
	 * This method checks.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	protected function checkMediaDownloadQueue()
	{
		if ($this->app->input->get('option') == '' && $this->app->input->get('op') == 'sellacious.downloadQueue')
		{
			$helper   = SellaciousHelper::getInstance();
			$filter   = array(
				'list.select' => 'a.id, a.path, a.params',
				'list.where'  => 'a.params LIKE ' . $this->db->q('%"remote_download":%', false),
				'state'       => -1,
			);
			$iterator = $helper->media->getIterator($filter);

			foreach ($iterator as $item)
			{
				$params = new Registry($item->params);

				if ($params->get('remote_download') && $params->get('download_url'))
				{
					try
					{
						set_time_limit(60);

						$response = JHttpFactory::getHttp()->get($params->get('download_url'), null, '30');

						if ($response->code == 200 && strlen($response->body))
						{
							jimport('joomla.filesystem.folder');

							$filename = JPATH_SITE . '/' . $item->path;

							JFolder::create(dirname($filename));
							file_put_contents($filename, $response->body);

							$item->state = 1;
							$item->size  = filesize($filename);
							$item->type  = mime_content_type($filename);

							$params->set('remote_download', null);
						}
					}
					catch (\Exception $e)
					{
						JLog::add(sprintf('Media download failed from URL %s: %s', $params->get('download_url'), $e->getMessage()), JLog::WARNING);
					}

					$params->set('download_attempt', $params->get('download_attempt', 0) + 1);
				}
				else
				{
					$params->set('remote_download', null);
				}

				$item->params = (string) $params;

				$this->db->updateObject('#__sellacious_media', $item, array('id'));
			}

			if ($this->app->input->getCmd('format') === 'raw')
			{
				echo '1';

				jexit();
			}
		}
	}

	/**
	 * This method tells Joomla about the sellacious client application.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function registerClientApp()
	{
		$obj    = new stdClass;
		$helper = SellaciousHelper::getInstance();

		$obj->id   = 2;
		$obj->name = 'sellacious';
		$obj->path = JPATH_SELLACIOUS;

		JApplicationHelper::addClientInfo($obj);
	}

	/**
	 * Load the language files for given module
	 *
	 * @param   string  $module  The extension name for which to load language
	 * @param   string  $path    The client base path
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function loadModuleLanguage($module, $path = JPATH_BASE)
	{
		$lang = JFactory::getLanguage();

		$lang->load($module, $path . '/modules/' . $module, $lang->getDefault(), true, false);
		$lang->load($module, $path, $lang->getDefault(), true, false);
		$lang->load($module, $path . '/modules/' . $module, null, true, false);
		$lang->load($module, $path, null, true, false);
	}

	protected function override()
	{
		// Override JMail class
		if (file_exists(JPATH_LIBRARIES . '/sellacious/joomla/mail/JMail.php'))
		{
			JLoader::register('JMail', JPATH_LIBRARIES . '/sellacious/joomla/mail/JMail.php');
		}
	}

	/**
	 * Add common scripts to html document head
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function addHtmlHead()
	{
		// Add user information in js options
		$doc  = JFactory::getDocument();
		$user = JFactory::getUser();
		$obj  = new stdClass;

		$obj->id    = $user->id;
		$obj->name  = $user->name;
		$obj->guest = $user->guest;
		$obj->email = $user->email;

		$doc->addScriptOptions('sellacious.user', $obj);

		// Add frontend specific scripts
		if ($this->app->isClient('site') && class_exists('SellaciousHelper'))
		{
			JHtml::_('script', 'com_sellacious/util.cart.external-item.js', array('version' => S_VERSION_CORE, 'relative' => true));

			try
			{
				$helper = SellaciousHelper::getInstance();

				if ($this->app->input->get('tmpl') !== 'component' && $helper->config->get('product_compare'))
				{
					JHtml::_('script', 'com_sellacious/util.compare.js', array('version' => S_VERSION_CORE, 'relative' => true));
					JHtml::_('stylesheet', 'com_sellacious/util.compare-bar.css', array('version' => S_VERSION_CORE, 'relative' => true,));

				}

				if ($helper->config->get('cart_modal'))
				{
					JHtml::_('script', 'com_sellacious/util.cart.aio.js', array('version' => S_VERSION_CORE, 'relative' => true));

					$body = JHtml::_('ctechBootstrap.modal', 'modal-cart', '', '', '', array('size' => 'large', 'header' => false, 'v-centered' => true));
					$body = json_encode($body);

					// We should rather append a global html fragment only instead of detecting from js
					$doc->addScriptDeclaration(<<<JS
						jQuery(z => {
							if (z('#modal-cart').length === 0) {
								let html={$body};
								z('body').append(html);

								const cartModal = z('#modal-cart');
								const oo = new SellaciousViewCartAIO;
								oo.token = Joomla.getOptions('csrf.token');
								oo.initCart('#modal-cart .ctech-modal-body', true);
								cartModal.find('.ctech-modal-body').html('<div id="cart-items"></div>');
								cartModal.data('CartModal', oo);
							}
						});
JS
					);
				}

				$this->addScripts();
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}
	}

	/**
	 * Setup Google Analytics Code, Facebook Pixel Code and other external scripts in the head
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	protected function addScripts()
	{
		/** @var  JDocumentHtml  $doc */
		$helper = SellaciousHelper::getInstance();
		$doc    = JFactory::getDocument();

		$ga       = $helper->config->get('google_analytics', 1);
		$gaCode   = $helper->config->get('ga_code');
		$gaScript = $helper->config->get('ga_script');

		if ($ga && $gaCode)
		{
			$gaScript = "<!-- Google Analytics Code -->
			<script type='text/javascript'>
			  let _gaq = _gaq || [];
			  _gaq.push(['_setAccount', '{$gaCode}']);
			  _gaq.push(['_trackPageview']);
			  (function() {
				let ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				let s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
			</script>
			<!-- End Google Analytics Code -->
			";
		}

		if ($gaScript)
		{
			$doc->addCustomTag($gaScript);
		}

		$fbPixelCode   = $helper->config->get('fb_pixel_code');
		$fbPixel       = $helper->config->get('facebook_pixel', 1);
		$fbPixelScript = $helper->config->get('fb_pixel_script');

		if ($fbPixel && $fbPixelCode)
		{
			$fbPixelScript = "
			<!-- Facebook Pixel Code -->
			<script>
			  !function(f,b,e,v,n,t,s)
			  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			  n.queue=[];t=b.createElement(e);t.async=!0;
			  t.src=v;s=b.getElementsByTagName(e)[0];
			  s.parentNode.insertBefore(t,s)}(window, document,'script',
			  'https://connect.facebook.net/en_US/fbevents.js');
			  fbq('init', '{$fbPixelCode}');
			  fbq('track', 'PageView');
			</script>
			<noscript>
			  <img height='1' width='1' style='display:none;' alt='' src='https://www.facebook.com/tr?id=your-pixel-id-goes-here&ev=PageView&noscript=1'/>
			</noscript>
			<!-- End Facebook Pixel Code -->
			";
		}

		if ($fbPixelScript)
		{
			$doc->addCustomTag($fbPixelScript);
		}

		if ($misc = $helper->config->get('misc_script'))
		{
			$doc->addCustomTag($misc);
		}
	}
}
