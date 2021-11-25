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

class SppagebuilderAddonSL_Product_SampleFile_Button extends SppagebuilderAddons
{

	public function render()
	{

		$class              = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title              = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector   = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$samplefile_btn_text      = (isset($this->addon->settings->samplefile_btn_text) && $this->addon->settings->samplefile_btn_text) ? $this->addon->settings->samplefile_btn_text : '';
		$samplefile_class 	    = (isset($this->addon->settings->samplefile_btn_type) && $this->addon->settings->samplefile_btn_type) ? ' sppb-btn-' . $this->addon->settings->samplefile_btn_type : '';
		$samplefile_class        .= (isset($this->addon->settings->samplefile_btn_shape) && $this->addon->settings->samplefile_btn_shape) ? ' sppb-btn-' . $this->addon->settings->samplefile_btn_shape : ' sppb-btn-round';
		$samplefile_class        .= (isset($this->addon->settings->samplefile_btn_appearance) && $this->addon->settings->samplefile_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->samplefile_btn_appearance : '';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';


		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$seller  = $jInput->getInt('s');
		$html    = '';

		$helper = SellaciousHelper::getInstance();
		$samplemedia = $this->getSampleMedia($product,$variant,$seller,$helper);

		//Options
		if ($samplemedia)
		{
			ob_start();

			$seperatorposition = array($show_seperator_position);
			$seperatortop = in_array('top', $seperatorposition);
			$seperatorbottom = in_array('bottom', $seperatorposition);
			$seperatorboth = in_array('both', $seperatorposition);

			if($show_seperator && $seperatortop || $show_seperator && $seperatorboth){
				echo '<hr class="isolate">';
			}

			?>



			<div class="samplemedia-infoarea ctech-wrapper">
				<div class="samplemedia-button">
					<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
						<div class="esamplefile">
							<a href="<?php echo $samplemedia->path; ?>" download id="btn-samplefile" class="ctech-btn ctech-btn-small ctech-btn-primary<?php echo $samplefile_class ?>">
								<i class="fa fa-download"></i> <?php echo $samplefile_btn_text; ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="clearfix"></div>

			<?php
			if($show_seperator && $seperatorbottom || $show_seperator && $seperatorboth){ ?>
				<hr class="isolate">
			<?php }

			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-buttons ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function css() {

		$addon_id = '#sppb-addon-' .$this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';

		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$samplefileoptions = new stdClass;
		$samplefileoptions->button_type = (isset($this->addon->settings->samplefile_btn_type) && $this->addon->settings->samplefile_btn_type) ? $this->addon->settings->samplefile_btn_type : '';
		$samplefileoptions->button_appearance = (isset($this->addon->settings->samplefile_btn_appearance) && $this->addon->settings->samplefile_btn_appearance) ? $this->addon->settings->samplefile_btn_appearance : '';
		$samplefileoptions->button_color = (isset($this->addon->settings->samplefile_btn_color) && $this->addon->settings->samplefile_btn_color) ? $this->addon->settings->samplefile_btn_color : '';
		$samplefileoptions->button_color_hover = (isset($this->addon->settings->samplefile_btn_color_hover) && $this->addon->settings->samplefile_btn_color_hover) ? $this->addon->settings->samplefile_btn_color_hover : '';
		$samplefileoptions->button_background_color = (isset($this->addon->settings->samplefile_btn_background_color) && $this->addon->settings->samplefile_btn_background_color) ? $this->addon->settings->samplefile_btn_background_color : '';
		$samplefileoptions->button_background_color_hover = (isset($this->addon->settings->samplefile_btn_background_color_hover) && $this->addon->settings->samplefile_btn_background_color_hover) ? $this->addon->settings->samplefile_btn_background_color_hover : '';
		$samplefileoptions->button_fontstyle = (isset($this->addon->settings->samplefile_btn_fontstyle) && $this->addon->settings->samplefile_btn_fontstyle) ? $this->addon->settings->samplefile_btn_fontstyle : '';
		$samplefileoptions->button_letterspace = (isset($this->addon->settings->samplefile_btn_letterspace) && $this->addon->settings->samplefile_btn_letterspace) ? $this->addon->settings->samplefile_btn_letterspace : '';

		$css = '';
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $samplefileoptions, 'id' => 'btn-samplefile'));

		return $css;
	}

	public function getSampleMedia($product_id,$variant_id,$seller_uid,$helper)
	{

		$filter = array(
			'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
			'list.join'  => array(
				array('inner', '#__sellacious_eproduct_media AS epm ON epm.id = a.record_id'),
			),
			'list.where' => array(

				'epm.product_id = ' . $product_id,
				'epm.variant_id = ' . $variant_id,
				'epm.seller_uid = ' . $seller_uid,
			),
			'table_name' => 'eproduct_media',
			'context'    => 'sample',
			'state'      => 1,
		);

		$sampledata  = $helper->media->loadObject($filter);

		return $sampledata;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');

	}
}
