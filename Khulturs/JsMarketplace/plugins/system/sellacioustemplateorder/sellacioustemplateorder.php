<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Product;

defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

/**
 * Sellacious template order plugin
 *
 * @since  1.7.0
 */
class plgSystemSellaciousTemplateOrder extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	public function __construct($subject, array $config)
	{
		JTable::addIncludePath(JPATH_SITE . '/sellacious/components/com_sellacioustemplates/tables');

		parent::__construct($subject, $config);
	}

	/**
	 * Fetch the available context of view template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function onFetchTemplateContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.viewtemplate')
		{
			$contexts['view_order.print']   = JText::_('PLG_SELLACIOUSTEMPLATEORDER_CONTEXT_VIEW_ORDER_PRINT');
			$contexts['view_order.pdf']     = JText::_('PLG_SELLACIOUSTEMPLATEORDER_CONTEXT_VIEW_ORDER_PDF');
			$contexts['view_order.invoice'] = JText::_('PLG_SELLACIOUSTEMPLATEORDER_CONTEXT_VIEW_ORDER_INVOICE');
			$contexts['backoffice_order.invoice'] = JText::_('PLG_SELLACIOUSTEMPLATEORDER_CONTEXT_BACKOFFICE_ORDER_INVOICE');
		}
	}

	/**
	 * Adds order print view fields
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() != 'com_sellacioustemplates.template')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchTemplateContext('com_sellacious.viewtemplate', $contexts);

		if (!empty($contexts))
		{
			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				$form->loadFile(__DIR__ . '/forms/order.xml', false);
			}
		}
	}

	/**
	 * Method to fetch order page template for print view
	 *
	 * @param   $context  string      The calling context
	 * @param   $order    \JRegistry  The order object
	 * @param   $html     string      The html layout
	 * @param   $options  array       Extra Options
	 *
	 * @since   1.7.0
	 */
	public function onParseViewTemplate($context, $order, &$html, $options = array())
	{
		if ($context == 'com_sellacious.order.print')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.print'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseTemplate($order, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.order.pdf')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.pdf'));

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseTemplate($order, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.order.invoice')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'view_order.invoice'));

			JHtml::_('jquery.framework');

			$document = JFactory::getDocument();
			$script = <<<JS
			jQuery(document).ready(function($){
				var headings = parseInt($('.order-items thead tr th').length);
				$('.ship-title, .overall-billing').attr('colspan', headings);
				$('.total-shipping, .sub-total, .grand-total, .rounded-total, .shipping_taxes_breakdown, .cart_taxes_breakdown').attr('colspan', headings - 2);
			});
JS;

			$document->addScriptDeclaration($script);

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseTemplate($order, $html, $context, $options);
			}
		}
		elseif ($context == 'com_sellacious.backoffice.order.invoice')
		{
			$template = JTable::getInstance('Template', 'SellaciousTable');
			$template->load(array('context' => 'backoffice_order.invoice'));

			JHtml::_('jquery.framework');

			$document = JFactory::getDocument();
			$script = <<<JS
			jQuery(document).ready(function($){
				var headings = parseInt($('.order-items thead tr th').length);
				$('.ship-title, .overall-billing').attr('colspan', headings);
				$('.total-shipping, .sub-total, .grand-total, .rounded-total, .shipping_taxes_breakdown, .cart_taxes_breakdown').attr('colspan', headings - 2);
			});
JS;

			$document->addScriptDeclaration($script);

			if ($template->get('state'))
			{
				$html = empty($html) ? $template->get('body') : $html;

				$this->parseTemplate($order, $html, $context, $options);
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
	 * @since   1.7.0
	 */
	public function onTemplatePreview($context, $templateContext, &$body)
	{
		if ($context == 'com_sellacious.viewtemplate')
		{
			$base       = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$c_currency = $this->helper->currency->current('code_3');
			$g_currency = $this->helper->currency->getGlobal('code_3');

			$shopLogo    = ImageHelper::getImage('config', 1, 'shop_logo');
			$paymentLogo = ImageHelper::getImage('paymentmethod', 3, 'logo');

			if ($templateContext == 'view_order.pdf')
			{
				if ($shopLogo)
				{
					$shopLogoPath = $shopLogo->getPath(true);
				}
				else
				{
					$shopLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getPath(true);
				}

				if ($paymentLogo)
				{
					$paymentLogoPath = $paymentLogo->getPath(true);
				}
				else
				{
					$paymentLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getPath(true);
				}
			}
			else
			{
				if ($shopLogo)
				{
					$shopLogoPath = $shopLogo->getUrl();
				}
				else
				{
					$shopLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getUrl();
				}

				if ($paymentLogo)
				{
					$paymentLogoPath = $paymentLogo->getUrl();
				}
				else
				{
					$paymentLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getUrl();
				}
			}

			$replacements = array(
				'sitename'                  => JFactory::getConfig()->get('sitename'),
				'shop_logo'                 => $shopLogoPath,
				'shop_name'                 => $this->helper->config->get('shop_name'),
				'shop_address'              => nl2br($this->helper->config->get('shop_address')),
				'shop_country'              => $this->helper->location->loadResult(array(
					'list.select' => 'a.title',
					'id'          => $this->helper->config->get('shop_country'),
				)),
				'shop_phone1'               => $this->helper->config->get('shop_phone1') ? '<i class="fa fa-phone"></i> ' . $this->helper->config->get('shop_phone1') : '',
				'shop_phone2'               => $this->helper->config->get('shop_phone2') ? '<i class="fa fa-mobile-phone"></i> ' . $this->helper->config->get('shop_phone2') : '',
				'shop_email'                => $this->helper->config->get('shop_email') ? '<i class="fa fa-envelope-o"></i> ' . $this->helper->config->get('shop_email') : '',
				'shop_website'              => $this->helper->config->get('shop_website') ? '<i class="fa fa-globe"></i> ' . $this->helper->config->get('shop_website') : '',
				'order_date'                => JHtml::_('date', 'now', 'F d, Y'),
				'order_id'                  => '5',
				'order_number'              => '190005',
				'customer_name'             => 'John Doe',
				'customer_email'            => 'johndoe@gmail.com',
				'billing_name'              => 'John Doe',
				'billing_address'           => '3683 Chardonnay Drive Redmond, WA 98052',
				'billing_company'           => '<span class="address_company">LMNOP</span><br>',
				'billing_district'          => '',
				'billing_landmark'          => '',
				'billing_state'             => 'Arizona',
				'billing_zip'               => '98052',
				'billing_country'           => 'United States',
				'billing_mobile'            => '3602505420',
				'billing_po_box'            => '',
				'shipping_name'             => 'John Doe',
				'shipping_address'          => '3683 Chardonnay Drive Redmond, WA 98052',
				'shipping_company'          => 'LMNOP',
				'shipping_district'         => '',
				'shipping_landmark'         => '',
				'shipping_state'            => 'Arizona',
				'shipping_zip'              => '98052',
				'shipping_country'          => 'United States',
				'shipping_po_box'           => '',
				'shipping_mobile'           => '3602505420',
				'cart_subtotal'             => $this->helper->currency->display('594.22', $c_currency, $c_currency, true),
				'cart_subtotal_ex_tax'      => $this->helper->currency->display('606.25', $c_currency, $c_currency, true),
				'cart_total'                => $this->helper->currency->display('598.22', $c_currency, $c_currency, true),
				'product_taxes'             => $this->helper->currency->display('0', $c_currency, $c_currency, true),
				'product_discounts'         => $this->helper->currency->display('12.03', $c_currency, $c_currency, true),
				'cart_taxes'                => $this->helper->currency->display('0.89133', $c_currency, $c_currency, true),
				'cart_discounts'            => $this->helper->currency->display('11.8844', $c_currency, $c_currency, true),
				'order_total'               => $this->helper->currency->display('587.22693', $c_currency, $c_currency, true),
				'payment_total'             => $this->helper->currency->display('587.22693', $c_currency, $c_currency, true),
				'cart_shipping'             => $this->helper->currency->display('4', $c_currency, $c_currency, true),
				'product_shipping_subtotal' => $this->helper->currency->display('0', $c_currency, $c_currency, true),
				'payment_method'            => 'Cash On Delivery',
				'coupon_code'               => JText::_('PLG_SELLACIOUSTEMPLATEORDER_NO_COUPON_APPLIED'),
				'coupon_code_amount'        => JText::_('PLG_SELLACIOUSTEMPLATEORDER_COUPON_AMOUNT_EMPTY'),
				'payment_status'            => '<span class="text-success">PAID</span>',
				'payment_logo'              => $paymentLogoPath,
				'processing_fee'            => JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', 'Cash On Delivery'),
				'processing_fee_amount'     => $this->helper->currency->display('0', $c_currency, $c_currency, true),
				'shipping_rule'             => 'Same day Delivery',
				'shipping_service'          => '',
				'status_old'                => '',
				'status_new'                => '',
				'checkout_form'             => '',
				'shipping_params'           => '',
				'seller_name'               => 'James Stuart',
				'seller_address'            => '3312 August Lane Natchitoches, LA 71457',
				'seller_phone'              => '3183650548',
				'seller_website'            => '',
				'seller_email'              => 'james@yopmail.com',
			);

			$replacements = array_change_key_case($replacements, CASE_UPPER);

			foreach ($replacements as $code => $replacement)
			{
				$body = str_ireplace('%' . $code . '%', $replacement, $body);
			}

			// Check if we have any product rows to process.
			$pattern = '@(%GRID_BEGIN%).*?<tbody>(.*?)</tbody>.*?(%GRID_END%)@s';
			$found   = preg_match($pattern, $body, $match);

			if ($found)
			{
				// 1st Product
				$product = array(
					'product_id'                  => 133,
					'product_title'               => 'Maxima 31331PPGN Hybrid Analog Watch - For Men',
					'product_sku'                 => '',
					'product_quantity'            => 5,
					'product_seller'              => 'Sellacious',
					'product_seller_name'         => 'Sellacious',
					'product_seller_username'     => 'admin',
					'product_seller_email'        => 'admin@sellacious.com',
					'product_seller_store'        => 'Sellacious',
					'product_seller_company'      => 'Sellacious',
					'product_seller_code'         => 'WEBMASTER',
					'product_seller_contact'      => '000011001100',
					'product_price'               => $this->helper->currency->display('1.00', $c_currency, $c_currency, true),
					'product_tax'                 => $this->helper->currency->display('0.00', $c_currency, $c_currency, true),
					'product_discount'            => $this->helper->currency->display('0.00', $c_currency, $c_currency, true),
					'product_sales_price'         => $this->helper->currency->display('1.00', $c_currency, $c_currency, true),
					'product_subtotal'            => $this->helper->currency->display('5.00', $c_currency, $c_currency, true),
					'product_subtotal_ex_tax'     => $this->helper->currency->display(1.00 * 5, $c_currency, $c_currency, true),
					'product_total_tax_rate'      => sprintf('%s %%', 0),
					'product_shipping'            => $this->helper->currency->display('0.00', $c_currency, $c_currency, true),
					'product_shipping_rule'       => '',
					'product_image'               => $base . $this->helper->media->getBlankImage(true),
					'product_status_old'          => 'NA',
					'product_status_new'          => 'NA',
					'product_status_creator'      => 'John Doe',
					'product_status_creator_role' => JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER'),
					'product_status_created_date' => JHtml::_('date', 'now', 'F d, Y h:i A T'),
					'product_status_log'          => '',
					'product_shoprules'           => '',
				);

				$products[] = array_change_key_case($product, CASE_UPPER);

				// 2nd Product
				$product = array(
					'product_id'                  => 247,
					'product_title'               => 'Voltas 1.5 Ton Split AC|5 Star ',
					'product_sku'                 => '',
					'product_quantity'            => 1,
					'product_seller'              => 'Sellacious',
					'product_seller_name'         => 'Sellacious',
					'product_seller_username'     => 'admin',
					'product_seller_email'        => 'admin@sellacious.com',
					'product_seller_store'        => 'Sellacious',
					'product_seller_company'      => 'Sellacious',
					'product_seller_code'         => 'WEBMASTER',
					'product_seller_contact'      => '000011001100',
					'product_price'               => $this->helper->currency->display('601.25', $c_currency, $c_currency, true),
					'product_tax'                 => $this->helper->currency->display('0.00', $c_currency, $c_currency, true),
					'product_discount'            => $this->helper->currency->display('12.03', $c_currency, $c_currency, true),
					'product_sales_price'         => $this->helper->currency->display('589.23', $c_currency, $c_currency, true),
					'product_subtotal'            => $this->helper->currency->display('589.23', $c_currency, $c_currency, true),
					'product_subtotal_ex_tax'     => $this->helper->currency->display(601.25 * 1, $c_currency, $c_currency, true),
					'product_total_tax_rate'      => sprintf('%s %%', 0),
					'product_shipping'            => $this->helper->currency->display('0.00', $c_currency, $c_currency, true),
					'product_shipping_rule'       => '',
					'product_image'               => $base . $this->helper->media->getBlankImage(true),
					'product_status_old'          => 'NA',
					'product_status_new'          => 'NA',
					'product_status_creator'      => 'John Doe',
					'product_status_creator_role' => JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER'),
					'product_status_created_date' => JHtml::_('date', 'now', 'F d, Y h:i A T'),
					'product_status_log'          => '',
					'product_shoprules'           => '',
				);

				$products[] = array_change_key_case($product, CASE_UPPER);

				while ($found)
				{
					reset($products);
					$rows = array();

					$productRow = $match[2];

					// In each set the groups are => 0:%GRID_BEGIN%, 1:DESIRED_ROW, 2:%GRID_END%
					foreach ($products as $product)
					{
						$row = trim($match[2]);

						foreach ($product as $code => $replacement)
						{
							$row = str_ireplace('%' . $code . '%', $replacement, $row);
						}

						$rows[] = $row;

						if (!empty($product['PRODUCT_SHOPRULES']) && ($context == 'com_sellacious.order.print' || $context == 'com_sellacious.order.pdf'))
						{
							$rows[] = $product['PRODUCT_SHOPRULES'];
						}
					}

					$output = str_ireplace(array($match[1], $productRow, $match[3]), array('', implode("\n", $rows), ''), $match[0]);
					$body   = str_ireplace($match[0], $output, $body);

					// Find next match after processing previous one.
					$found = preg_match($pattern, $body, $match);
				}
			}
		}
	}

	/**
	 * Get the HTML for the print view template body for the given order
	 *
	 * @param    $order   \JRegistry  The order object
	 * @param    $body     string     The html body
	 * @param    $context  string     The calling context
	 * @param    $options  array      Extra options
	 *
	 * @since    1.7.0
	 */
	public function parseTemplate($order, &$body, $context, $options = array())
	{
		$helper    = SellaciousHelper::getInstance();
		$sellerUid = ArrayHelper::getValue($options, 'seller_uid', 0);

		// Process order data first.
		$checkoutForms = new Registry($order->get('checkout_forms'));
		$checkoutForms = $checkoutForms->toArray();

		$shippingParams = new Registry($order->get('shipping_params'));
		$shippingParams = $shippingParams->toArray();

		$items                = $order->get('items');
		$sellers              = ArrayHelper::getColumn($items, 'seller_company');
		$cartTaxes            = $order->get('cart_taxes', 0);
		$cartDiscounts        = $order->get('cart_discounts', 0);
		$prodShipping         = $order->get('product_shipping', 0);
		$prodShippingSubTotal = $order->get('product_shipping_subtotal', 0);

		$c_currency = $helper->currency->current('code_3');
		$g_currency = $helper->currency->getGlobal('code_3');

		$shopLogo    = ImageHelper::getImage('config', 1, 'shop_logo');
		$paymentLogo = ImageHelper::getImage('paymentmethod', $order->get('payment.method_id'), 'logo');

		if ($context == 'com_sellacious.order.pdf')
		{
			if ($shopLogo)
			{
				$shopLogoPath = $shopLogo->getPath(true);
			}
			else
			{
				$shopLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getPath(true);
			}

			if ($paymentLogo)
			{
				$paymentLogoPath = $paymentLogo->getPath(true);
			}
			else
			{
				$paymentLogoPath = ImageHelper::getBlank('com_sellacioustemplates/template')->getPath(true);
			}
		}
		else
		{
			$shopLogoPath    = $this->helper->media->getImage('config.shop_logo', 1);
			$paymentLogoPath = $this->helper->media->getImage('paymentmethod.logo', $order->get('payment.method_id'));
		}

		$replacements = array(
			'sitename'                  => JFactory::getConfig()->get('sitename'),
			'shop_logo'                 => $shopLogoPath,
			'shop_name'                 => $helper->config->get('shop_name'),
			'shop_address'              => nl2br($helper->config->get('shop_address')),
			'shop_country'              => $helper->location->loadResult(array(
				'list.select' => 'a.title',
				'id'          => $helper->config->get('shop_country'),
			)),
			'shop_phone1'               => $helper->config->get('shop_phone1') ? '<i class="fa fa-phone"></i> ' . $helper->config->get('shop_phone1') : '',
			'shop_phone2'               => $helper->config->get('shop_phone2') ? '<i class="fa fa-mobile-phone"></i> ' . $helper->config->get('shop_phone2') : '',
			'shop_email'                => $helper->config->get('shop_email') ? '<i class="fa fa-envelope-o"></i> ' . $helper->config->get('shop_email') : '',
			'shop_website'              => $helper->config->get('shop_website') ? '<i class="fa fa-globe"></i> ' . $helper->config->get('shop_website') : '',
			'order_date'                => JHtml::_('date', $order->get('created'), 'F d, Y'),
			'order_id'                  => $order->get('id'),
			'order_number'              => $order->get('order_number'),
			'customer_name'             => $order->get('customer_name'),
			'customer_email'            => $order->get('customer_email'),
			'billing_name'              => $order->get('bt_name'),
			'billing_address'           => $order->get('bt_address'),
			'billing_company'           => $order->get('bt_company') ? '<span class="address_company">' . $order->get('bt_company') . '</span><br>' : '',
			'billing_district'          => $order->get('bt_district'),
			'billing_landmark'          => $order->get('bt_landmark'),
			'billing_state'             => $order->get('bt_state'),
			'billing_zip'               => $order->get('bt_zip'),
			'billing_country'           => $order->get('bt_country'),
			'billing_mobile'            => $order->get('bt_mobile'),
			'billing_po_box'            => $order->get('bt_po_box') ? 'P. O. Box #<span class="address_po_box">' . JText::_('COM_SELLACIOUS_ORDER_PO_BOX') . $order->get('bt_po_box') . '</span><br>' : '',
			'shipping_name'             => $order->get('st_name'),
			'shipping_address'          => $order->get('st_address'),
			'shipping_company'          => $order->get('st_company') ? '<span class="address_company">' . $order->get('st_company') . '</span><br>' : '',
			'shipping_district'         => $order->get('st_district'),
			'shipping_landmark'         => $order->get('st_landmark'),
			'shipping_state'            => $order->get('st_state'),
			'shipping_zip'              => $order->get('st_zip'),
			'shipping_country'          => $order->get('st_country'),
			'shipping_po_box'           => $order->get('st_po_box') ? 'P. O. Box #<span class="address_po_box">' . JText::_('COM_SELLACIOUS_ORDER_PO_BOX') . $order->get('st_po_box') . '</span><br>' : '',
			'shipping_mobile'           => $order->get('st_mobile'),
			'cart_subtotal'             => $helper->currency->display($order->get('product_subtotal'), $order->get('currency'), $c_currency, true),
			'cart_subtotal_ex_tax'      => $helper->currency->display($order->get('product_total'), $order->get('currency'), $c_currency, true),
			'cart_total'                => $helper->currency->display($order->get('cart_total'), $order->get('currency'), $c_currency, true),
			'product_taxes'             => $helper->currency->display($order->get('product_taxes'), $order->get('currency'), $c_currency, true),
			'product_discounts'         => $helper->currency->display($order->get('product_discounts'), $order->get('currency'), $c_currency, true),
			'cart_taxes'                => $cartTaxes >= 0.01 ? $helper->currency->display($cartTaxes, $order->get('currency'), $c_currency, true) : JText::_('PLG_SELLACIOUSTEMPLATEORDER_CART_TAXES_EMPTY'),
			'cart_discounts'            => $cartDiscounts >= 0.01 ? $helper->currency->display($cartDiscounts, $order->get('currency'), $c_currency, true) : JText::_('PLG_SELLACIOUSTEMPLATEORDER_CART_DISCOUNTS_EMPTY'),
			'order_total'               => $helper->currency->display($order->get('grand_total'), $order->get('currency'), $c_currency, true),
			'payment_total'             => $helper->currency->display($order->get('payment.id') ? $order->get('payment.amount_payable') : $order->get('grand_total'), $order->get('currency'), $c_currency, true),
			'cart_shipping'             => $prodShipping >= 0.01 ? $helper->currency->display($prodShipping, $order->get('currency'), $c_currency, true) : JText::_('PLG_SELLACIOUSTEMPLATEORDER_CART_SHIPPING_EMPTY'),
			'product_shipping_subtotal' => $helper->currency->display($prodShippingSubTotal, $order->get('currency'), $c_currency, true),
			'payment_method'            => $order->get('payment.method_name'),
			'coupon_code'               => ($coupon = $order->get('coupon')) ? $coupon->code : JText::_('PLG_SELLACIOUSTEMPLATEORDER_NO_COUPON_APPLIED'),
			'coupon_code_amount'        => $coupon ? $helper->currency->display($coupon->amount, $order->get('currency'), $c_currency, true) : JText::_('PLG_SELLACIOUSTEMPLATEORDER_COUPON_AMOUNT_EMPTY'),
			'payment_status'            => $order->get('payment.id') ? '<span class="text-success">PAID</span>' : '<span class="text-danger">UNPAID</span>',
			'payment_logo'              => $paymentLogoPath,
			'processing_fee'            => JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', $order->get('payment.method_name')),
			'processing_fee_amount'     => $helper->currency->display($order->get('payment.fee_amount'), $order->get('currency'), $c_currency, true),
			'shipping_rule'             => $order->get('shipping_rule'),
			'shipping_service'          => $order->get('shipping_service'),
			'status_old'                => $order->get('status_old'),
			'status_new'                => $order->get('status_new'),
			'checkout_form'             => $this->buildHtml($checkoutForms, 'checkout_form'),
			'shipping_params'           => $this->buildHtml($shippingParams, 'shipping_params'),
		);

		if ($sellerUid > 0)
		{
			$seller     = new Sellacious\Seller($sellerUid);
			$sellerProp = $seller->getAttributes();

			$replacements['seller_name']    = $sellerProp['company'];
			$replacements['seller_address'] = $sellerProp['store_address'];
			$replacements['seller_phone']   = $sellerProp['mobile'];
			$replacements['seller_website'] = $sellerProp['website'];
			$replacements['seller_email']   = $sellerProp['email'];
		}
		else
		{
			$shopName = $this->helper->config->get('shop_name');

			$replacements['seller_name']    = $shopName ?: implode('<br>', array_unique($sellers));
			$replacements['seller_address'] = $this->helper->config->get('shop_address');
			$replacements['seller_phone']   = $this->helper->config->get('shop_phone1');
			$replacements['seller_website'] = $this->helper->config->get('shop_website');
			$replacements['seller_email']   = $this->helper->config->get('shop_email');
		}

		// Check if we have any product rows to process.
		$pattern = '@(%GRID_BEGIN%).*?<tbody>(.*?)</tbody>.*?(%GRID_END%)@s';
		$found   = preg_match($pattern, $body, $match);

		$taxTitlePattern     = '@(%GRID_BEGIN%).*?(<th>%PRODUCT_TAXES_TITLES%</th>).*?(%GRID_END%)@s';
		$foundTitle          = preg_match($taxTitlePattern, $body, $matchTaxTitles);
		$taxTotalsPattern    = '@(%GRID_BEGIN%).*?(<td>%CART_TAXES_TOTALS%</td>).*?(%GRID_END%)@s';
		$foundTotals         = preg_match($taxTotalsPattern, $body, $matchTaxTotals);
		$taxBreakPattern     = '@(%GRID_BEGIN%).*?(<tr><td>%CART_TAXES_BREAKDOWN%</td></tr>).*?(%GRID_END%)@s';
		$foundBreakdown      = preg_match($taxBreakPattern, $body, $matchTaxBreakdown);
		$shipTaxPattern      = '@(%GRID_BEGIN%).*?(<tr><td>%SHIPPING_TAXES_BREAKDOWN%</td></tr>).*?(%GRID_END%)@s';
		$foundShipTax        = preg_match($shipTaxPattern, $body, $matchShipTax);
		$shipDiscountPattern = '@(%GRID_BEGIN%).*?(<tr><td>%SHIPPING_DISCOUNTS_BREAKDOWN%</td></tr>).*?(%GRID_END%)@s';
		$foundDiscountTax    = preg_match($shipDiscountPattern, $body, $matchShipDiscount);
		$allShopRules        = array();
		$taxTitles           = array();
		$taxRows             = array();
		$ruleTotals          = array();
		$taxTotals           = array();
		$taxBreakdowns       = array();

		foreach ($items as $item)
		{
			foreach ($item->shoprules as $ri => $rule)
			{
				if (abs($rule->change) >= 0.01 && $rule->type == 'tax')
				{
					if ($rule->parent_id)
					{
						$parent      = $helper->shopRule->getItem($rule->parent_id);
						$rule->title = $parent->title;
					}

					$allShopRules[$rule->id] = $rule;
				}
			}
		}

		if ($found)
		{
			$products = $this->getItems($order);

			if ($foundTitle)
			{
				foreach ($items as $item)
				{
					if (count($allShopRules))
					{
						foreach ($allShopRules as $ri => $rule)
						{
							$shoprule = array_values(array_filter($item->shoprules, function ($srule) use ($ri) {
								return (int) $srule->id == (int) $ri;
							}));

							if (!empty($shoprule) && isset($shoprule[0]) && abs($shoprule[0]->change) >= 0.01 && $shoprule[0]->type == 'tax')
							{
								$ruleTotals[$shoprule[0]->id] = (isset($ruleTotals[$shoprule[0]->id]) ? $ruleTotals[$shoprule[0]->id] : 0) + (abs($shoprule[0]->change) * $item->quantity);
								$iChange                      = $helper->currency->display(abs($shoprule[0]->change) * $item->quantity, $g_currency, '', true);
								$taxRows[$item->product_id][] = '<td class="text-center nowrap v-top">' . $iChange . '</td>';
							}
							else
							{
								$taxRows[$item->product_id][] = '<td class="text-center nowrap v-top">' . $helper->currency->display(0, $g_currency, '', true) . '</td>';
							}
						}
					}
				}

				foreach ($allShopRules as $ri => $rule)
				{
					$taxTitles[] = '<th class="text-center">' . sprintf('%s Amount', $rule->title) . '</th>';
				}

				$body = str_ireplace($matchTaxTitles[2], implode("\n", $taxTitles), $body);
			}

			if ($foundTotals)
			{
				foreach ($ruleTotals as $ruleTotal)
				{
					$taxTotals[] = '<td class="text-center">' . $helper->currency->display($ruleTotal, $g_currency, $c_currency, true) . '</td>';
				}

				$body = str_ireplace($matchTaxTotals[2], implode("\n", $taxTotals), $body);
			}

			if ($foundBreakdown)
			{
				foreach ($ruleTotals as $ruleId => $ruleTotal)
				{
					$taxBreakdowns[] = '<tr><td class="text-right cart_taxes_breakdown" colspan="5">' . sprintf('%s Amount : ', $allShopRules[$ruleId]->title) . '</td><td class="text-right" colspan="2">' . $helper->currency->display($ruleTotal, $g_currency, $c_currency, true) . '</td></tr>';
				}

				$body = str_ireplace($matchTaxBreakdown[2], implode("\n", $taxBreakdowns), $body);
			}

			if ($foundShipTax)
			{
				$allShippingShopRules = array();
				$shipShopRuleTotals   = array();
				$shipTaxBreakdowns    = array();

				foreach ($order->get('shipping_shoprules') as $ri => $rule)
				{
					if (abs($rule->change) >= 0.01 && $rule->type == 'tax')
					{
						if ($rule->parent_id > 1)
						{
							$parent      = $helper->shopRule->getItem($rule->parent_id);
							$rule->title = $parent->title;
						}

						$allShippingShopRules[$rule->id] = $rule;
						$shipShopRuleTotals[$rule->id]   = (isset($ruleTotals[$rule->id]) ? $ruleTotals[$rule->id] : 0) + abs($rule->change);
					}
				}

				foreach ($shipShopRuleTotals as $ruleId => $ruleTotal)
				{
					$shipTaxBreakdowns[] = '<tr><td class="text-right shipping_taxes_breakdown" colspan="5">' . sprintf('%s Amount on Shipping (@ %s): ', $allShippingShopRules[$ruleId]->title, $allShippingShopRules[$ruleId]->amount . ($allShippingShopRules[$ruleId]->percent ? '%' : '')) . '</td><td class="text-right" colspan="2">' . $helper->currency->display($ruleTotal, $g_currency, $c_currency, true) . '</td></tr>';
				}

				$body = str_ireplace($matchShipTax[2], implode("\n", $shipTaxBreakdowns), $body);
			}

			if ($foundDiscountTax)
			{
				$allShippingDiscRules = array();
				$shipDiscRuleTotals   = array();
				$shipDiscBreakdowns   = array();

				foreach ($order->get('shipping_shoprules') as $ri => $rule)
				{
					if (abs($rule->change) >= 0.01 && $rule->type == 'discount')
					{
						if ($rule->parent_id > 1)
						{
							$parent      = $helper->shopRule->getItem($rule->parent_id);
							$rule->title = $parent->title;
						}

						$allShippingDiscRules[$rule->id] = $rule;
						$shipDiscRuleTotals[$rule->id]   = (isset($ruleTotals[$rule->id]) ? $ruleTotals[$rule->id] : 0) + abs($rule->change);
					}
				}

				foreach ($shipDiscRuleTotals as $ruleId => $ruleTotal)
				{
					$shipDiscBreakdowns[] = '<tr><td class="text-right shipping_discounts_breakdown" colspan="5">' . sprintf('%s Amount on Shipping (@ %s): ', $allShippingDiscRules[$ruleId]->title, $allShippingDiscRules[$ruleId]->amount . ($allShippingDiscRules[$ruleId]->percent ? '%' : '')) . '</td><td class="text-right" colspan="2">' . $helper->currency->display($ruleTotal, $g_currency, $c_currency, true) . '</td></tr>';
				}

				$body = str_ireplace($matchShipDiscount[2], implode("\n", $shipDiscBreakdowns), $body);
			}
		}

		$replacements = array_change_key_case($replacements, CASE_UPPER);

		foreach ($replacements as $code => $replacement)
		{
			$body = str_ireplace('%' . $code . '%', $replacement, $body);
		}

		while ($found)
		{
			reset($products);
			$rows = array();

			$productRow    = $match[2];
			$taxRowPattern = '/(<td>%PRODUCT_TAXES_ROWS%<\/td>)/s';
			$foundTax      = preg_match($taxRowPattern, $productRow, $matchTaxRow);

			// In each set the groups are => 0:%GRID_BEGIN%, 1:DESIRED_ROW, 2:%GRID_END%
			foreach ($products as $product)
			{
				$row = trim($match[2]);

				foreach ($product as $code => $replacement)
				{
					$row = str_ireplace('%' . $code . '%', $replacement, $row);
				}

				if ($foundTax)
				{
					$row = str_ireplace($matchTaxRow[0], implode("\n", $taxRows[$product['PRODUCT_ID']]), $row);
				}

				$rows[] = $row;

				if (!empty($product['PRODUCT_SHOPRULES']) && ($context == 'com_sellacious.order.print' || $context == 'com_sellacious.order.pdf'))
				{
					$rows[] = $product['PRODUCT_SHOPRULES'];
				}
			}

			$output = str_ireplace(array($match[1], $productRow, $match[3]), array('', implode("\n", $rows), ''), $match[0]);
			$body   = str_ireplace($match[0], $output, $body);

			// Find next match after processing previous one.
			$found = preg_match($pattern, $body, $match);
		}
	}

	/**
	 * Get all data from the given order for template replacements.
	 *
	 * @param   Registry  $order
	 *
	 * @return  array
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function getItems($order)
	{
		$orderId = $order->get('id');

		$products   = array();
		$helper     = SellaciousHelper::getInstance();
		$dispatcher = $helper->core->loadPlugins();
		$items      = $order->get('items');
		$currency   = $order->get('currency');
		$c_currency = $helper->currency->current('code_3');
		$base       = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		foreach ($items as $item)
		{
			$images = $helper->product->getImages($item->product_id, $item->variant_id);

			foreach ($images as &$image)
			{
				if (!@getimagesize($base . $image))
				{
					$image = $this->helper->media->getBlankImage(true);
				}
			}

			$seller     = new Sellacious\Seller($item->seller_uid);
			$sellerProp = $seller->getAttributes();

			$shipping = $helper->currency->display($item->shipping_amount, $currency, $c_currency, true);

			$log = $helper->order->getStatusLog($orderId, $item->item_uid);

			$status_old = 'NA';
			$status_new = 'NA';

			if (count($log) == 1)
			{
				$status_new = $log[0]->s_title;
			}
			elseif (count($log) >= 2)
			{
				$status_old = $log[1]->s_title;
				$status_new = $log[0]->s_title;
			}

			$record = $log[0];

			if ($record->created_by == $order->get('customer_uid'))
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
				$status_creator      = $order->get('customer_name');
			}
			elseif ($record->created_by == $item->seller_uid)
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
				$status_creator      = $item->seller_company ?: $item->seller_name;
			}
			else
			{
				$user = JFactory::getUser($record->created_by);

				if ($user->authorise('config.edit'))
				{
					$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
				}
				else
				{
					$status_creator_role = JText::sprintf('COM_SELLACIOUS_ORDER_USERTYPE_UNKNOWN', $user->get('name', 'N/A'));
				}

				$status_creator = $user->get('name', 'N/A');
			}

			$statuses      = $this->helper->order->getStatusLog($item->order_id, $item->item_uid);
			$totalTaxRates = array();	//Total Tax Percentages

			foreach ($item->shoprules as $ri => $rule)
			{
				if (abs($rule->change) >= 0.01 && $rule->type == 'tax' && isset($rule->rule_class) && $rule->percent)
				{
					$totalTaxRates[$rule->rule_class->alias] = (isset($totalTaxRates[$rule->rule_class->alias]) ? $totalTaxRates[$rule->rule_class->alias] : 0) + $rule->amount;
				}
			}

			$totalTaxRate = array_sum($totalTaxRates);

			$product = array(
				'product_id'                  => $item->product_id,
				'product_title'               => $item->product_title . ($item->variant_title ? ' - ' . $item->variant_title : ''),
				'product_sku'                 => $item->local_sku . ($item->variant_sku ? ' - ' . $item->variant_sku : ''),
				'product_quantity'            => $item->quantity,
				'product_seller'              => $item->seller_company ?: $item->seller_name,
				'product_seller_name'         => $item->seller_name ?: null,
				'product_seller_username'     => $sellerProp['username'] ?: null,
				'product_seller_email'        => $item->seller_email ?: null,
				'product_seller_store'        => $sellerProp['store'] ?: null,
				'product_seller_company'      => $item->seller_company ?: null,
				'product_seller_code'         => $item->seller_code ?: null,
				'product_seller_contact'      => $sellerProp['mobile'] ?: null,
				'product_price'               => $helper->currency->display($item->basic_price, $currency, $c_currency, true),
				'product_tax'                 => $helper->currency->display($item->tax_amount, $currency, $c_currency, true),
				'product_discount'            => $helper->currency->display($item->discount_amount, $currency, $c_currency, true),
				'product_sales_price'         => $helper->currency->display($item->sales_price, $currency, $c_currency, true),
				'product_subtotal'            => $helper->currency->display($item->sub_total + $item->shipping_amount, $currency, $c_currency, true),
				'product_subtotal_ex_tax'     => $helper->currency->display($item->basic_price * $item->quantity, $currency, $c_currency, true),
				'product_total_tax_rate'      => sprintf('%s %%', $totalTaxRate),
				'product_shipping'            => $shipping,
				'product_shipping_rule'       => $item->shipping_rule ? JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $item->shipping_rule) : '',
				'product_image'               => $base . reset($images),
				'product_status_old'          => $status_old,
				'product_status_new'          => $status_new,
				'product_status_creator'      => $status_creator,
				'product_status_creator_role' => $status_creator_role,
				'product_status_created_date' => JHtml::_('date', $record->created, 'F d, Y h:i A T'),
				'product_status_log'          => $this->buildHtml($statuses, 'item_statuses'),
			);

			$dispatcher->trigger('onParseProductRow', array('com_sellacious.templates.product', $item, &$product));

			$products[] = array_change_key_case($product, CASE_UPPER);
		}

		return $products;
	}

	/**
	 * Build render-able layout from form field data array
	 *
	 * @param   array   $displayData
	 * @param   string  $layout
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	protected function buildHtml($displayData, $layout)
	{
		ob_start();
		include JPluginHelper::getLayoutPath($this->_type, $this->_name, $layout);

		return ob_get_clean();
	}
}
