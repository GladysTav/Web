<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

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
	 * @param   string                     $type
	 * @param   \JInstallerAdapterPackage  $installer
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
					$product_filter = is_array($product_filter) ? $product_filter :  explode(',', $product_filter);
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
	 * Set default permission for sellacious if it is empty
	 *
	 * @param   array  $groups  Associative array of group type => id to set the permissions
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function setPermissions($groups)
	{
		/** @var  JTableUsergroup  $group */
		$group = JTable::getInstance('Usergroup');
		$group->rebuild();

		/** @var  JTableAsset  $asset */
		$asset = JTable::getInstance('Asset', 'JTable');
		$asset->loadByName('com_sellacious');

		if ($asset->id == 0)
		{
			$asset->set('parent_id', 1);
			$asset->set('name', 'com_sellacious');
			$asset->set('title', 'com_sellacious');
			$asset->setLocation(1, 'last-child');
		}

		$ruleSets = json_decode($asset->rules, true);

		if (empty($ruleSets))
		{
			$ruleSets = array(
				'admin'    => array(
					'app.admin'                       => 1,
					'app.manage'                      => 1,
					'app.login'                       => 1,
					'app.login.offline'               => 1,
					'config.edit'                     => 1,
					'permissions.edit'                => 1,
					'statistics.visitor'              => 1,
					'category.list'                   => 1,
					'category.create'                 => 1,
					'category.edit'                   => 1,
					'category.edit.state'             => 1,
					'category.delete'                 => 1,
					'coupon.list'                     => 1,
					'coupon.list.own'                 => 1,
					'coupon.create'                   => 1,
					'coupon.edit'                     => 1,
					'coupon.edit.own'                 => 1,
					'coupon.delete'                   => 1,
					'coupon.delete.own'               => 1,
					'coupon.edit.state'               => 1,
					'currency.list'                   => 1,
					'currency.create'                 => 1,
					'currency.edit'                   => 1,
					'currency.edit.state'             => 1,
					'currency.delete'                 => 1,
					'currency.edit.forex'             => 1,
					'field.list'                      => 1,
					'field.create'                    => 1,
					'field.delete'                    => 1,
					'field.edit'                      => 1,
					'field.edit.state'                => 1,
					'product.list'                    => 1,
					'product.list.own'                => 1,
					'product.create'                  => 1,
					'product.edit.basic'              => 1,
					'product.edit.basic.own'          => 1,
					'product.edit.seller'             => 1,
					'product.edit.pricing'            => 1,
					'product.edit.shipping'           => 1,
					'product.edit.related'            => 1,
					'product.edit.seo'                => 1,
					'product.edit.seo.own'            => 1,
					'product.delete'                  => 1,
					'product.delete.own'              => 1,
					'product.edit.state'              => 1,
					'variant.create'                  => 1,
					'variant.delete'                  => 1,
					'variant.delete.own'              => 1,
					'variant.edit'                    => 1,
					'variant.edit.own'                => 1,
					'variant.edit.state'              => 1,
					'shippingrule.list'               => 1,
					'shippingrule.list.own'           => 1,
					'shippingrule.create'             => 1,
					'shippingrule.delete'             => 1,
					'shippingrule.delete.own'         => 1,
					'shippingrule.edit'               => 1,
					'shippingrule.edit.own'           => 1,
					'shippingrule.edit.state'         => 1,
					'shoprule.list'                   => 1,
					'shoprule.create'                 => 1,
					'shoprule.delete'                 => 1,
					'shoprule.edit'                   => 1,
					'shoprule.edit.state'             => 1,
					'splcategory.list'                => 1,
					'splcategory.create'              => 1,
					'splcategory.delete'              => 1,
					'splcategory.edit'                => 1,
					'splcategory.edit.state'          => 1,
					'status.list'                     => 1,
					'status.create'                   => 1,
					'status.delete'                   => 1,
					'status.edit'                     => 1,
					'status.edit.state'               => 1,
					'user.list'                       => 1,
					'user.create'                     => 1,
					'user.delete'                     => 1,
					'user.edit'                       => 1,
					'user.edit.state'                 => 1,
					'user.edit.own'                   => 1,
					'unit.list'                       => 1,
					'unit.create'                     => 1,
					'unit.delete'                     => 1,
					'unit.edit'                       => 1,
					'unit.edit.state'                 => 1,
					'order.list'                      => 1,
					'order.list.own'                  => 1,
					'transaction.list'                => 1,
					'transaction.list.own'            => 1,
					'transaction.addfund.direct'      => 1,
					'transaction.addfund.gateway'     => 1,
					'transaction.addfund.direct.own'  => 1,
					'transaction.addfund.gateway.own' => 1,
					'transaction.withdraw'            => 1,
					'transaction.withdraw.own'        => 1,
					'transaction.withdraw.approve'    => 1,
					'emailtemplate.list'              => 1,
					'emailtemplate.edit'              => 1,
					'location.list'                   => 1,
					'location.list.own'               => 1,
					'location.create'                 => 1,
					'location.edit'                   => 1,
					'location.edit.state'             => 1,
					'paymentmethod.list'              => 1,
					'paymentmethod.list.own'          => 1,
					'paymentmethod.create'            => 1,
					'paymentmethod.edit'              => 1,
					'message.list'                    => 1,
					'message.list.own'                => 1,
					'message.create'                  => 1,
					'message.edit'                    => 1,
					'message.edit.own'                => 1,
					'rating.list'                     => 1,
					'rating.list.own'                 => 1,
					'rating.edit.own'                 => 1,
					'location.delete'                 => 1,
					'paymentmethod.delete'            => 1,
					'message.delete'                  => 1,
					'rating.delete'                   => 1,
					'rating.edit.state'               => 1,
					'product.edit.seller.own'         => 1,
					'product.edit.pricing.own'        => 1,
					'product.edit.shipping.own'       => 1,
					'product.edit.related.own'        => 1,
					'order.item.edit.status.own'      => 1,
					'message.reply'                   => 1,
					'message.reply.own'               => 1,
					'message.create.bulk'             => 1,
					'message.html'                    => 1,
					'order.item.edit.status'          => 1,
					'mailqueue.list.own'              => 1,
					'mailqueue.list'                  => 1,
				),
				'staff'    => array(
					'app.manage'                 => 1,
					'app.login'                  => 1,
					'app.login.offline'          => 1,
					'coupon.list'                => 1,
					'coupon.list.own'            => 1,
					'coupon.create'              => 1,
					'coupon.edit.own'            => 1,
					'coupon.delete.own'          => 1,
					'currency.list'              => 1,
					'currency.edit.state'        => 1,
					'product.list'               => 1,
					'product.list.own'           => 1,
					'product.create'             => 1,
					'product.edit.basic'         => 1,
					'product.edit.basic.own'     => 1,
					'product.edit.pricing'       => 1,
					'product.edit.shipping'      => 1,
					'product.edit.related'       => 1,
					'product.edit.seo'           => 1,
					'product.delete.own'         => 1,
					'product.edit.state'         => 1,
					'shippingrule.list'          => 1,
					'shippingrule.create'        => 1,
					'shippingrule.delete'        => 1,
					'shippingrule.edit'          => 1,
					'shippingrule.edit.state'    => 1,
					'shoprule.list'              => 1,
					'shoprule.create'            => 1,
					'shoprule.edit.state'        => 1,
					'splcategory.list'           => 1,
					'splcategory.create'         => 1,
					'splcategory.delete'         => 1,
					'splcategory.edit'           => 1,
					'splcategory.edit.state'     => 1,
					'status.list'                => 1,
					'status.create'              => 1,
					'status.edit.state'          => 1,
					'user.list'                  => 1,
					'unit.list'                  => 1,
					'unit.create'                => 1,
					'unit.delete'                => 1,
					'unit.edit'                  => 1,
					'unit.edit.state'            => 1,
					'order.list'                 => 1,
					'order.list.own'             => 1,
					'emailtemplate.list'         => 1,
					'location.list'              => 1,
					'location.list.own'          => 1,
					'location.create'            => 1,
					'location.edit.state'        => 1,
					'paymentmethod.list'         => 1,
					'paymentmethod.list.own'     => 1,
					'paymentmethod.create'       => 1,
					'message.list.own'           => 1,
					'message.create'             => 1,
					'rating.list'                => 1,
					'rating.list.own'            => 1,
					'rating.delete'              => 1,
					'rating.edit.state'          => 1,
					'product.edit.pricing.own'   => 1,
					'product.edit.shipping.own'  => 1,
					'product.edit.related.own'   => 1,
					'order.item.edit.status.own' => 1,
					'message.reply.own'          => 1,
					'message.create.bulk'        => 1,
					'message.html'               => 1,
					'order.item.edit.status'     => 1,
					'download.list.own'          => 1,
					'download.list'              => 1,
					'license.list'               => 1,
					'license.create'             => 1,
					'license.delete'             => 1,
					'license.edit'               => 1,
					'license.edit.state'         => 1,
				),
				'seller'   => array(
					'app.login'                       => 1,
					'coupon.list.own'                 => 1,
					'coupon.create'                   => 1,
					'coupon.edit.own'                 => 1,
					'coupon.delete.own'               => 1,
					'product.list.own'                => 1,
					'product.create'                  => 1,
					'product.edit.basic.own'          => 1,
					'product.delete.own'              => 1,
					'variant.create'                  => 1,
					'variant.delete.own'              => 1,
					'variant.edit.own'                => 1,
					'shippingrule.list.own'           => 1,
					'shippingrule.delete.own'         => 1,
					'shippingrule.edit.own'           => 1,
					'user.edit.own'                   => 1,
					'order.list.own'                  => 1,
					'transaction.list.own'            => 1,
					'transaction.addfund.gateway.own' => 1,
					'transaction.withdraw.own'        => 1,
					'message.list.own'                => 1,
					'message.create'                  => 1,
					'rating.list.own'                 => 1,
					'product.edit.seller.own'         => 1,
					'product.edit.pricing.own'        => 1,
					'product.edit.shipping.own'       => 1,
					'product.edit.related.own'        => 1,
					'order.item.edit.status.own'      => 1,
					'message.reply.own'               => 1,
					'message.html'                    => 1,
					'mailqueue.list.own'              => 1,
					'download.list.own'               => 1,
				),
				'customer' => array(
					'user.edit.own'                   => 1,
				),
			);

			$rules = array();

			foreach ($ruleSets as $k => $ruleSet)
			{
				if ($gid = ArrayHelper::getValue($groups, $k))
				{
					foreach ($ruleSet as $ruleName => $value)
					{
						$rules[$ruleName][$gid] = $value;
					}
				}
			}

			$asset->rules = json_encode($rules);

			$asset->check();
			$asset->store();
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
			'sellacious/components/com_sellacious/layouts/views/activation/default.php',
			'sellacious/components/com_sellacious/layouts/views/products/default_modal.php',
			'sellacious/includes/toolbar.php',
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
			'modules/mod_sellacious_hyperlocal/tmpl/empty.php',
			'libraries/sellacious/objects/TCPDF/TCPDF.phar'
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
	 * @since   1.5.2
	 *
	 * @throws  Exception
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

			// Create the required user groups
			$uParams     = JComponentHelper::getParams('com_users');
			$regGroup    = $uParams->get('new_usertype', 2);
			$guestGroup  = $uParams->get('guest_usergroup', 1);
			$sellerGroup = 0;
			$staffGroup  = 0;
			$adminGroup  = 0;

			$table = JTable::getInstance('Usergroup');
			$group = array('parent_id' => $regGroup, 'title' => 'Seller');

			if ($table->load($group) || ($table->bind($group) && $table->check() && $table->store()))
			{
				$sellerGroup = $table->get('id');
				JUserHelper::addUserToGroup($me->id, $sellerGroup);
			}

			$table = JTable::getInstance('Usergroup');
			$group = array('parent_id' => $regGroup, 'title' => 'Staff');

			if ($table->load($group) || ($table->bind($group) && $table->check() && $table->store()))
			{
				$staffGroup = $table->get('id');
			}

			$table  = JTable::getInstance('Usergroup');
			$group1 = array('title' => 'Administrator');
			$group2 = array('parent_id' => $regGroup, 'title' => 'Shop Administrator');

			if ($table->load($group2) || $table->load($group1) || ($table->bind($group2) && $table->check() && $table->store()))
			{
				$adminGroup = $table->get('id');
				JUserHelper::addUserToGroup($me->id, $adminGroup);
			}

			// Update configuration with these groups
			$helper->config->set('usergroups_client', array($guestGroup, $regGroup));
			$helper->config->set('usergroups_seller', array($sellerGroup));
			$helper->config->set('usergroups_staff', array($staffGroup));
			$helper->config->set('usergroups_company', array($adminGroup));

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			try
			{
				$query->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($regGroup))))
					->where('type = ' . $db->q('client'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			try
			{
				$query->clear()
					->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($sellerGroup))))
					->where('type = ' . $db->q('seller'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			try
			{
				$query->clear()
					->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($staffGroup))))
					->where('type = ' . $db->q('staff'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			// Now set permissions
			$groups = array(
				'admin'    => $adminGroup,
				'staff'    => $staffGroup,
				'seller'   => $sellerGroup,
				'customer' => $regGroup,
			);

			$this->setPermissions($groups);
		}
	}
}
