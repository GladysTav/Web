<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Product;

defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

/**
 * Product Redirect Plugin
 *
 * @since  1.7.3
 */
class plgSystemSellaciousProductRedirect extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.7.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Whether this class has a configuration to inject into sellacious configurations
	 *
	 * @var    bool
	 *
	 * @since  1.7.3
	 */
	protected $hasConfig = true;

	/**
	 * Method to add more rows and columns to check matrix field
	 *
	 * @param   string  $context  The calling context
	 * @param   array   $rows     Array of vector rows
	 * @param   array   $columns  Array of vector columns
	 *
	 * @since   1.7.3
	 */
	public function onMatrixGetVectors($context, &$rows, &$columns)
	{
		if ($context == 'com_sellacious.field.checkmatrix')
		{
			$row          = new stdClass();
			$row->value   = 'custom_product_url';
			$row->text    = JText::_('PLG_SYSTEM_SELLACIOUSPRODUCTREDIRECT_CONFIG_PRODUCT_FIELDS_OPTION_CUSTOM_PRODUCT_URL');
			$row->columns = null;

			$rows['custom_product_url'] = $row;
		}
	}

	/**
	 * Runs on form preparation
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.3
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm)
		{
			$name     = $form->getName();
			$registry = new Registry($data);

			$multiVariant = $this->helper->config->get('multi_variant', 0);
			$variantEdit  = $this->helper->access->check('variant.create')
				|| $this->helper->access->check('variant.edit')
				|| $this->helper->access->check('variant.edit.own');
			$isNew        = $registry->get('id', 0) == 0;

			if ($name == 'com_sellacious.product' && !$isNew && $variantEdit && $multiVariant)
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'plg_system_sellaciousproductredirect/product.js', false, true);

				$form->loadFile(__DIR__ . '/forms/product.xml', false);

				$product_type = $registry->get('basic.type', 'physical');
				$editFields   = $this->helper->config->get('product_fields');
				$editFields   = new Registry($editFields);
				$editCols     = $editFields->extract($product_type) ?: new Registry;

				if (!$editCols->get('custom_product_url'))
				{
					$form->removeField('custom_url_type', 'basic.params');
					$form->removeField('custom_product_url', 'basic.params');
					$form->removeField('custom_url_advanced', 'basic.params');
					$form->removeField('custom_product_menu', 'basic.params');
					$form->removeField('custom_url_append_query', 'basic.params');
				}
			}
			elseif ($name == 'com_sellacious.variant')
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'plg_system_sellaciousproductredirect/variant.js', false, true);

				$form->loadFile(__DIR__ . '/forms/variant.xml', false);

				$product_id   = $registry->get('product_id', 0);
				$product      = $this->helper->product->getItem($product_id);
				$product_type = $product->type;
				$editFields   = $this->helper->config->get('product_fields');
				$editFields   = new Registry($editFields);
				$editCols     = $editFields->extract($product_type) ?: new Registry;

				if (!$editCols->get('custom_product_url'))
				{
					$form->removeField('custom_url_type', 'params');
					$form->removeField('custom_product_url', 'params');
					$form->removeField('custom_url_advanced', 'params');
					$form->removeField('custom_product_menu', 'params');
					$form->removeField('custom_url_append_query', 'params');
				}
			}
		}

		return true;
	}

	/**
	 * This method redirects product page to a custom url
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.3
	 */
	public function onAfterRoute()
	{
		$option = $this->app->input->get('option');
		$view   = $this->app->input->get('view');
		$code   = $this->app->input->get('p');
		$found  = $this->helper->product->parseCode($code, $productId, $variantId, $sellerUid);

		if ($this->app->isClient('site') && $option == 'com_sellacious' && $view == 'product' && $found)
		{
			$product = new Product($productId, $variantId, $sellerUid);

			$params           = new Registry($product->get('params'));
			$variant_params   = new Registry($product->get('variant_params'));
			$custom_url_type  = $params->get('custom_url_type', 0);
			$variant_url_type = $variant_params->get('custom_url_type', '');

			if ($variant_url_type != '')
			{
				$custom_url_type         = $variant_url_type;
				$custom_product_url      = $variant_params->get('custom_product_url', '');
				$custom_product_menu     = $variant_params->get('custom_product_menu', '');
				$custom_url_append_query = $variant_params->get('custom_url_append_query', '');
			}
			else
			{
				$custom_product_url      = $params->get('custom_product_url', '');
				$custom_product_menu     = $params->get('custom_product_menu', '');
				$custom_url_append_query = $params->get('custom_url_append_query', '');
			}

			if ($custom_url_type == 2 && $custom_product_menu > 0)
			{
				$menu               = $this->app->getMenu('site');
				$item               = $menu->getItem($custom_product_menu);
				$link               = new JURI($item->link);
				$custom_product_url = $link->toString();

				if (!empty($custom_product_url))
				{
					$base               = JUri::getInstance()->toString(array('scheme', 'host', 'port'));
					$custom_product_url = $base . JRoute::link('site', $custom_product_url, false);
				}
			}

			if ($custom_url_type > 0 && !empty($custom_product_url))
			{
				$link = new JURI($custom_product_url);

				if (!empty($custom_url_append_query))
				{
					$link->setVar($custom_url_append_query, $code);
				}

				$this->app->redirect($link->toString());
			}
		}
	}
}
