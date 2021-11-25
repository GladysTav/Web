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
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Include dependencies
jimport('sellacious.loader');

/**
 * Sellacious GST plugin
 *
 * @since  1.7.3
 */
class plgSystemSellaciousGst extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
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
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array  $config    An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.3
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		JTable::addIncludePath(__DIR__ . '/tables');
		JTable::addIncludePath(JPATH_SELLACIOUS . '/components/com_sellacioustemplates/tables');
	}

	/**
	 * Adds additional fields to the sellacious field editing form
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

		// Check we are manipulating a valid form.
		if ($this->hasConfig && ($form instanceof JForm))
		{
			$name     = $form->getName();
			$obj      = is_array($data) ? ArrayHelper::toObject($data) : $data;
			$registry = new Registry($obj);

			if ($name == 'com_sellacious.user' && $obj->seller->category_id)
			{
				$formPath = $this->pluginPath . '/forms/seller.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);
			}
			elseif ($name == 'com_sellacious.product')
			{
				$formPath = $this->pluginPath . '/forms/product.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);

				$product_type = $registry->get('basic.type', 'physical');
				$editFields   = $this->helper->config->get('product_fields');
				$editFields   = new Registry($editFields);
				$editCols     = $editFields->extract($product_type) ?: new Registry;

				if (!$editCols->get('hsn_sac'))
				{
					$form->removeField('hsn_sac', 'basic.gst');
				}
			}
			elseif ($name == 'com_sellacious.profile' && isset($obj->seller))
			{
				$formPath = $this->pluginPath . '/forms/seller.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);
			}
			elseif ($name == 'com_sellacious.cart.checkoutform' && $this->helper->config->get('ask_buyer_gst', 0 , 'plg_system_sellaciousgst'))
			{
				$formPath = $this->pluginPath . '/forms/buyer.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);
			}
			elseif ($name == 'com_sellacioustemplates.template')
			{
				$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

				if (isset($array['context']))
				{
					$context = explode('.', $array['context']);

					if ($context[0] == 'view_order' || $context[0] == 'backoffice_order')
					{
						$form->loadFile(__DIR__ . '/forms/template.xml', false);
					}
				}
			}
			elseif ($name == 'com_sellacious.emailtemplate')
			{
				$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;
				$context = isset($array['context']) ? explode('.', $array['context']) : array();

				if (isset($context[0]))
				{
					$prefix = explode('_', $context[0]);

					if ($prefix[0] == 'order')
					{
						$form->loadFile(__DIR__ . '/forms/template.xml', false);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to add GST replacements to email template
	 *
	 * @param   string    $context      The context for the data
	 * @param   Registry  $data         The data object
	 * @param   array     $replacements Array of replacements by code
	 *
	 * @since   1.7.3
	 */
	public function onParseTemplate($context, $data, &$replacements)
	{
		if ($context == 'com_sellacious.email.order')
		{
			$helper  = SellaciousHelper::getInstance();

			$checkoutForms  = new Registry($data->get('checkout_forms'));
			$checkoutForms  = $checkoutForms->toArray();

			$gst_number = $helper->config->get('gst_num', 0 , 'plg_system_sellaciousgst');
			$pan_number = $helper->config->get('pan_num', 0 , 'plg_system_sellaciousgst');

			$buyer_gst = array_filter($checkoutForms, function ($item) {
				return $item['field_name'] == 'gst_num';
			});

			$buyer_gst_number = !empty($buyer_gst) ? $buyer_gst[0]['value'] : '&nbsp;';

			$gstValues = array(
				'pan_number'       => $pan_number,
				'gst_number'       => $gst_number,
				'buyer_gst_number' => $buyer_gst_number,
			);

			$replacements = array_merge($replacements, $gstValues);
		}
		elseif ($context == 'com_sellacious.email.order.product')
		{
			if (!$data instanceof Registry)
			{
				$data = new Registry($data);
			}

			$sellerGstTable = JTable::getInstance('SellerGst', 'SellaciousTable');
			$sellerGstTable->load(array('user_id' => $data->get('seller_uid')));

			$prodGstTable = JTable::getInstance('ProductGst', 'SellaciousTable');
			$prodGstTable->load(array('product_id' => $data->get('product_id')));

			$values       = array(
				'seller_gst_number' => $sellerGstTable->get('gst_num'),
				'product_hsn_sac'   => $prodGstTable->get('hsn_sac'),
			);
			$replacements = array_merge($replacements, $values);
		}
	}

	/**
	 * Method to show gst field values in checkout forms
	 *
	 * @param  string    $context  The calling context
	 * @param  Registry  $data     The data object
	 * @param  array     $values   Array of Form values
	 *
	 * @since 1.7.3
	 */
	public function onRenderFormValues($context, $data, &$values)
	{
		if ($context == 'com_sellacious.cart.checkoutform')
		{
			$checkoutform = $data->get('checkoutform');

			if (isset($checkoutform->gst))
			{
				$value = $checkoutform->gst->gst_num;
				$input = new stdClass;

				$input->field_id   = 0;
				$input->field_name = 'gst_num';
				$input->label      = JText::_('PLG_SYSTEM_SELLACIOUSGST_SELLER_GST_NUM_LABEL');
				$input->value      = $value;
				$input->html       = $this->helper->field->renderValue($value, 'text');

				$values[] = $input;
			}
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string $context The context for the data
	 * @param   object $data    An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 *
	 * @throws  \Exception
	 */
	public function onContentPrepareData($context, $data)
	{
		parent::onContentPrepareData($context, $data);

		$registry = new Registry($data);
		$seller   = $registry->get('seller');

		if ($context == 'com_sellacious.user' || ($context == 'com_sellacious.profile' && !empty($seller)))
		{
			$table = JTable::getInstance('Seller', 'SellaciousTable');

			if (isset($seller->id))
			{
				$table->load($seller->id);
			}

			$sellerUid = $table->get('user_id');

			$gstTable = JTable::getInstance('SellerGst', 'SellaciousTable');
			$gstTable->load(array('user_id' => $sellerUid));

			$gst           = $gstTable->getProperties(1);
			$gst['params'] = json_decode($gstTable->get('params'));

			$data = is_array($data) ? ArrayHelper::toObject($data) : $data;

			$data->seller      = isset($data->seller) ? $data->seller : new stdClass();
			$data->seller->gst = (object) $gst;
		}
		elseif ($context == 'com_sellacious.product' && $registry->get('id'))
		{
			$table = JTable::getInstance('Product', 'SellaciousTable');

			$productId = $registry->get('id');
			$table->load($productId);

			$gstTable = JTable::getInstance('ProductGst', 'SellaciousTable');
			$gstTable->load(array('product_id' => $productId));

			$gst = $gstTable->getProperties(1);
			$data = is_array($data) ? ArrayHelper::toObject($data) : $data;

			$data->basic      = isset($data->basic) ? $data->basic : new stdClass();
			$data->basic      = is_array($data->basic) ? ArrayHelper::toObject($data->basic) : $data->basic;
			$data->basic->gst = (object) $gst;
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string $context The calling context
	 * @param   object $table   A JTable object
	 * @param   bool   $isNew   If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 *
	 * @throws  \Exception
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		$app  = JFactory::getApplication();
		$data = $app->input->get('jform', array(), 'array');

		if ($context == 'com_sellacious.user')
		{
			$gst = isset($data['seller']['gst']) ? $data['seller']['gst'] : array();

			if (!empty($gst))
			{
				$gstTable = JTable::getInstance('SellerGst', 'SellaciousTable');
				$gstTable->load(array('user_id' => $table->get('id')));

				$gst['user_id']    = $table->get('id');
				$gst['user_email'] = $table->get('email');
				$gst['params']     = $gst['params'] ? json_encode($gst['params']) : '';

				$gstTable->bind($gst);
				$gstTable->check();
				$gstTable->store();
			}
		}
		elseif ($context == 'com_sellacious.product')
		{
			$gst = isset($data['basic']['gst']) ? $data['basic']['gst'] : array();

			if (!empty($gst))
			{
				$gstTable = JTable::getInstance('ProductGst', 'SellaciousTable');
				$gstTable->load(array('product_id' => $table->get('id')));

				$gst['product_id'] = $table->get('id');

				$gstTable->bind($gst);
				$gstTable->check();
				$gstTable->store();
			}
		}
	}

	/**
	 * Method to add or alter product object
	 *
	 * @param   string     $context  The calling context
	 * @param   \stdClass  $item     The product object in question
	 *
	 * @since   1.7.3
	 */
	public function onProcessProduct($context, $item)
	{
		if ($context == 'com_sellacious.product')
		{
			$prodGstTable = JTable::getInstance('ProductGst', 'SellaciousTable');
			$prodGstTable->load(array('product_id' => $item->product_id));

			$hsn             = $prodGstTable->get('hsn_sac', '');
			$hsn_sac_display = (array) $this->helper->config->get('hsn_sac_display', '', 'plg_system_sellaciousgst');

			if (!empty($hsn) && in_array('product', $hsn_sac_display))
			{
				$spec              = new stdClass();
				$spec->id          = 0;
				$spec->title       = JText::_('PLG_SYSTEM_SELLACIOUSGST_PRODUCT_HSN_SAC');
				$spec->parent_id   = 1;
				$spec->type        = 'text';
				$spec->group_title = JText::_('PLG_SYSTEM_SELLACIOUSGST_PRODUCT_GST_INFO');
				$spec->value       = $hsn;

				array_push($item->specifications, $spec);
			}
		}
	}

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
			$row->value   = 'hsn_sac';
			$row->text    = 'HSN/SAC';
			$row->columns = null;

			$rows['hsn_sac'] = $row;
		}
	}

	/**
	 * Method to fetch order page template
	 *
	 * @param   $context  string     The calling context
	 * @param   $data    \JRegistry  The data object
	 * @param   $html     string     The html layout
	 * @param   $options  array      Extra Options
	 *
	 * @since   1.7.3
	 */
	public function OnParseViewTemplate($context, $data, &$html, $options = array())
	{
		if ($context == 'com_sellacious.order.print')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.print'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseViewTemplate($data, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.order.pdf')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.pdf'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseViewTemplate($data, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.order.invoice')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.invoice'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseViewTemplate($data, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.backoffice.order.invoice')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'backoffice_order.invoice'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseViewTemplate($data, $html, $context, $options);
			}
		}
	}

	/**
	 * Method to replace short codes in template preview html
	 *
	 * @param  string  $context          The calling context
	 * @param  string  $templateContext  The template context
	 * @param  string  $body             The html string which needs to be replaced
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.3
	 */
	public function onTemplatePreview($context, $templateContext, &$body)
	{
		if ($context == 'com_sellacious.viewtemplate')
		{
			$gst_number = $this->helper->config->get('gst_num', '', 'plg_system_sellaciousgst');
			$pan_number = $this->helper->config->get('pan_num', '', 'plg_system_sellaciousgst');

			$replacements = array(
				'pan_number'       => $pan_number,
				'gst_number'       => $gst_number,
				'buyer_gst_number' => '123456789 RT 0001',
			);

			$replacements = array_change_key_case($replacements, CASE_UPPER);

			foreach ($replacements as $code => $replacement)
			{
				$body = str_ireplace('%' . $code . '%', $replacement, $body);
			}
		}
	}

	/**
	 * Method to replace view template short codes for a particular product row
	 *
	 * @param   string     $context       The calling context
	 * @param   \stdClass  $product       The product object
	 * @param   array      $replacements  Array of short code replacements
	 *
	 * @since   1.7.3
	 */
	public function onParseProductRow($context, $product, &$replacements)
	{
		if ($context == 'com_sellacious.templates.product')
		{
			$sellerGstTable = JTable::getInstance('SellerGst', 'SellaciousTable');
			$sellerGstTable->load(array('user_id' => $product->seller_uid));

			$prodGstTable = JTable::getInstance('ProductGst', 'SellaciousTable');
			$prodGstTable->load(array('product_id' => $product->product_id));

			$replacements['seller_gst_number'] = $sellerGstTable->get('gst_num');
			$replacements['product_hsn_sac']   = $prodGstTable->get('hsn_sac');
		}
	}

	/**
	 * Get the HTML for the view template body for the given order
	 *
	 * @param    $order   \JRegistry  The order object
	 * @param    $body     string     The html body
	 * @param    $context  string     The calling context
	 * @param    $options  array      Extra options
	 *
	 * @since    1.7.3
	 */
	public function parseViewTemplate($order, &$body, $context, $options = array())
	{
		// Process order data first.
		$checkoutForms = new Registry($order->get('checkout_forms'));
		$checkoutForms = $checkoutForms->toArray();

		$gst_number = $this->helper->config->get('gst_num', '', 'plg_system_sellaciousgst');
		$pan_number = $this->helper->config->get('pan_num', '', 'plg_system_sellaciousgst');

		$buyer_gst = array_values(array_filter($checkoutForms, function ($item) {
			return (isset($item['field_name']) && $item['field_name'] == 'gst_num');
		}));

		$buyer_gst_number = !empty($buyer_gst) ? $buyer_gst[0]['value'] : '&nbsp;';

		$replacements = array(
			'pan_number'       => $pan_number,
			'gst_number'       => $gst_number,
			'buyer_gst_number' => $buyer_gst_number,
		);

		$replacements = array_change_key_case($replacements, CASE_UPPER);

		foreach ($replacements as $code => $replacement)
		{
			$body = str_ireplace('%' . $code . '%', $replacement, $body);
		}
	}
}
