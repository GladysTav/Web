<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Sellacious\Access\Access;
use Sellacious\Access\AccessHelper;
use Sellacious\Access\Rule;
use Sellacious\Access\Rules;
use Sellacious\User\UserGroupHelper;

/**
 * Class pkg_sellacious_extendedInstallerScript
 *
 * @since   1.0.0
 */
class pkg_sellacious_extendedInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method.
	 * Used to warn user that core package needs to be installed first before installing this one.
	 *
	 * @param   string                    $type
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function preFlight($type, $installer)
	{
		// Todo: Detect existing installation and active backoffice directory when in update routine
		if ($type == 'install')
		{
			$table = JTable::getInstance('Extension');

			if (!$table->load(array('type' => 'component', 'element' => 'com_sellacious')))
			{
				$message = 'You need to install <strong>Sellacious Core Package first</strong>. ' .
					'You can <a target="_blank" href="https://extensions.joomla.org/extension/sellacious">download it from JED</a> for FREE!';

				throw new RuntimeException($message);
			}
		}
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   string                    $type
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function postFlight($type, $installer)
	{
		// Delete the old version files
		if ($type == 'update')
		{
			$this->cleanupOldFiles();
			$this->enablePlugins();
			$this->fixPermissions();

			$this->updateRules('shoprule');
			$this->updateRules('shippingrule');
			$this->updateRules('coupon');
		}
		elseif ($type == 'install')
		{
			$this->setupUserGroups();
			$this->enablePlugins(true);
		}
	}

	/**
	 * Method to update Rules (Shop, Shipping, Coupons)
	 *
	 * @param   string  $type  The Rule type
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.2
	 */
	protected function updateRules($type = 'shoprule')
	{
		$db     = JFactory::getDbo();
		$helper = SellaciousHelper::getInstance();

		// Set Filter products to new table
		$query = $db->getQuery(true);
		$query->select('a.id, a.params')->from($db->qn('#__sellacious_' . $type . 's', 'a'));

		$db->setQuery($query);

		$rules = $db->loadObjectList();

		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				$params = new Registry($rule->params);
				$filter = $params->extract('product');

				if ($filter)
				{
					$product_filter = $filter->get('products', null);
					$product_filter = is_array($product_filter) ? $product_filter : explode(',', $product_filter);
					$product_filter = array_filter($product_filter);

					$helper->product->setRuleProducts($rule->id, $type, $product_filter);
				}
			}
		}
	}

	/**
	 * Enable all plugins installed with sellacious packages
	 *
	 * @param   bool  $all  Whether to enable all plugins or just the isnew="true" marked ones.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function enablePlugins($all = false)
	{
		$fileNames[] = JPATH_MANIFESTS . '/packages/pkg_sellacious_extended.xml';

		foreach ($fileNames as $filename)
		{
			if (file_exists($filename))
			{
				$manifest = simplexml_load_file($filename);

				if ($manifest instanceof SimpleXMLElement)
				{
					$plugins = $manifest->xpath('/extension/files[@folder="extensions"]/file[@type="plugin"]');
					$enabled = 0;

					foreach ($plugins as $plugin)
					{
						if ($all || (string) $plugin['isnew'] == 'true')
						{
							$keys = array(
								'type'    => (string) $plugin['type'],
								'folder'  => (string) $plugin['group'],
								'element' => (string) $plugin['id'],
							);

							$extension = JTable::getInstance('Extension');

							if ($extension->load($keys))
							{
								$extension->set('enabled', 1);
								$enabled += $extension->store();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Delete the files which existed in earlier versions but not in this version
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function cleanupOldFiles()
	{
		$files = array(
			'plugins/sellacious/order/forms/order.xml',
			'plugins/sellacious/mailqueue/forms/message.xml',
			'plugins/sellacious/mailqueue/forms/seller.xml',
			'plugins/sellacious/mailqueue/forms/transaction.xml',
			'plugins/system/sellaciousmailer/tmpl/footer.php',
			'libraries/sellacious/helper/price.php',
			'libraries/sellacious/joomla/html/calendar.php',
			'libraries/sellacious/objects/Sellacious/Cache/Prices.php',
			'libraries/sellacious/objects/Sellacious/Cache/Products.php',
			'libraries/sellacious/objects/Sellacious/Cache/Specifications.php',
			'sellacious/components/com_sellacious/layouts/views/activation/default.php',
			'sellacious/components/com_sellacious/layouts/views/product/edit_variants.php',
			'sellacious/components/com_sellacious/layouts/views/products/default_modal.php',
			'sellacious/components/com_sellacious/models/fields/pricedisplay.php',
			'sellacious/components/com_sellacious/models/fields/pricedisplaylist.php',
			'sellacious/components/com_sellacious/models/fields/unitcombo.php',
			'sellacious/components/com_sellacious/models/forms/product/prices.xml',
			'sellacious/includes/toolbar.php',
			'sellacious/templates/sellacious/html/layouts/com_sellacious/formfield/grouprules/grid.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/base.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/containerclose.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/iconclass.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/link.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/standard.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/title.php',
			'sellacious/templates/sellacious/js/plugin/ckeditor/CHANGES.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/LICENSE.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/README.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/adapters/jquery.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/build-config.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/ckeditor-.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/ckeditor.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/config.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/contents.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/af.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ar.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/bg.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/bn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/bs.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/cs.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/cy.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/da.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/de.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/el.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/en-au.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/en-ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/en-gb.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/en.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/eo.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/es.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/et.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/eu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/fa.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/fi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/fo.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/fr-ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/fr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/gl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/gu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/he.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/hi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/hr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/hu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/id.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/is.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/it.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ja.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ka.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/km.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ko.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ku.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/lt.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/lv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/mk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/mn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ms.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/nb.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/nl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/no.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/pl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/pt-br.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/pt.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ro.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ru.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/si.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sq.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sr-latn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/sv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/th.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/tr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/ug.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/uk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/vi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/zh-cn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/lang/zh.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/a11yhelp.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/_translationstatus.txt',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ar.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/bg.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/cs.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/cy.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/da.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/de.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/el.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/en.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/eo.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/es.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/et.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fa.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fr-ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/gl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/gu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/he.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/id.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/it.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ja.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/km.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ko.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ku.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/lt.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/lv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/mk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/mn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/nb.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/nl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/no.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pt-br.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pt.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ro.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ru.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/si.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sq.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sr-latn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/th.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/tr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ug.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/uk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/vi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/zh-cn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/about/dialogs/about.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/about/dialogs/hidpi/logo_ckeditor.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/about/dialogs/logo_ckeditor.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/clipboard/dialogs/paste.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/colordialog/dialogs/colordialog.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/dialog/dialogDefinition.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/div/dialogs/div.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/fakeobjects/images/spacer.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/find/dialogs/find.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/flash/dialogs/flash.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/flash/images/placeholder.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/button.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/checkbox.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/form.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/hiddenfield.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/radio.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/select.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/textarea.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/dialogs/textfield.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/forms/images/hiddenfield.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/icons.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/icons_hidpi.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/iframe/dialogs/iframe.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/iframe/images/placeholder.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/image/dialogs/image.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/image/images/noimage.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/link/dialogs/anchor.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/link/dialogs/link.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/link/images/anchor.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/link/images/hidpi/anchor.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/liststyle/dialogs/liststyle.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/magicline/images/hidpi/icon.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/magicline/images/icon.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/pagebreak/images/pagebreak.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/pastefromword/filter/default.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/preview/preview.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/scayt/LICENSE.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/scayt/README.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/scayt/dialogs/options.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/scayt/dialogs/toolbar.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_address.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_blockquote.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_div.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h1.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h2.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h3.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h4.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h5.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_h6.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_p.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/showblocks/images/block_pre.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/dialogs/smiley.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/angel_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/angry_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/broken_heart.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/confused_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/cry_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/devil_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/embaressed_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/embarrassed_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/envelope.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/heart.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/kiss.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/lightbulb.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/omg_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/regular_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/sad_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/shades_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/teeth_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/thumbs_down.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/thumbs_up.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/tongue_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/tounge_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/whatchutalkingabout_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/smiley/images/wink_smile.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/_translationstatus.txt',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ar.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/bg.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/cs.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/cy.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/de.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/el.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/en.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/eo.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/es.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/et.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fa.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fr-ca.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/gl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/he.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/hr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/hu.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/id.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/it.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ja.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ku.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/lv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/nb.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/nl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/no.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pt-br.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pt.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ru.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/si.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sl.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sq.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sv.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/th.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/tr.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ug.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/uk.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/vi.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/zh-cn.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/specialchar.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/table/dialogs/table.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/tabletools/dialogs/tableCell.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/dialogs/templates.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/dialogs/templates.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/templates/default.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/templates/images/template1.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/templates/images/template2.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/templates/templates/images/template3.gif',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/LICENSE.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/README.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/ciframe.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/tmp.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/tmpFrameset.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc_ie.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/ajax.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/api.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/appendto.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/inlineall/logo.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/outputxhtml/outputxhtml.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/posteddata.php',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/sample.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/sample.jpg',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/assets/uilanguages/languages.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/datafiltering.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/divreplace.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/inlineall.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/inlinebycode.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/inlinetextarea.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/jquery.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/dialog/assets/my_dialog.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/dialog/dialog.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/enterkey/enterkey.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/htmlwriter/assets/outputforflash/outputforflash.fla',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/htmlwriter/assets/outputforflash/outputforflash.swf',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/htmlwriter/assets/outputforflash/swfobject.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/htmlwriter/outputforflash.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/htmlwriter/outputhtml.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/magicline/magicline.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/toolbar/toolbar.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/plugins/wysiwygarea/fullpage.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/readonly.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/replacebyclass.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/replacebycode.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/sample.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/sample.js',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/sample_posteddata.php',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/tabindex.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/uicolor.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/uilanguages.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/samples/xhtmlstyle.html',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog_ie.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog_ie7.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog_ie8.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog_iequirks.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/dialog_opera.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor_gecko.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor_ie.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor_ie7.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor_ie8.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/editor_iequirks.css',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/icons.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/icons_hidpi.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/arrow.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/close.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/hidpi/close.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/hidpi/lock-open.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/hidpi/lock.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/hidpi/refresh.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/lock-open.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/lock.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/images/refresh.png',
			'sellacious/templates/sellacious/js/plugin/ckeditor/skins/moono/readme.md',
			'sellacious/templates/sellacious/js/plugin/ckeditor/styles.js',
			'libraries/paypal/composer/ClassLoader.php',
			'libraries/paypal/composer/autoload_classmap.php',
			'libraries/paypal/composer/autoload_namespaces.php',
			'libraries/paypal/composer/autoload_psr4.php',
			'libraries/paypal/composer/autoload_real.php',
			'libraries/paypal/composer/installed.json',
			'libraries/paypal/PayPal/Core/PayPalLoggingLevel.php',
			'libraries/paypal/PayPal/Validation/ModelAccessorValidator.php',
			'plugins/system/sellacioushyperlocal/forms/category.xml',
			'plugins/system/sellacioushyperlocal/forms/fields/timings.php',
			'plugins/system/sellacioushyperlocal/forms/seller.xml',
			'plugins/system/sellacioushyperlocal/tables/sellerhyperlocal.php',
			'plugins/system/sellacioushyperlocal/tables/sellertimings.php',
			'plugins/system/sellacioushyperlocal/libraries/Sellacious/Cache/Distances.php',
			'plugins/system/sellacioushyperlocal/tmpl/default_store.php',
			'plugins/system/sellacioushyperlocal/tmpl/field_timings.php',
			'plugins/system/sellacioushyperlocal/tmpl/store_timings.php',
			'plugins/system/sellacioushyperlocal/sql/plg_system_sellacioushyperlocal.install.mysqli.sql',
			'plugins/system/sellacioushyperlocal/sql/plg_system_sellacioushyperlocal.uninstall.mysqli.sql',
			'media/plg_system_sellacioushyperlocal/js/config.js',
			'media/plg_system_sellacioushyperlocal/js/seller.js',
			'media/plg_system_sellacioushyperlocal/css/layout.profile.css',
			'media/plg_system_sellacioushyperlocal/css/layout.store.css',
			'modules/mod_sellacious_filters/tmpl/filter_product_attribute.php',
			'modules/mod_sellacious_filters/tmpl/filter_shippable.php',
			'modules/mod_sellacious_filters/tmpl/filter_shipping.php',
			'modules/mod_sellacious_filters/tmpl/filter_shop_name.php',
			'media/mod_sellacious_filters/js/filters.pagination.js',
			'media/mod_sellacious_filters/js/locations.js',
			'modules/mod_sellacious_products/tmpl/carousel.php',
			'modules/mod_sellacious_products/tmpl/grid.php',
			'modules/mod_sellacious_products/tmpl/list.php',
			'modules/mod_sellacious_products/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_products/assets/js/owl.carousel.js',
			'modules/mod_sellacious_latestproducts/helper.php',
			'modules/mod_sellacious_latestproducts/tmpl/carousel.php',
			'modules/mod_sellacious_latestproducts/tmpl/default.php',
			'modules/mod_sellacious_latestproducts/tmpl/grid.php',
			'modules/mod_sellacious_latestproducts/tmpl/list.php',
			'modules/mod_sellacious_latestproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_latestproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_latestproducts/css/style.css',
			'modules/mod_sellacious_relatedproducts/helper.php',
			'modules/mod_sellacious_relatedproducts/tmpl/carousel.php',
			'modules/mod_sellacious_relatedproducts/tmpl/default.php',
			'modules/mod_sellacious_relatedproducts/tmpl/grid.php',
			'modules/mod_sellacious_relatedproducts/tmpl/list.php',
			'modules/mod_sellacious_relatedproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_relatedproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_relatedproducts/css/style.css',
			'modules/mod_sellacious_specialcatsproducts/helper.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/carousel.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/default.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/grid.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/list.php',
			'modules/mod_sellacious_specialcatsproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_specialcatsproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_specialcatsproducts/css/style.css',
			'modules/mod_sellacious_stores/helper.php',
			'modules/mod_sellacious_stores/tmpl/carousel.php',
			'modules/mod_sellacious_stores/tmpl/grid.php',
			'modules/mod_sellacious_stores/tmpl/list.php',
			'modules/mod_sellacious_stores/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_stores/assets/js/owl.carousel.js',
			'modules/mod_sellacious_sellerproducts/helper.php',
			'modules/mod_sellacious_sellerproducts/tmpl/carousel.php',
			'modules/mod_sellacious_sellerproducts/tmpl/default.php',
			'modules/mod_sellacious_sellerproducts/tmpl/grid.php',
			'modules/mod_sellacious_sellerproducts/tmpl/list.php',
			'modules/mod_sellacious_sellerproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_sellerproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_sellerproducts/css/style.css',
			'modules/mod_sellacious_bestsellingproducts/helper.php',
			'modules/mod_sellacious_bestsellingproducts/tmpl/carousel.php',
			'modules/mod_sellacious_bestsellingproducts/tmpl/default.php',
			'modules/mod_sellacious_bestsellingproducts/tmpl/grid.php',
			'modules/mod_sellacious_bestsellingproducts/tmpl/list.php',
			'modules/mod_sellacious_bestsellingproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_bestsellingproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_bestsellingproducts/css/style.css',
			'modules/mod_sellacious_recentlyviewedproducts/helper.php',
			'modules/mod_sellacious_recentlyviewedproducts/tmpl/carousel.php',
			'modules/mod_sellacious_recentlyviewedproducts/tmpl/default.php',
			'modules/mod_sellacious_recentlyviewedproducts/tmpl/grid.php',
			'modules/mod_sellacious_recentlyviewedproducts/tmpl/list.php',
			'modules/mod_sellacious_recentlyviewedproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_recentlyviewedproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_recentlyviewedproducts/css/style.css',
			'modules/mod_sellacious_hyperlocal/tmpl/empty.php',
			'media/mod_sellacious_hyperlocal/js/default.js',
			'libraries/sellacious/objects/Sellacious/Cache/Product.php',
			'libraries/sellacious/objects/Sellacious/Cache/SpecificationRange.php',
			'libraries/sellacious/objects/TCPDF/TCPDF.phar',
			'sellacious/components/com_sellacious/models/fields/mapaddress.php',
			'sellacious/templates/sellacious/html/layouts/com_sellacious/formfield/shopruleslabs/rowtemplate.php',
			'sellacious/templates/sellacious/html/layouts/com_sellacious/formfield/shopruleslabs.php',
			'plugins/system/sellacioushyperlocal/forms/fields/timecombo.php',
			'plugins/system/sellacioushyperlocal/forms/template.xml',
			'plugins/system/sellacioushyperlocal/tables/orderdeliveryslot.php',
			'plugins/system/sellacioushyperlocal/tables/productsellerslotlimit.php',
			'plugins/system/sellacioushyperlocal/tmpl/default_cart_attributes.php',
			'plugins/system/sellacioushyperlocal/tmpl/default_cartprices.php',
			'plugins/system/sellacioushyperlocal/tmpl/default_item_attributes.php',
			'plugins/system/sellacioushyperlocal/tmpl/default_orderitem.php',
			'plugins/system/sellacioushyperlocal/tmpl/field_timecombo.php',
			'media/plg_system_sellacioushyperlocal/js/bootstrap-datetimepicker.js',
			'media/plg_system_sellacioushyperlocal/js/layout.cart_attributes.js',
			'media/plg_system_sellacioushyperlocal/js/layout.item_attributes.js',
			'media/plg_system_sellacioushyperlocal/js/moment-with-locales.js',
			'media/plg_system_sellacioushyperlocal/css/bootstrap-datetimepicker.css',
			'media/plg_system_sellacioushyperlocal/css/glyphicons.css',
			'media/plg_system_sellacioushyperlocal/css/layout.cart_attributes.css',
			'media/plg_system_sellacioushyperlocal/css/layout.item_attributes.css',
			'media/plg_system_sellacioushyperlocal/fonts/glyphicons-halflings-regular.eot',
			'media/plg_system_sellacioushyperlocal/fonts/glyphicons-halflings-regular.svg',
			'media/plg_system_sellacioushyperlocal/fonts/glyphicons-halflings-regular.ttf',
			'media/plg_system_sellacioushyperlocal/fonts/glyphicons-halflings-regular.woff',
			'modules/mod_sellacious_latestproducts/tmpl/layout.php',
			'modules/mod_sellacious_hyperlocal/latitude_inner_details.xml',
			'modules/mod_sellacious_hyperlocal/location_inner_details.xml',
			'modules/mod_sellacious_hyperlocal/tmpl/_empty.php',
			'libraries/sellacious/objects/tcpdf/README.TXT',
			'plugins/system/sellacioushyperlocal/forms/module.xml'
		);

		foreach ($files as $file)
		{
			JFile::delete(JPATH_ROOT . '/' . $file);
		}
	}

	/**
	 * Setup user groups and their default permissions as required by sellacious upon first install.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function setupUserGroups()
	{
		jimport('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			$me       = JFactory::getUser();
			$helper   = SellaciousHelper::getInstance();
			$category = $helper->category->getDefault('seller', 'a.id');
			$seller   = array(
				'user_id'     => $me->id,
				'category_id' => $category ? $category->id : 0,
				'title'       => $me->name,
				'code'        => strtoupper($me->username),
			);

			// We may want to skip this if the default seller account is already set up.
			$helper->profile->create($me->id);
			$helper->user->addAccount($seller, 'seller');
			$helper->config->set('default_seller', $me->id);

			$groups = UserGroupHelper::getAll();

			foreach ($groups as $group)
			{
				AccessHelper::setDefaultAccess($group->id);
			}
		}
	}

	/**
	 * Fix and Merge all old permissions to new permissions
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function fixPermissions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			$query->select('rules')->from('#__sellacious_permissions')->where('name = ' . $db->q('com_sellacious'));

			$rules = $db->setQuery($query)->loadResult();

			if (strlen($rules) <= 10)
			{
				$this->processRules(JPATH_SITE . '/sellacious/access.xml', null);
				$this->processRules(JPATH_SITE . '/sellacious/components/com_sellacious/access.xml', 'com_sellacious');
			}
		}
		catch (Exception $e)
		{
			// Just in case the table could not be read, ignore and skip!
		}
	}

	/**
	 * Process rules for import from legacy permissions to new permissions
	 *
	 * @param   string  $file   Path to manifest
	 * @param   string  $asset  Asset name
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function processRules($file, $asset = null)
	{
		$db      = JFactory::getDbo();
		$xpath   = "/access/section[@name='component']/actions/";
		$actions = Access::getActionsFromFile($file, $xpath);

		$query = $db->getQuery(true);

		$query->select('a.id, a.type, a.usergroups')
			->from($db->qn('#__sellacious_categories', 'a'))
			->where('a.state = 1 AND a.level > 0');

		$categories = $db->setQuery($query)->loadObjectList();

		$rules = new Rules;

		foreach ($actions as $action)
		{
			$rule       = new Rule(array());
			$actionName = $action->name;

			foreach ($categories as $category)
			{
				$userGroups = json_decode($category->usergroups, 1) ?: array();

				foreach ($userGroups as $userGroup)
				{
					$allow = JAccess::checkGroup($userGroup, $actionName, 'com_sellacious');

					if ($allow !== null)
					{
						$rule->mergeIdentity($category->id, $allow);
					}
				}
			}

			$rules->mergeAction($actionName, $rule->getData());
		}

		AccessHelper::saveRules($rules, $asset);
	}
}
