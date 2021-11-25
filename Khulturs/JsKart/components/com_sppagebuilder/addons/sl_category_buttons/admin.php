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
		'addon_name' => 'sp_sl_category_buttons',
		'category'   => 'Sellacious',
		'title'      => JText::_('Category Buttons'),
		'desc'       => JText::_('Sellacious Category Buttons'),
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

				'separator_back_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('Back Button Options'),
				),
				'show_back_btn' => array(
					'type'    => 'select',
					'title'   => JText::_('Show Back Button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
					'depends' => array(),
				),
				'back_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('Back Button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'Back',
					'depends'=>array('show_back_btn'=>1)
				),
				'back_btn_fontstyle'=>array(
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
					'depends' => array(array('back_btn_text', '!=', '')),
				),
				'back_btn_letterspace'=>array(
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
					'depends' => array(array('back_btn_text', '!=', '')),
				),
				'back_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('back_btn_text', '!=', '')),
				),
				'back_btn_type'=>array(
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
					'depends' => array(array('back_btn_text', '!=', '')),
				),
				'back_btn_size'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DEFAULT'),
						'lg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_LARGE'),
						'xlg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_XLARGE'),
						'sm'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_SMALL'),
						'xs'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_EXTRA_SAMLL'),
					),
				),
				'back_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('back_btn_type'=>'custom'),
				),

				'back_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('back_btn_type'=>'custom'),
				),

				'back_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('back_btn_type'=>'custom'),
				),

				'back_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('back_btn_type'=>'custom'),
				),
				'back_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('back_btn_text', '!=', '')),
				),

				// View All Product btn
				'separator_viewallprod_btn'=>array(
					'type'=>'separator',
					'title'=>JText::_('View All Products Options'),
				),
				'show_viewallprod_btn'    => array(
					'type'    => 'select',
					'title'   => JText::_('Show View All Products button'),
					'values'  => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std'     => '1',
					'depends' => array(),
				),
				'viewallprod_btn_text' => array(
					'type'  => 'text',
					'title' => JText::_('View All Products button Text'),
					'desc'  => JText::_('Put here your desirable text'),
					'std'   => 'View All Products',
					'depends'=>array('show_viewallprod_btn'=>1)
				),
				'viewallprod_btn_fontstyle'=>array(
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
					'depends' => array(array('viewallprod_btn_text', '!=', '')),
				),
				'viewallprod_btn_letterspace'=>array(
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
					'depends' => array(array('viewallprod_btn_text', '!=', '')),
				),
				'viewallprod_btn_appearance'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
						'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
						'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
					),
					'std'=>'flat',
					'depends' => array(array('viewallprod_btn_text', '!=', '')),
				),
				'viewallprod_btn_type'=>array(
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
					'depends' => array(array('viewallprod_btn_text', '!=', '')),
				),
				'viewallprod_btn_size'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DEFAULT'),
						'lg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_LARGE'),
						'xlg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_XLARGE'),
						'sm'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_SMALL'),
						'xs'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_EXTRA_SAMLL'),
					),
				),
				'viewallprod_btn_background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
					'std' => '#444444',
					'depends'=>array('viewallprod_btn_type'=>'custom'),
				),

				'viewallprod_btn_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
					'std' => '#fff',
					'depends'=>array('viewallprod_btn_type'=>'custom'),
				),

				'viewallprod_btn_background_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
					'std' => '#222',
					'depends'=>array('viewallprod_btn_type'=>'custom'),
				),

				'viewallprod_btn_color_hover'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
					'std' => '#fff',
					'depends'=>array('viewallprod_btn_type'=>'custom'),
				),
				'viewallprod_btn_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
					'values'=>array(
						'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
						'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
						'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
					),
					'std' => 'round',
					'depends' => array(array('viewallprod_btn_text', '!=', '')),
				),


				// Seperator
				'separator_for_seperator'=>array(
					'type'=>'separator',
					'title'=>JText::_('Seperator Options'),
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
