<?php
/**
 * @version     2.2.0
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
		'addon_name' => 'sp_sl_product_cartbuttons',
		'category'   => 'Sellacious',
		'title'      => JText::_('Product Cart Buttons'),
		'desc'       => JText::_('Sellacious Product Cart Buttons'),
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

				'separator_addtocart_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Add to Cart Button Options'),
					'depends'=>array('show_addtocart_btn'=>1)
				),
				'show_addtocart_btn' => array(
					'type'    => 'select',
					'title'   => JText::_('Show Add to Cart Button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
					'depends' => array(),
				),
				'addtocart_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Add to Cart button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Add to Cart',
					'depends'=>array('show_addtocart_btn'=>1)
				),
				'addtocart_btn_fontstyle'=>array(
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
					'depends' => array(array('addtocart_btn_text', '!=', '')),
				),
				'addtocart_btn_letterspace'=>array(
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
					'depends' => array(array('addtocart_btn_text', '!=', '')),
				),
				'addtocart_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('addtocart_btn_text', '!=', '')),
				),
				'addtocart_btn_type'=>array(
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
					'depends' => array(array('addtocart_btn_text', '!=', '')),
				),
				'addtocart_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('addtocart_btn_type'=>'custom'),
				),

				'addtocart_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('addtocart_btn_type'=>'custom'),
				),

				'addtocart_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('addtocart_btn_type'=>'custom'),
				),

				'addtocart_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('addtocart_btn_type'=>'custom'),
				),
				'addtocart_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('addtocart_btn_text', '!=', '')),
				),

				// buy now btn
				'separator_buynow_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Buy Now Button Options'),
					'depends'=>array('show_buynow_btn'=>1)
				),
				'show_buynow_btn'    => array(
					'type'    => 'select',
					'title'   => JText::_('Show Buy Now button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
					'depends' => array(),
				),
				'buynow_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Buy Now button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Buy Now',
					'depends'=>array('show_buynow_btn'=>1)
				),
				'buynow_btn_fontstyle'=>array(
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
					'depends' => array(array('buynow_btn_text', '!=', '')),
				),
				'buynow_btn_letterspace'=>array(
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
					'depends' => array(array('buynow_btn_text', '!=', '')),
				),
				'buynow_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('buynow_btn_text', '!=', '')),
				),
				'buynow_btn_type'=>array(
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
					'depends' => array(array('buynow_btn_text', '!=', '')),
				),
				'buynow_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('buynow_btn_type'=>'custom'),
				),

				'buynow_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('buynow_btn_type'=>'custom'),
				),

				'buynow_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('buynow_btn_type'=>'custom'),
				),

				'buynow_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('buynow_btn_type'=>'custom'),
				),
				'buynow_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('buynow_btn_text', '!=', '')),
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
