<?php
/**
 * @version     2.1.4
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

SpAddonsConfig::addonConfig(
	array(
		'type'       => 'content',
		'category'   => 'Sellacious',
		'addon_name' => 'sp_sl_product_toolbar',
		'title'      => JText::_('Product Toolbar'),
		'desc'       => JText::_('Sellacious Product Toolbar'),
		'attr'       => array(
			'general' => array(

				'admin_label' => array(
					'type'  => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'  => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'   => ''
				),

				'title' => array(
					'type'  => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
					'desc'  => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
					'std'   => ''
				),

				'heading_selector' => array(
					'type'    => 'select',
					'title'   => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
					'desc'    => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
					'values'  => array(
						'h1' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
						'h2' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
						'h3' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
						'h4' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
						'h5' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
						'h6' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
					),
					'std'     => 'h3',
					'depends' => array(array('title', '!=', '')),
				),

				'title_fontsize' => array(
					'type'    => 'number',
					'title'   => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc'    => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std'     => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_lineheight' => array(
					'type'    => 'text',
					'title'   => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std'     => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_fontstyle' => array(
					'type'     => 'select',
					'title'    => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
					'values'   => array(
						'underline' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic'    => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter'   => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal'    => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold'      => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder'    => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple' => true,
					'std'      => '',
					'depends'  => array(array('title', '!=', '')),
				),

				'title_letterspace' => array(
					'type'    => 'select',
					'title'   => JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
					'values'  => array(
						'0'    => 'Default',
						'1px'  => '1px',
						'2px'  => '2px',
						'3px'  => '3px',
						'4px'  => '4px',
						'5px'  => '5px',
						'6px'  => '6px',
						'7px'  => '7px',
						'8px'  => '8px',
						'9px'  => '9px',
						'10px' => '10px'
					),
					'std'     => '0',
					'depends' => array(array('title', '!=', '')),
				),

				'title_fontweight' => array(
					'type'    => 'text',
					'title'   => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT'),
					'desc'    => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT_DESC'),
					'std'     => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_text_color' => array(
					'type'    => 'color',
					'title'   => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc'    => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
					'depends' => array(array('title', '!=', '')),
				),

				'title_margin_top' => array(
					'type'        => 'number',
					'title'       => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc'        => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder' => '10',
					'depends'     => array(array('title', '!=', '')),
				),

				'title_margin_bottom' => array(
					'type'        => 'number',
					'title'       => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc'        => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder' => '10',
					'depends'     => array(array('title', '!=', '')),
				),

				'class' => array(
					'type'  => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'  => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'   => ''
				),

			),

			'sellacious' => array(

				'separator_review_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Review Button Options'),
					'depends'=>array('show_review_btn'=>1)
				),
				'show_review_btn'   => array(
					'type'    => 'select',
					'title'   => JText::_('Show Review Button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
				),
				'review_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Review button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Write a Review',
					'depends'=>array('show_review_btn'=>1)
				),
				'review_btn_fontstyle'=>array(
					'type'=>'select',
					'title'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_FONT_STYLE'),
					'values'=>array(
						'underline'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple'=>true,
					'std'=>'',
					'depends' => array(array('review_btn_text', '!=', '')),
				),
				'review_btn_letterspace'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_LETTER_SPACING'),
					'values'=>array(
						'0'=> 'Default',
						'1px'=> '1px',
						'2px'=> '2px',
						'3px'=> '3px',
						'4px'=> '4px',
						'5px'=> '5px',
						'6px'=>	'6px',
						'7px'=>	'7px',
						'8px'=>	'8px',
						'9px'=>	'9px',
						'10px'=> '10px'
					),
					'std'=>'0',
					'depends' => array(array('review_btn_text', '!=', '')),
				),
				'review_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('review_btn_text', '!=', '')),
				),
				'review_btn_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE_DESC'),
					'values'=>array(
						'default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
						'primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
						'success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
						'info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
						'warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
						'danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
						'link'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
						'custom'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CUSTOM'),
					),
					'std'=>'default',
					'depends' => array(array('review_btn_text', '!=', '')),
				),
				'review_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('review_btn_type'=>'custom'),
				),

				'review_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('review_btn_type'=>'custom'),
				),

				'review_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('review_btn_type'=>'custom'),
				),

				'review_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('review_btn_type'=>'custom'),
				),
				'review_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('review_btn_text', '!=', '')),
				),
				'review_btn_border'=>array(
					'type'  =>  'checkbox',
					'title' =>  JText::_('Use Border on Review Button'),
					'std'   =>  0
				),

				'review_btn_border_width'=>array(
					'type'    =>  'number',
					'title'   =>  JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'desc'    =>  JText::_('Define a border width of button'),
					'std'     =>  1,
					'depends' =>  array('review_btn_border'=>1)
				),
				'review_btn_border_style'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE'),
					'values'=>array(
						'none'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_NONE'),
						'solid'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_SOLID'),
						'double'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOUBLE'),
						'dotted'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOTTED'),
						'dashed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DASHED'),
					),
					'std'=>'solid',
					'depends' =>  array('review_btn_border'=>1)
				),
				'review_btn_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'desc'  => JText::_('Define a border color of Product Image Preview'),
					'std'=>'#E5E5E5',
					'depends' =>  array('review_btn_border'=>1)
				),




				// Wishlist btn
				'separator_wishlist_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Wishlist Button Options'),
					'depends'=>array('show_wishlist_btn'=>1)
				),
				'show_wishlist_btn' => array(
					'type'    => 'select',
					'title'   => JText::_('Show WishList Button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
				),
				'wishlist_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Wishlist button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Add to Wishlist',
					'depends'=>array('show_wishlist_btn'=>1)
				),

				'wishlist_btn_fontstyle'=>array(
					'type'=>'select',
					'title'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_FONT_STYLE'),
					'values'=>array(
						'underline'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple'=>true,
					'std'=>'',
					'depends' => array(array('wishlist_btn_text', '!=', '')),
				),
				'wishlist_btn_letterspace'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_LETTER_SPACING'),
					'values'=>array(
						'0'=> 'Default',
						'1px'=> '1px',
						'2px'=> '2px',
						'3px'=> '3px',
						'4px'=> '4px',
						'5px'=> '5px',
						'6px'=>	'6px',
						'7px'=>	'7px',
						'8px'=>	'8px',
						'9px'=>	'9px',
						'10px'=> '10px'
					),
					'std'=>'0',
					'depends' => array(array('wishlist_btn_text', '!=', '')),
				),
				'wishlist_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('wishlist_btn_text', '!=', '')),
				),
				'wishlist_btn_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE_DESC'),
					'values'=>array(
						'default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
						'primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
						'success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
						'info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
						'warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
						'danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
						'link'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
						'custom'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CUSTOM'),
					),
					'std'=>'default',
					'depends' => array(array('wishlist_btn_text', '!=', '')),
				),
				'wishlist_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('wishlist_btn_type'=>'custom'),
				),

				'wishlist_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('wishlist_btn_type'=>'custom'),
				),

				'wishlist_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('wishlist_btn_type'=>'custom'),
				),

				'wishlist_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('wishlist_btn_type'=>'custom'),
				),
				'wishlist_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('wishlist_btn_text', '!=', '')),
				),
				'wishlist_btn_border'=>array(
					'type'  =>  'checkbox',
					'title' =>  JText::_('Use Border on Wishlist Button'),
					'std'   =>  0
				),

				'wishlist_btn_border_width'=>array(
					'type'    =>  'number',
					'title'   =>  JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'desc'    =>  JText::_('Define a border width of button'),
					'std'     =>  1,
					'depends' =>  array('wishlist_btn_border'=>1)
				),
				'wishlist_btn_border_style'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE'),
					'values'=>array(
						'none'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_NONE'),
						'solid'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_SOLID'),
						'double'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOUBLE'),
						'dotted'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOTTED'),
						'dashed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DASHED'),
					),
					'std'=>'solid',
					'depends' =>  array('wishlist_btn_border'=>1)
				),
				'wishlist_btn_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'desc'  => JText::_('Define a border color of Product Image Preview'),
					'std'=>'#E5E5E5',
					'depends' =>  array('wishlist_btn_border'=>1)
				),



				// Compare btn
				'separator_compare_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Compare Button Options'),
					'depends'=>array('show_compare_btn'=>1)
				),
				'show_compare_btn'  => array(
					'type'    => 'select',
					'title'   => JText::_('Show Compare Button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
				),
				'compare_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Compare button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Add to Compare',
					'depends'=>array('show_compare_btn'=>1)
				),

				'compare_btn_fontstyle'=>array(
					'type'=>'select',
					'title'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_FONT_STYLE'),
					'values'=>array(
						'underline'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple'=>true,
					'std'=>'',
					'depends' => array(array('compare_btn_text', '!=', '')),
				),
				'compare_btn_letterspace'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_LETTER_SPACING'),
					'values'=>array(
						'0'=> 'Default',
						'1px'=> '1px',
						'2px'=> '2px',
						'3px'=> '3px',
						'4px'=> '4px',
						'5px'=> '5px',
						'6px'=>	'6px',
						'7px'=>	'7px',
						'8px'=>	'8px',
						'9px'=>	'9px',
						'10px'=> '10px'
					),
					'std'=>'0',
					'depends' => array(array('compare_btn_text', '!=', '')),
				),
				'compare_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('compare_btn_text', '!=', '')),
				),
				'compare_btn_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE_DESC'),
					'values'=>array(
						'default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
						'primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
						'success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
						'info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
						'warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
						'danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
						'link'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
						'custom'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CUSTOM'),
					),
					'std'=>'default',
					'depends' => array(array('compare_btn_text', '!=', '')),
				),
				'compare_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('compare_btn_type'=>'custom'),
				),

				'compare_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('compare_btn_type'=>'custom'),
				),

				'compare_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('compare_btn_type'=>'custom'),
				),

				'compare_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('compare_btn_type'=>'custom'),
				),
				'compare_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('compare_btn_text', '!=', '')),
				),
				'compare_btn_border'=>array(
					'type'  =>  'checkbox',
					'title' =>  JText::_('Use Border on Compare Button'),
					'std'   =>  0
				),

				'compare_btn_border_width'=>array(
					'type'    =>  'number',
					'title'   =>  JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'desc'    =>  JText::_('Define a border width of button'),
					'std'     =>  1,
					'depends' =>  array('compare_btn_border'=>1)
				),
				'compare_btn_border_style'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE'),
					'values'=>array(
						'none'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_NONE'),
						'solid'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_SOLID'),
						'double'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOUBLE'),
						'dotted'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOTTED'),
						'dashed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DASHED'),
					),
					'std'=>'solid',
					'depends' =>  array('compare_btn_border'=>1)
				),
				'compare_btn_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'desc'  => JText::_('Define a border color of Product Image Preview'),
					'std'=>'#E5E5E5',
					'depends' =>  array('compare_btn_border'=>1)
				),


				// Seperator
				'separator_for_seperator'=>array(
					'type'=>'separator',
					'title'=>JText::_('Seperator Options'),
					'depends'=>array('show_seperator'=>1)
				),
				'show_seperator' => array(
					'type'    => 'select',
					'title'   => JText::_('Display Seperator'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
					'depends' => array(),
				),

				'show_seperator_position' => array(
					'type'    => 'select',
					'title'   => JText::_('Seperator Position'),
					'values'=>array(
						'top'=>JText::_('Top'),
						'bottom'=>JText::_('Bottom'),
						'both'=>JText::_('Both'),
					),
					'std'     => 'top',
					'depends'=>array('show_seperator'=>1)
				),

			),
		),
	)
);
