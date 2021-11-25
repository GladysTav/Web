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
		'category'   => 'Sellacious',
		'addon_name' => 'sp_sl_product_images',
		'title'      => JText::_('Product Images'),
		'desc'       => JText::_('Sellacious Product Images'),
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

				'product_border' => array(
					'type'    => 'select',
					'title'   => JText::_('Enable Product Thumb Border'),
					'desc'  => JText::_('Choose whether to show product border or not'),
					'values'  => array(
						'false' => JText::_('Disabled'),
						'true' => JText::_('Enabled')
					),
					'std'     => 'true',
					'depends' => array(),
				),
				
				'product_border_width' => array(
					'type'    =>  'number',
					'title'   =>  JText::_('Thumb Border Width'),
					'desc'    =>  JText::_('Define a border width of Product Image thumb'),
					'std'     =>  8,
					'depends' => array('product_border'=>'true'),
				),
				
				'product_border_color' => array(
					'type'=>'color',
					'title'=>JText::_('Thumb Border Color'),
					'desc'  => JText::_('Define a border color of Product Image thumb'),
					'std'=>'rgba(0, 0, 0, 0.1)',
					'depends' => array('product_border'=>'true'),
				),

				//zoom
				
				'separator_options'=>array(
					'type'=>'separator',
					'title'=>JText::_('Product Zoom Options')
				),

				'product_zoom_enabled' => array(
					'type'    => 'select',
					'title'   => JText::_('Enable Product Zoom'),
					'desc'  => JText::_('Choose whether to show product zoom or not'),
					'values'  => array(
						'false' => JText::_('Disabled'),
						'true' => JText::_('Enabled')
					),
					'std'     => 'true',
					'depends' => array(),
				),

				'product_zoom_size'=>array(
					'type'    =>  'number',
					'title'   =>  JText::_('Lens Size'),
					'desc'    =>  JText::_('Define a zoom box size Product Image Preview'),
					'std'     =>  '200',
					'depends'=> array('product_zoom_enabled'=>'true'),
				),

				'product_zoom_border_width'=>array(
					'type'    =>  'number',
					'title'   =>  JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'desc'    =>  JText::_('Define a border width of Product Image Preview'),
					'std'     =>  8,
					'depends' => array('product_zoom_enabled'=>'true'),
				),

				'product_zoom_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'desc'  => JText::_('Define a border color of Product Image Preview'),
					'std'=>'rgba(0, 0, 0, 0.1)',
					'depends' => array('product_zoom_enabled'=>'true'),
				),


			),
		),
	)
);
