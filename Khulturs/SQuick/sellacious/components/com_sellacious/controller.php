<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
use Joomla\CMS\Response\JsonResponse;
use Sellacious\Access\AccessHelper;
use Sellacious\Cache\CacheHelper;
use Sellacious\Media\MediaCleanup;
use Sellacious\Search\SearchHelper;

defined('_JEXEC') or die;

class SellaciousController extends SellaciousControllerBase
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @see     JControllerLegacy
	 *
	 * @since   1.0.0
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		$this->registerTask('refreshTableAndMedia', 'systemAutofix');
	}

	/**
	 * Method to display a view.
	 *
	 * @param   bool   $cacheable  if true, the view output will be cached
	 * @param   mixed  $urlparams  An array of safe url parameters and their variable types,
	 *                             for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'dashboard');
		$this->input->set('view', $view);

		if (!$this->helper->core->isRegistered())
		{
			$this->input->set('tmpl', 'component');
			$this->input->set('view', 'activation');
		}
		elseif (!$this->helper->core->isConfigured())
		{
			$this->input->set('tmpl', 'component');
			$this->input->set('view', 'setup');
		}
		elseif (!$this->canView())
		{
			$tmpl   = $this->input->get('tmpl', null);
			$suffix = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
			$return = JRoute::_('index.php?option=com_sellacious' . $suffix, false);

			if ($tmpl != 'raw')
			{
				$this->setRedirect($return);

				JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			}

			return $this;
		}

		return parent::display($cacheable, $urlparams);
	}

	/**
	 * Checks whether a user can see this view.
	 *
	 * @return  bool
	 *
	 * @since   1.6
	 */
	protected function canView()
	{
		$view   = $this->input->get('view', 'dashboard');
		$layout = $this->input->get('layout');
		$format = $this->input->get('format');
		$id     = $this->input->getInt('id', null);
		$basic  = array(
			'option' => 'com_sellacious',
			'view'   => $view,
			'layout' => $layout,
			'format' => $format,
		);
		$query  = $this->input->get->getArray();
		$query  = array_merge($basic, $query);

		if ($layout == 'edit')
		{
			$editId = $this->app->getUserState('com_sellacious.edit.' . $view . '.id');

			if ($id === null && $editId)
			{
				$query['id'] = $editId;

				// We should allow default edit id if not set in Request URI
				$this->app->redirect(JRoute::_('index.php?' . http_build_query($query), false));
			}

			if ($editId != $id)
			{
				/**
				 * Somehow the person just went to the form - we don't allow that.
				 * But instead of stopping him just switch the context
				 * if already editing something else, clear it from session to prevent data in new form
				 */
				$query['id'] = $id;
				$this->app->setUserState('com_sellacious.edit.' . $view . '.id', $id);
				$this->app->setUserState('com_sellacious.edit.' . $view . '.data', null);
				$this->app->redirect(JRoute::_('index.php?' . http_build_query($query), false));
			}
		}

		/**
		 * Todo: Below we assume all (backend) singular views to be edit layout only.
		 * Todo: This may not be true. 'create' etc permissions need to be checked too.
		 */
		switch ($view)
		{
			case 'order':
			case 'productlisting':
			case 'variants':
			case 'dashboard':
				$allow = true;
				break;
			case 'coupons':
				$allow = $this->canList('coupon');
				break;
			case 'coupon':
				$allow = $this->canEdit('coupon');
				break;
			case 'orders':
				$allow = $this->canList('order');
				break;
			case 'products':
				$allow = $this->canList('product');
				break;
			case 'productbuttons':
				$allow = $this->canList('productbutton');
				break;
			case 'productbutton':
				$allow = $this->canEdit('productbutton');
				break;
			case 'product':
			case 'productmedia':
				$allow = $this->helper->product->canEdit();
				break;
			case 'ratings':
				$allow = $this->canList('rating');
				break;
			case 'rating':
				$allow = false;
				break;
			case 'mailqueue':
				$allow = $this->canList('mailqueue');
				break;
			case 'activation':
				$allow = AccessHelper::allow('app.manage');
				break;
			case 'setup':
			case 'config':
				$allow = AccessHelper::allow('config.edit');
				break;
			case 'emailoption':
				$allow = $this->helper->access->checkAny(array('list', 'edit'), 'emailoption.');
				break;
			case 'emailtemplate':
			case 'emailtemplates':
				$allow = $this->helper->access->checkAny(array('list', 'edit'), 'emailtemplate.');
				break;
			case 'questions':
				$allow = $this->canList('question');
				break;
			case 'question':
				$allow = $this->canEdit('question');
				break;
			case 'location':
				$allow = $this->helper->access->checkAny(array('edit', 'edit.own'), 'location.');
				break;
			case 'locations':
				$allow = $this->helper->access->checkAny(array('list', 'list.own'), 'location.');
				break;
			case 'licenses':
				$allow = $this->helper->access->check('license.list');
				break;
			case 'license':
				$allow = $this->helper->access->check('license.edit');
				break;
			case 'permissions':
				$allow = AccessHelper::allow('permissions.edit');
				break;
			case 'categories':
				$allow = $this->helper->access->check('category.list');
				break;
			case 'category':
				$allow = $this->helper->access->check('category.edit');
				break;
			case 'shoprule':
			case 'shoprules':
				$allow = $this->canList('shoprule');
				break;
			case 'splcategories':
				$allow = $this->helper->access->check('splcategory.list');
				break;
			case 'splcategory':
				$allow = $this->helper->access->check('splcategory.edit');
				break;
			case 'fields':
				$allow = $this->helper->access->check('field.list');
				break;
			case 'field':
				$allow = $this->helper->access->check('field.edit');
				break;
			case 'units':
				$allow = $this->helper->access->check('unit.list');
				break;
			case 'unit':
				$allow = $this->helper->access->check('unit.edit');
				break;
			case 'currencies':
				$allow = $this->helper->access->check('currency.list');
				break;
			case 'currency':
				$allow = $this->helper->access->check('currency.edit');
				break;
			case 'users':
				$allow = $this->helper->access->check('user.list');
				break;
			case 'user':
				$allow = $this->helper->access->check('user.edit');
				break;
			case 'profile':
				$allow = $this->helper->access->check('user.edit.own');
				break;
			case 'variant':
				$allow = $this->canEdit('variant');
				break;
			case 'shippingrules':
				$allow = $this->canList('shippingrule');
				break;
			case 'shippingrule':
				$allow = $this->helper->access->checkAny(array('edit', 'edit.own'), 'shippingrule.');
				break;
			case 'statuses':
				$allow = $this->helper->access->check('status.list');
				break;
			case 'status':
				$allow = $this->helper->access->check('status.edit');
				break;
			case 'relatedproducts':
				$allow = $this->helper->access->checkAny(array('edit.related', '.edit.related.own'), 'product');
				break;
			case 'transactions':
				$allow = $this->helper->access->checkAny(array('list', 'list.own'), 'transaction.');
				break;
			case 'transaction':
				$allow = $this->helper->access->checkAny(array('direct', 'direct.own', 'gateway', 'gateway.own'), 'transaction.addfund.')
						|| $this->helper->access->checkAny(array('withdraw', 'withdraw.own'), 'transaction.');
				break;
			case 'messages':
				$allow = $this->helper->access->checkAny(array('list', 'list.own'), 'message.');
				break;
			case 'message':
				$allow = $this->helper->access->checkAny(array('create', 'reply', 'reply.own'), 'message.');
				break;
			case 'downloads':
				$allow = $this->helper->access->checkAny(array('list', 'list.own'), 'download.');
				break;
			case 'paymentmethods':
				$allow = $this->helper->access->check('paymentmethod.list');
				break;
			case 'paymentmethod':
				$allow = $this->helper->access->check('paymentmethod.edit');
				break;
			default:
				$allow = false;
		}

		return $allow;
	}

	/**
	 * Check whether the user can view the singular/edit view or not
	 *
	 * @param   string  $asset
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function canEdit($asset)
	{
		$rules = $asset == 'message' ? array('.create', '.create.bulk', '.reply', '.reply.own') : array('.create', '.edit', '.edit.own');

		return $this->helper->access->checkAny($rules, $asset);
	}

	/**
	 * Check whether the user can view the plural/list view or not
	 *
	 * @param   string  $asset
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function canList($asset)
	{
		$b = $this->helper->access->check($asset . '.list') ||
			 $this->helper->access->check($asset . '.list.own');

		return $b;
	}

	/**
	 * Rebuild backoffice menu from the xml
	 *
	 * @since   1.5.0
	 */
	public function rebuildMenu()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));

		if (AccessHelper::allow('app.manage'))
		{
			$this->helper->core->rebuildMenu(true);
		}
	}

	/**
	 * Refresh the media from folders into our media table.
	 * Rebuild the nested set tree for all nested structured sellacious tables.
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	public function systemAutofix()
	{
		$this->checkToken();

		$this->setRedirect($this->getReturnURL());

		$cleanup = new MediaCleanup;

		try
		{
			$cleanup->execute();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		// Table: #__sellacious_category_types
		// Table: #__sellacious_permissions
		// Table: #__sellacious_messages (removed)

		try
		{
			// Table: #__sellacious_categories
			/** @var  SellaciousTableCategory $table */
			$table = JTable::getInstance('Category', 'SellaciousTable');
			$table->rebuild();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		try
		{
			// Table: #__sellacious_fields
			/** @var  SellaciousTableField $table */
			$table = JTable::getInstance('Field', 'SellaciousTable');
			$table->rebuild();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		try
		{
			// Table: #__sellacious_shoprules
			/** @var  SellaciousTableShopRule $table */
			$table = JTable::getInstance('ShopRule', 'SellaciousTable');
			$table->rebuild();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		try
		{
			// Table: #__sellacious_splcategories
			/** @var  SellaciousTableSplCategory $table */
			$table = JTable::getInstance('SplCategory', 'SellaciousTable');
			$table->rebuild();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}

		// Fix products alias
		$filters  = array('list.select' => 'a.id');
		$iterator = $this->helper->product->getIterator($filters);

		foreach ($iterator as $obj)
		{
			$table = SellaciousTable::getInstance('Product');

			try
			{
				$table->load($obj->id);

				$value = $table->alias;

				$table->check();

				if ($value != $table->alias)
				{
					$table->store();
				}
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		// Fix variants alias
		$filters  = array('list.select' => 'a.id');
		$iterator = $this->helper->variant->getIterator($filters);

		foreach ($iterator as $obj)
		{
			$table = SellaciousTable::getInstance('Variant');

			try
			{
				$table->load($obj->id);

				$value = $table->alias;

				$table->check();

				if ($value !== $table->alias)
				{
					$table->store();
				}
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		$this->setMessage(JText::_('COM_SELLACIOUS_REFRESH_SUCCESS'));

		return true;
	}

	/**
	 * Check whether a cache process is running in the background
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function checkCacheAjax()
	{
		$state = CacheHelper::isRunning();

		if ($state)
		{
			$data = array('state' => 2, 'message' => 'Cache is running', 'data'  => null);
		}
		else
		{
			$data = array('state' => 1, 'message' => 'Cache is not running', 'data'  => null);
		}

		echo json_encode($data);

		jexit();
	}

	/**
	 * Generate search index for TNT Search API based searches
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function buildIndex()
	{
		try
		{
			if (!$this->checkToken('post', false))
			{
				throw new \Exception(JText::_('JINVALID_TOKEN_NOTICE'));
			}

			$adapters = SearchHelper::getAdapters();

			foreach ($adapters as $adapter)
			{
				$adapter->buildIndex();
			}

			echo new JsonResponse(true);
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}

		jexit();
	}

	/**
	 * Save Joomla Configuration
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function saveConfig()
	{
		try
		{
			if (!$this->checkToken('post', false))
			{
				throw new \Exception(JText::_('JINVALID_TOKEN_NOTICE'));
			}

			$config = $this->input->get('config', array(), 'Array');
			$this->helper->config->saveJConfig($config);

			echo new JsonResponse(true);
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}

		jexit();
	}
}
