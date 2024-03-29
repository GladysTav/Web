<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Config\ConfigHelper;

/**
 * Sellacious initial configuration model.
 *
 * @since   1.5.0
 */
class SellaciousModelSetup extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @note    Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->app->getUserStateFromRequest('com_sellacious.setup.return', 'return', '', 'cmd');

		parent::populateState();
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @since   1.5.0
	 */
	public function getTable($type = 'Config', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function save($data)
	{
		unset($data['tags']);
		unset($data['premium_trial']);

		$defaults = new Registry($this->getItem());
		$registry = new Registry($data);

		$helper = SellaciousHelper::getInstance();

		$defaults->merge($registry, true);

		$cUid = JFactory::getUser()->id;

		foreach ($defaults as $name => $params)
		{
			$config = ConfigHelper::getInstance($name);

			if ($name == 'com_sellacious')
			{
				$config->set('default_seller', $cUid);
			}

			$config->bind($params);

			$config->store();
		}

		$query = $this->_db->getQuery(true);
		$query->select('user_id')->from('#__sellacious_sellers')->where('user_id = ' . (int) $cUid);

		$pUid = 0;

		try
		{
			$pUid = $this->_db->setQuery($query)->loadResult();
		}
		catch (RuntimeException $e)
		{
		}

		if (!$pUid)
		{
			$defSeller        = $helper->category->getDefault('seller');
			$obj              = new stdClass;
			$obj->user_id     = $cUid;
			$obj->state       = 1;
			// We might need to create a default seller on the fly later.
			$obj->category_id = is_object($defSeller) ? $defSeller->id : null;

			$this->_db->insertObject('#__sellacious_sellers', $obj, 'id');
		}

		if (!$data['com_sellacious']['multi_seller'])
		{
			$defaultSeller = $this->helper->config->get('default_seller', -1);

			if ($defaultSeller)
			{
				// Set all products in sample data to default seller
				$productSeller = $this->getTable('ProductSeller');

				$query = $this->_db->getQuery(true);
				$query->update($this->_db->qn($productSeller->getTableName()));
				$query->set('seller_uid = ' . $defaultSeller);

				$this->_db->setQuery($query);

				$this->_db->execute();

				// Delete duplicates
				$delete = 'DELETE b FROM ' . $this->_db->qn('#__sellacious_product_sellers', 'a')
						. ' INNER JOIN ' . $this->_db->qn('#__sellacious_product_sellers', 'b')
						. ' WHERE b.id > a.id'
						. ' AND a.product_id = b.product_id'
						. ' AND a.seller_uid = b.seller_uid';

				$this->_db->setQuery($delete);

				$this->_db->execute();

				// Set all product prices in sample to default seller
				$query = $this->_db->getQuery(true);
				$query->update($this->_db->qn('#__sellacious_product_prices'));
				$query->set('seller_uid = ' . (int) $defaultSeller);

				$this->_db->setQuery($query);

				$this->_db->execute();

				// Delete duplicates
				$delete = 'DELETE b FROM ' . $this->_db->qn('#__sellacious_product_prices', 'a')
					. ' INNER JOIN ' . $this->_db->qn('#__sellacious_product_prices', 'b')
					. ' WHERE b.id > a.id'
					. ' AND a.product_id = b.product_id'
					. ' AND a.seller_uid = b.seller_uid';

				$this->_db->setQuery($delete);

				$this->_db->execute();
			}
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.setup', $data, true));

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @note    This must be kept in sync with \InstallationModelDatabase::getDefaultConfig()
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  stdClass
	 *
	 * @since   1.5.0
	 */
	public function getItem($pk = null)
	{
		$data = array(
			'address_mobile_regex'              => '^\\+?[\\d]{8,12}$',
			'address_zip_regex'                 => '^[\\w\\d]{5,8}$',
			'disable_checkout'                  => '0',
			'allow_client_authorised_users'     => '0',
			'allow_credit_limit'                => '0',
			'allow_duplicate_location'          => '0',
			'allow_guest_questions'             => '1',
			'allow_guest_ratings'               => '1',
			'allow_non_buyer_ratings'           => '1',
			'allow_ratings_for'                 =>
				array(
					0 => 'product',
					1 => 'seller',
				),
			'allow_review_for'                  =>
				array(
					0 => 'product',
					1 => 'seller',
				),
			'allowed_listing_type'              =>
				array(
					0 => '1',
				),
			'allowed_product_package'           => '1',
			'allowed_product_type'              => 'both',
			'cart_shop_more_link'               => '1',
			'categories_limit'                  => '10',
			'category_banner_height'            => '300',
			'category_cols'                     => '4',
			'category_image_size_adjust'        => 'contain',
			'category_level_limit'              => '10',
			'category_menu'                     => array(),
			'category_menu_parent'              => 1,
			'category_no_child_redirect'        => '0',
			'category_page_product_limit'       => '5',
			'category_page_view_all_products'   => '1',
			'category_sef_prefix'               => '0',
			'checkout_type'                     => '1',
			'compare_limit'                     => '3',
			'contact_spam_protection'           => '1',
			'default_seller'                    => '',
			'development'                       => '',
			'enable_fe_messages'                => '1',
			'productmedia_extensions'           => 'xlsx,docx,zip,tar.gz,mp3,pdf,jpg,png,gif,webp,webm,avi,mpg,dat,doc,xls,csv',
			'extensions_group'                  =>
				array(
					'com_sellacious'                           => 'sellacious',
					'sellacious'                               => 'sellacious',
					'mod_smartymenu'                           => 'sellacious',
					'plg_system_sellaciousimporter'            => 'sellacious',
					'com_importer'                             => 'sellacious',
					'com_sellaciousreporting'                  => 'sellacious',
					'plg_system_sellaciousreportscart'         => 'sellacious',
					'com_sellaciousopc'                        => 'sellacious',
					'plg_sellaciouspayment_paypalstandard'     => 'sellacious',
					'plg_content_sellaciousbuttons'            => 'sellacious',
					'plg_content_sellaciousproductquery'       => 'sellacious',
					'plg_sellacious_fieldtypeforms'            => 'sellacious',
					'plg_sellacious_order'                     => 'sellacious',
					'plg_sellacious_user'                      => 'sellacious',
					'plg_sellacious_mailqueue'                 => 'sellacious',
					'plg_sellaciouspayment_cod'                => 'sellacious',
					'plg_sellaciouspayment_custom'             => 'sellacious',
					'plg_sellaciouspayment_ewallet'            => 'sellacious',
					'plg_sellaciousrules_amountfilter'         => 'sellacious',
					'plg_sellaciousrules_client'               => 'sellacious',
					'plg_sellaciousrules_geolocation'          => 'sellacious',
					'plg_sellaciousrules_product'              => 'sellacious',
					'plg_system_sellacious'                    => 'sellacious',
					'plg_system_sellaciouscache'               => 'sellacious',
					'plg_system_sellaciousmailer'              => 'sellacious',
					'plg_system_sellaciousutm'                 => 'sellacious',
					'plg_system_sellaciousforex'               => 'sellacious',
					'plg_system_sellaciousproductnotification' => 'sellacious',
					'plg_system_sellaciousrecent'              => 'sellacious',
					'plg_system_sellaciousgooglemarkup'        => 'sellacious',
					'plg_system_sellacioushyperlocal'          => 'sellacious',
					'plg_finder_sellaciousproduct'             => 'sellacious',
					'mod_usercurrency'                         => 'sellacious',
					'mod_sellacious_ewallet'                   => 'sellacious',
					'mod_sellacious_cart'                      => 'sellacious',
					'mod_sellacious_users'                     => 'sellacious',
					'mod_sellacious_filters'                   => 'sellacious',
					'mod_sellacious_finder'                    => 'sellacious',
					'mod_sellacious_latestproducts'            => 'sellacious',
					'mod_sellacious_relatedproducts'           => 'sellacious',
					'mod_sellacious_specialcatsproducts'       => 'sellacious',
					'mod_sellacious_stores'                    => 'sellacious',
					'mod_sellacious_sellerproducts'            => 'sellacious',
					'mod_sellacious_bestsellingproducts'       => 'sellacious',
					'mod_sellacious_recentlyviewedproducts'    => 'sellacious',
					'mod_sellacious_hyperlocal'                => 'sellacious',
					'mod_sellacious_finder_for_category'       => 'sellacious',
					'mod_sellacious_finder_for_store'          => 'sellacious',
				),
			'financial_year_start'              => '1',
			'flat_shipping'                     => '1',
			'forex_api'                         => 'Sellacious',
			'forex_update_interval'             =>
				array(
					'l' => '1',
					'p' => 'day',
				),
			'free_listing'                      => '1',
			'frontend_display_stock'            => '0',
			'frontend_sef'                      => array(),
			'geolocation_levels'                =>
				array(
					'company'     => '1',
					'address'     => '1',
					'po_box'      => '1',
					'landmark'    => '1',
					'country'     => '1',
					'state_loc'   => '1',
					'district'    => '1',
					'zip'         => '1',
					'mobile'      => '1',
					'residential' => '1',
				),
			'global_currency'                   => 'USD',
			'hide_out_of_stock'                 => '0',
			'hide_questions_captcha_guest'      => '0',
			'hide_questions_captcha_registered' => '0',
			'hide_zero_priced'                  => '0',
			'image_gallery_enable'              => '1',
			'image_navigation_enable'           => '1',
			'image_zoom_border_color'           => 'rgba(0, 0, 0, 0.1)',
			'image_zoom_border_width'           => '8',
			'image_zoom_easing_enable'          => '1',
			'image_zoom_enable'                 => '1',
			'image_zoom_lens_background_color'  => 'rgba(255, 255, 255, 0.4)',
			'image_zoom_lens_border_color'      => 'rgba(0, 0, 0, 1)',
			'image_zoom_lens_border_width'      => '1',
			'image_zoom_lens_size'              => '200',
			'image_zoom_lens_size_mobile'       => '180',
			'image_zoom_type'                   => 'lens',
			'image_zoom_type_mobile'            => 'lens',
			'image_zoom_window_height'          => '400',
			'image_zoom_window_width'           => '400',
			'ip_country'                        => '0',
			'ip_currency'                       => '1',
			'itemised_shipping'                 => '0',
			'list_style'                        => 'grid',
			'list_style_switcher'               => '1',
			'listing_currency'                  => '0',
			'listing_fee'                       => '0.00',
			'listing_fee_recurrence'            => '0',
			'login_to_see_price'                => '0',
			'main_menutype'                     => '*',
			'mfg_link'                          => 'products',
			'min_checkout_value'                => 0,
			'multi_seller'                      => '0',
			'multi_variant'                     => '1',
			'on_sale_commission'                => '0.00',
			'order_number_pad'                  => '4',
			'order_number_pattern'              => '{YY}{OID}',
			'order_number_shift'                => '0',
			'product_add_to_cart_display'       =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_buy_now_display'           =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_category_required'         => '0',
			'product_compare'                   => '1',
			'product_compare_display'           =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_detail_page'               => '1',
			'product_features_list'             =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_filter_position'           => 'left',
			'product_img_height'                => '350',
			'product_img_size_adjust'           => 'contain',
			'product_img_width'                 => '250',
			'product_price_display'             => '2',
			'product_price_display_pages'       =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_questions'                 => '1',
			'product_quick_detail_pages'        =>
				array(
					0 => 'categories',
					1 => 'products',
				),
			'product_rating'                    => '1',
			'product_rating_display'            =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'product_wishlist'                  => '1',
			'products_cols'                     => '4',
			'products_image_height'             => '220',
			'products_image_size_adjust'        => 'contain',
			'profile_fieldset_order'            =>
				array(
					0 => 'basic',
					1 => 'client',
					2 => 'seller',
					3 => 'address',
					4 => 'custom',
				),
			'purchase_exchange'                 => '1',
			'purchase_return'                   => '1',
			'query_form_recipient'              => '2',
			'require_activation_cart_aio'       => '0',
			'seller_can_know_client_category'   => '0',
			'seller_product_approve'            => '0',
			'seller_tnc'                        => '0',
			'send_mail_product_creation'        => '0',
			'use_shoprule_import'               => '0',
			'shipment_itemised'                 => '0',
			'shippable_location_by_seller'      => '1',
			'shipped_by'                        => 'shop',
			'shipping_calculation_batch'        => 'cart',
			'shipping_country'                  => '216',
			'shipping_district'                 => '1404',
			'shipping_flat_fee'                 => '0.00',
			'shipping_state'                    => '1291',
			'shop_country'                      => '216',
			'shop_email'                        => '',
			'shop_name'                         => '',
			'shop_name_limit'                   => '10',
			'show_advertisement'                => '1',
			'show_allowed_listing_type'         => '1',
			'show_back_to_joomla'               => '1',
			'show_brand_footer'                 => '1',
			'show_category_banner'              => '1',
			'show_category_child_count'         => '1',
			'show_category_description'         => '1',
			'show_category_product_count'       => '1',
			'show_category_products'            => '1',
			'show_doc_link'                     => '1',
			'show_license_to'                   => '1',
			'show_order_download_link'          => '1',
			'show_orders_in_catalogue'          => '1',
			'show_rate_us'                      => '1',
			'show_ratings_in_catalogue'         => '1',
			'show_reviewer_badge'               => '1',
			'show_sellacious_version'           => '1',
			'show_seller_rating'                => '1',
			'show_shipping_info_on_detail'      => '1',
			'show_stock_in_catalogue'           => '1',
			'product_fields'                    => '{"product_type":{"row_checked":1},"physical":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"electronic":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"package":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"product_category":{"row_checked":1},"product_sku":{"row_checked":1},"primary_image":{"row_checked":1},"primary_video_url":{"row_checked":1},"other_images":{"row_checked":1},"short_description":{"row_checked":1},"description":{"row_checked":1},"product_attachments":{"row_checked":1},"location":{"row_checked":1}}',
			'show_store_product_count'          => '1',
			'show_store_rating'                 => '1',
			'show_support_link'                 => '1',
			'special_categories_limit'          => '10',
			'special_offer_limit'               => '10',
			'splcategory_badge_display'         =>
				array(
					0 => 'categories',
					1 => 'products',
					2 => 'product',
					3 => 'product_modal',
				),
			'stock_default'                     => '1',
			'stock_management'                  => 'product',
			'stock_over_default'                => '0',
			'tax_on_shipping'                   => '0',
			'use_shippingrule_import'           => '0',
			'user_currency'                     => '0',
			'zero_price_checkout'               => '1',
		);
		$params = new Registry($data);

		if (!$this->helper->access->isSubscribed())
		{
			$params->set('show_brand_footer', 1);
			$params->set('show_rate_us', 1);
			$params->set('show_doc_link', 1);
			$params->set('show_support_link', 1);
			$params->set('show_advertisement', 1);
			$params->set('show_back_to_joomla', 1);
			$params->set('show_sellacious_version', 1);
			$params->set('show_license_to', 1);
			$params->set('enable_fe_messages', 0);
		}

		$data = (object) array('com_sellacious' => $params->toArray());

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm  $form  A JForm object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 *
	 * @see     JFormField
	 *
	 * @since   12.2
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		if ($this->helper->core->getLicense('free_forever'))
		{
			$form->removeField('premium_trial');
		}

		parent::preprocessForm($form, $data, $group);
	}
}
