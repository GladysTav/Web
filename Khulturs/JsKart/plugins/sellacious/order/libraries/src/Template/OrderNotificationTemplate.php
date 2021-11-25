<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Template;

// no access
defined('_JEXEC') or die;

use Exception;
use JFactory;
use JHtml;
use Joomla\Event\AbstractEvent;
use Joomla\Registry\Registry;
use JPluginHelper;
use JRoute;
use JText;
use JUri;
use Sellacious\Media\Attachment;
use Sellacious\Seller;
use Sellacious\User\User;
use Sellacious\User\UserHelper;
use SellaciousHelper;
use stdClass;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
class OrderNotificationTemplate extends AbstractNotificationTemplate
{
	/**
	 * Sellacious Helper
	 *
	 * @var   SellaciousHelper
	 *
	 * @since  2.0.0
	 */
	protected $helper;

	/**
	 * Order object
	 *
	 * @var   object
	 *
	 * @since  2.0.0
	 */
	protected $order;

	/**
	 * Seller object
	 *
	 * @var   object
	 *
	 * @since  2.0.0
	 */
	protected $seller;

	/**
	 * Constructor
	 *
	 * @param   AbstractEvent  $event
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct(AbstractEvent $event)
	{
		$this->order  = $event->getArgument('order');
		$this->seller = $event->getArgument('seller');
		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Get a list of recipients of given type based on the event,
	 * For guest users we can only promise of having email and/or phone
	 *
	 * @param   string  $type  The recipient type
	 *
	 * @return  array  An array containing user id for registered users / an object [name, email, phone] for guests
	 *
	 * @since   2.0.0
	 */
	public function getRecipients($type)
	{
		if ($type == 'admin')
		{
			return UserHelper::getSuperUsers();
		}

		if ($type == 'seller')
		{
			if ($this->seller)
			{
				return array($this->seller->seller_uid);
			}

			$sellers    = $this->helper->order->getSellers($this->order->get('id'));
			$recipients = array();

			foreach ($sellers as $seller)
			{
				$recipients[] = $seller->seller_uid;
			}

			return $recipients;
		}

		if ($type == 'self')
		{
			if ($this->order->get('customer_uid'))
			{
				return array($this->order->get('customer_uid'));
			}

			// For guest users
			$recipient        = new stdClass();
			$recipient->email = $this->order->get('customer_email');
			$recipient->phone = $this->order->get('customer_phone');

			return array($recipient);
		}

		return array();
	}

	/**
	 * Add Invoice as attachment
	 *
	 * @param   string  $type  The recipient type so that we have the flexibility to
	 *                         support different attachment for different recipient type on same event
	 *
	 * @return  Attachment[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getAttachments($type = null)
	{
		$attachments = parent::getAttachments($type);

		if ($this->order)
		{
			$filename = 'Invoice_' . $this->order->get('order_number');
			$file     = $filename . '.pdf';
			$path     = 'images/com_sellacious/orders/invoices/' . $file;
			$absPath  = JPATH_ROOT . '/' . $path;
			$options  = array(
				'type'     => 'f',
				'filename' => $filename,
				'path'     => $absPath
			);

			$content = $this->helper->order->getInvoiceHtml($this->order);
			$this->helper->order->createInvoicePdf($this->order, $content, $options);

			if (file_exists($absPath))
			{
				$attachments[] = new Attachment($path, $file);
			}
		}

		return $attachments;
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	protected function loadObject()
	{
		$obj = null;

		try
		{
			$password = substr($this->order->get('cart_hash'), 0, 8);

			$shippingParams = $this->order->get('shipping_params');
			$checkoutForms  = $this->order->get('checkout_forms');

			$data = array(
				'order_url'         => JUri::root() . 'index.php?option=com_sellacious&view=order&id=' . $this->order->get('id') . '&secret=' . $password,
				'order_date'        => JHtml::_('date', $this->order->get('created'), 'F d, Y h:i A T'),
				'order_number'      => $this->order->get('order_number'),
				'customer_name'     => $this->order->get('customer_name'),
				'customer_email'    => $this->order->get('customer_email'),
				'billing_name'      => $this->order->get('bt_name'),
				'billing_address'   => $this->order->get('bt_address'),
				'billing_district'  => $this->order->get('bt_district'),
				'billing_landmark'  => $this->order->get('bt_landmark'),
				'billing_state'     => $this->order->get('bt_state'),
				'billing_zip'       => $this->order->get('bt_zip'),
				'billing_country'   => $this->order->get('bt_country'),
				'bt_company'        => $this->order->get('bt_company'),
				'bt_po_number'      => $this->order->get('bt_po_number'),
				'billing_mobile'    => $this->order->get('bt_mobile'),
				'bt_address_type'   => $this->order->get('bt_residential') ? 'Residential' : 'Office',
				'bt_residential'    => $this->order->get('bt_residential') ? 'Yes' : 'No',
				'shipping_name'     => $this->order->get('st_name'),
				'shipping_address'  => $this->order->get('st_address'),
				'shipping_district' => $this->order->get('st_district'),
				'shipping_landmark' => $this->order->get('st_landmark'),
				'shipping_state'    => $this->order->get('st_state'),
				'shipping_zip'      => $this->order->get('st_zip'),
				'shipping_country'  => $this->order->get('st_country'),
				'st_company'        => $this->order->get('st_company'),
				'st_po_number'      => $this->order->get('st_po_number'),
				'shipping_mobile'   => $this->order->get('st_mobile'),
				'st_address_type'   => $this->order->get('st_residential') ? 'Residential' : 'Office',
				'st_residential'    => $this->order->get('st_residential') ? 'Yes' : 'No',
				'cart_subtotal'     => $this->helper->currency->display($this->order->get('product_subtotal'), $this->order->get('currency'), null),
				'cart_total'        => $this->helper->currency->display($this->order->get('cart_total'), $this->order->get('currency'), null),
				'cart_taxes'        => $this->helper->currency->display($this->order->get('cart_taxes'), $this->order->get('currency'), null),
				'cart_discounts'    => $this->helper->currency->display($this->order->get('cart_discounts'), $this->order->get('currency'), null),
				'grand_total'       => $this->helper->currency->display($this->order->get('grand_total'), $this->order->get('currency'), null),
				'cart_shipping'     => $this->helper->currency->display($this->order->get('product_shipping'), $this->order->get('currency'), null),
				'coupon_title'      => $this->order->get('coupon.coupon_title'),
				'coupon_code'       => $this->order->get('coupon.code'),
				'coupon_value'      => $this->helper->currency->display($this->order->get('coupon.amount'), $this->order->get('currency'), null),
				'shipping_rule'     => $this->order->get('shipping_rule'),
				'shipping_service'  => $this->order->get('shipping_service'),
				'status_old'        => $this->order->get('status_old'),
				'status_new'        => $this->order->get('status_new'),
				'order_password'    => $password,
				'shipment_form'     => $this->buildHtml($shippingParams, 'shipping_params'),
				'checkout_form'     => $this->buildHtml($checkoutForms, 'checkout_form'),
				'payment_method'    => $this->order->get('payment.method_name', 'NA'),
				'payment_sandbox'   => $this->order->get('payment.test_mode') ? 'TEST MODE' : '',
				'payment_fee'       => $this->helper->currency->display($this->order->get('payment.fee_amount'), $this->order->get('payment.currency'), null),
				'payment_amount'    => $this->helper->currency->display($this->order->get('payment.amount_payable'), $this->order->get('payment.currency'), null),
				'payment_response'  => $this->order->get('payment.response_message'),
				'seller_company'    => $this->seller ? ($this->seller->seller_company ?: $this->seller->seller_name) : null,
			);

			$sellerUid = $this->seller ? $this->seller->seller_uid : null;

			$data['grid_begin'] = $this->getOrderItems($sellerUid);
			$data['grid_end']   = '';

			// Order amount total seller wise
			$seller_total = 0;

			foreach ($data['grid_begin'] as $item)
			{
				$seller_total += (float) $item['product_price'];
			}

			$data['seller_total'] = $seller_total;

			$obj = (object) $data;
		}
		catch (Exception $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING);
		}

		return $obj;
	}

	/**
	 * Method to order items array
	 *
	 * @param   int  $sellerUid  Seller user id associated to an order item
	 *
	 * @return  array
	 *
	 * @throws   Exception
	 *
	 * @since   2.0.0
	 */
	protected function getOrderItems($sellerUid = null)
	{
		$orderId  = $this->order->get('id');
		$filter   = $sellerUid ? array('a.seller_uid = ' . (int) $sellerUid) : array();
		$items    = $this->helper->order->getOrderItems($orderId, null, $filter);
		$currency = $this->order->get('currency');
		$base     = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		$products = array();

		foreach ($items as $item)
		{
			$images = $this->helper->product->getImages($item->product_id, $item->variant_id);

			$seller     = new Seller($item->seller_uid);
			$sellerProp = $seller->getAttributes();

			if ($item->shipping_amount > 0)
			{
				$shipping = $this->helper->currency->display($item->shipping_amount, $currency, null);
			}
			else
			{
				$shipping = JText::_('COM_SELLACIOUS_ORDER_SHIPPING_COST_FREE');
			}

			$status = $this->helper->order->getStatus($item->order_id, $item->item_uid);

			// If Item status is available
			if ($status->id)
			{
				$log = $this->helper->order->getStatusLog($orderId, $item->item_uid);
			}
			else
			{
				// If seller wise status is available
				$log = $this->helper->order->getStatusLog($orderId, null, $item->seller_uid);
			}

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

			if ($record->created_by == $this->order->get('customer_uid'))
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
				$status_creator      = $this->order->get('customer_name');
			}
			elseif ($record->created_by == $item->seller_uid)
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
				$status_creator      = $item->seller_company ?: $item->seller_name;
			}
			else
			{
				$user = User::getInstance($record->created_by);

				if ($user->authorise('app.admin'))
				{
					$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
				}
				elseif ($user->authorise('app.manage'))
				{
					$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_MANAGER');
				}
				else
				{
					$status_creator_role = JText::sprintf('COM_SELLACIOUS_ORDER_USERTYPE_UNKNOWN', $user->getUser()->get('name', 'N/A'));
				}

				$status_creator = $user->getUser()->get('name', 'N/A');
			}

			$product = array(
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
				'product_price'               => $this->helper->currency->display($item->basic_price, $currency, null),
				'product_tax'                 => $this->helper->currency->display($item->tax_amount, $currency, null),
				'product_discount'            => $this->helper->currency->display($item->discount_amount, $currency, null),
				'product_sales_price'         => $this->helper->currency->display($item->sales_price, $currency, null),
				'product_subtotal'            => $this->helper->currency->display($item->sub_total, $currency, null),
				'product_shipping'            => $shipping,
				'product_image'               => $base . reset($images),
				'product_url'                 => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $item->item_uid),
				'product_seller_uid'          => $item->seller_uid,
				'product_status_old'          => $status_old,
				'product_status_new'          => $status_new,
				'product_status_creator'      => $status_creator,
				'product_status_creator_role' => $status_creator_role,
				'product_status_created_date' => JHtml::_('date', $record->created, 'F d, Y h:i A T'),
			);

			$products[] = $product;
		}

		return $products;
	}

	/**
	 * Method to get the name of this template object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return 'order_initiated';
	}

	/**
	 * Set default variables and sample
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		parent::loadVariables();

		$this->addVariable(new TemplateVariable('order_url', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ORDER_URL'), ''));
		$this->addVariable(new TemplateVariable('order_date', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ORDER_DATE'), ''));
		$this->addVariable(new TemplateVariable('order_number', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ORDER_NUMBER'), ''));
		$this->addVariable(new TemplateVariable('customer_name', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CUSTOMER_NAME'), ''));
		$this->addVariable(new TemplateVariable('customer_email', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CUSTOMER_EMAIL'), ''));
		$this->addVariable(new TemplateVariable('billing_name', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_NAME'), ''));
		$this->addVariable(new TemplateVariable('billing_address', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_ADDRESS'), ''));
		$this->addVariable(new TemplateVariable('billing_district', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_DISTRICT'), ''));
		$this->addVariable(new TemplateVariable('billing_landmark', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_LANDMARK'), ''));
		$this->addVariable(new TemplateVariable('billing_state', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_STATE'), ''));
		$this->addVariable(new TemplateVariable('billing_zip', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_ZIP'), ''));
		$this->addVariable(new TemplateVariable('billing_country', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_COUNTRY'), ''));
		$this->addVariable(new TemplateVariable('bt_company', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BT_COMPANY'), ''));
		$this->addVariable(new TemplateVariable('bt_po_number', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BT_PO_NUMBER'), ''));
		$this->addVariable(new TemplateVariable('billing_mobile', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BILLING_MOBILE'), ''));
		$this->addVariable(new TemplateVariable('bt_address_type', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BT_ADDRESS_TYPE'), ''));
		$this->addVariable(new TemplateVariable('bt_residential', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_BT_RESIDENTIAL'), ''));
		$this->addVariable(new TemplateVariable('shipping_name', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_NAME'), ''));
		$this->addVariable(new TemplateVariable('shipping_address', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_ADDRESS'), ''));
		$this->addVariable(new TemplateVariable('shipping_district', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_DISTRICT'), ''));
		$this->addVariable(new TemplateVariable('shipping_landmark', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_LANDMARK'), ''));
		$this->addVariable(new TemplateVariable('shipping_state', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_STATE'), ''));
		$this->addVariable(new TemplateVariable('shipping_zip', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_ZIP'), ''));
		$this->addVariable(new TemplateVariable('shipping_country', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_COUNTRY'), ''));
		$this->addVariable(new TemplateVariable('st_company', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ST_COMPANY'), ''));
		$this->addVariable(new TemplateVariable('st_po_number', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ST_PO_NUMBER'), ''));
		$this->addVariable(new TemplateVariable('shipping_mobile', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_MOBILE'), ''));
		$this->addVariable(new TemplateVariable('st_address_type', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ST_ADDRESS_TYPE'), ''));
		$this->addVariable(new TemplateVariable('st_residential', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ST_RESIDENTIAL'), ''));
		$this->addVariable(new TemplateVariable('cart_subtotal', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CART_SUBTOTAL'), ''));
		$this->addVariable(new TemplateVariable('cart_total', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CART_TOTAL'), ''));
		$this->addVariable(new TemplateVariable('cart_taxes', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CART_TAXES'), ''));
		$this->addVariable(new TemplateVariable('cart_discounts', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CART_DISCOUNTS'), ''));
		$this->addVariable(new TemplateVariable('grand_total', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_GRAND_TOTAL'), ''));
		$this->addVariable(new TemplateVariable('cart_shipping', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CART_SHIPPING'), ''));
		$this->addVariable(new TemplateVariable('coupon_title', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_COUPON_TITLE'), ''));
		$this->addVariable(new TemplateVariable('coupon_code', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_COUPON_CODE'), ''));
		$this->addVariable(new TemplateVariable('coupon_value', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_COUPON_VALUE'), ''));
		$this->addVariable(new TemplateVariable('shipping_rule', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_RULE'), ''));
		$this->addVariable(new TemplateVariable('shipping_service', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPPING_SERVICE'), ''));
		$this->addVariable(new TemplateVariable('status_old', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_STATUS_OLD'), ''));
		$this->addVariable(new TemplateVariable('status_new', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_STATUS_NEW'), ''));
		$this->addVariable(new TemplateVariable('order_password', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_ORDER_PASSWORD'), ''));
		$this->addVariable(new TemplateVariable('shipment_form', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SHIPMENT_FORM'), ''));
		$this->addVariable(new TemplateVariable('checkout_form', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_CHECKOUT_FORM'), ''));
		$this->addVariable(new TemplateVariable('payment_method', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PAYMENT_METHOD'), ''));
		$this->addVariable(new TemplateVariable('payment_sandbox', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PAYMENT_SANDBOX'), ''));
		$this->addVariable(new TemplateVariable('payment_fee', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PAYMENT_FEE'), ''));
		$this->addVariable(new TemplateVariable('payment_amount', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PAYMENT_AMOUNT'), ''));
		$this->addVariable(new TemplateVariable('payment_response', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PAYMENT_RESPONSE'), ''));
		$this->addVariable(new TemplateVariable('seller_company', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SELLER_COMPANY'), ''));
		$this->addVariable(new TemplateVariable('seller_total', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_SELLER_TOTAL'), ''));
		$this->addVariable(new OrderItemsGridVariable('grid_begin', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_GRID_BEGIN'), ''));
		$this->addVariable(new TemplateVariable('product_title', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_TITLE'), ''));
		$this->addVariable(new TemplateVariable('product_sku', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SKU'), ''));
		$this->addVariable(new TemplateVariable('product_quantity', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_QUANTITY'), ''));
		$this->addVariable(new TemplateVariable('product_seller', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER'), ''));
		$this->addVariable(new TemplateVariable('product_seller_name', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_NAME'), ''));
		$this->addVariable(new TemplateVariable('product_seller_username', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_USERNAME'), ''));
		$this->addVariable(new TemplateVariable('product_seller_email', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_EMAIL'), ''));
		$this->addVariable(new TemplateVariable('product_seller_store', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_STORE'), ''));
		$this->addVariable(new TemplateVariable('product_seller_company', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_COMPANY'), ''));
		$this->addVariable(new TemplateVariable('product_seller_code', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_CODE'), ''));
		$this->addVariable(new TemplateVariable('product_seller_contact', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SELLER_CONTACT'), ''));
		$this->addVariable(new TemplateVariable('product_price', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_PRICE'), ''));
		$this->addVariable(new TemplateVariable('product_tax', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_TAX'), ''));
		$this->addVariable(new TemplateVariable('product_discount', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_DISCOUNT'), ''));
		$this->addVariable(new TemplateVariable('product_sales_price', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SALES_PRICE'), ''));
		$this->addVariable(new TemplateVariable('product_subtotal', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SUBTOTAL'), ''));
		$this->addVariable(new TemplateVariable('product_shipping', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_SHIPPING'), ''));
		$this->addVariable(new TemplateVariable('product_image', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_IMAGE'), ''));
		$this->addVariable(new TemplateVariable('product_url', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_URL'), ''));
		$this->addVariable(new TemplateVariable('product_status_old', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_STATUS_OLD'), ''));
		$this->addVariable(new TemplateVariable('product_status_new', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_STATUS_NEW'), ''));
		$this->addVariable(new TemplateVariable('product_status_creator', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_STATUS_CREATOR'), ''));
		$this->addVariable(new TemplateVariable('product_status_creator_role', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_STATUS_CREATOR_ROLE'), ''));
		$this->addVariable(new TemplateVariable('product_status_created_date', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_PRODUCT_STATUS_CREATED_DATE'), ''));
		$this->addVariable(new TemplateVariable('grid_end', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_ORDER_PRODUCTS_GRID_END'), ''));
	}

	/**
	 * Build render-able layout from form field data array
	 *
	 * @param   array   $displayData
	 * @param   string  $layout
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	protected function buildHtml($displayData, $layout)
	{
		ob_start();

		/**
		 * Variables available to the layout
		 *
		 * @var  $this
		 * @var  $layoutPath
		 * @var  $displayData
		 */
		include JPluginHelper::getLayoutPath('sellacious', 'order', $layout);

		return ob_get_clean();
	}
}
