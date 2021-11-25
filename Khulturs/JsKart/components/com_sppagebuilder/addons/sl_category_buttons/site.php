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

class SppagebuilderAddonSL_Category_Buttons extends SppagebuilderAddons
{

	public function render()
	{

		$class              = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title              = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector   = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_back_btn      = (isset($this->addon->settings->show_back_btn) && $this->addon->settings->show_back_btn) ? $this->addon->settings->show_back_btn : '0';
		$back_btn_text      = (isset($this->addon->settings->back_btn_text) && $this->addon->settings->back_btn_text) ? $this->addon->settings->back_btn_text : '';
		$back_class 	    = (isset($this->addon->settings->back_btn_type) && $this->addon->settings->back_btn_type) ? ' sppb-btn-' . $this->addon->settings->back_btn_type : '';
		$back_class        .= (isset($this->addon->settings->back_btn_shape) && $this->addon->settings->back_btn_shape) ? ' sppb-btn-' . $this->addon->settings->back_btn_shape : ' sppb-btn-round';
		$back_class        .= (isset($this->addon->settings->back_btn_size) && $this->addon->settings->back_btn_size) ? ' sppb-btn-' . $this->addon->settings->back_btn_size : '';
		$back_class        .= (isset($this->addon->settings->back_btn_appearance) && $this->addon->settings->back_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->back_btn_appearance : '';
		$show_viewallprod_btn    = (isset($this->addon->settings->show_viewallprod_btn) && $this->addon->settings->show_viewallprod_btn) ? $this->addon->settings->show_viewallprod_btn : '0';
		$viewallprod_btn_text 	 = (isset($this->addon->settings->viewallprod_btn_text) && $this->addon->settings->viewallprod_btn_text) ? $this->addon->settings->viewallprod_btn_text : '';
		$viewallprod_class 		 = (isset($this->addon->settings->viewallprod_btn_type) && $this->addon->settings->viewallprod_btn_type) ? ' sppb-btn-' . $this->addon->settings->viewallprod_btn_type : '';
		$viewallprod_class      .= (isset($this->addon->settings->viewallprod_btn_shape) && $this->addon->settings->viewallprod_btn_shape) ? ' sppb-btn-' . $this->addon->settings->viewallprod_btn_shape : ' sppb-btn-round';
		$viewallprod_class      .= (isset($this->addon->settings->viewallprod_btn_size) && $this->addon->settings->viewallprod_btn_size) ? ' sppb-btn-' . $this->addon->settings->viewallprod_btn_size : '';
		$viewallprod_class      .= (isset($this->addon->settings->viewallprod_btn_appearance) && $this->addon->settings->viewallprod_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->viewallprod_btn_appearance : '';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';


		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$category = $jInput->getInt('category');
		$html    = '';

		$helper = SellaciousHelper::getInstance();
		$list = $helper->category->getItem($category);

		//Options
		if ($category)
		{
			ob_start();

			$suffix = $list->parent_id > 1 ? '&parent_id=' . $list->parent_id : '';
			$urlC   = JRoute::_('index.php?option=com_sellacious&view=categories' . $suffix);
			$urlP   = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $category);

			$seperatorposition = array($show_seperator_position);
			$seperatortop = in_array('top', $seperatorposition);
			$seperatorbottom = in_array('bottom', $seperatorposition);
			$seperatorboth = in_array('both', $seperatorposition);

			if($show_seperator && $seperatortop || $show_seperator && $seperatorboth){
				echo '<hr class="isolate">';
			}

			?>

			<div class="cat-infoarea">
				<div class="cat-btn-group">
					<?php if ($show_back_btn && ($back_btn_text != '')): ?>
						<a href="<?php echo $urlC ?>" id="btn-back" class="cat-btn <?php echo $back_class ?>">
							<i class="fa fa-chevron-left"></i>
							&nbsp; <?php echo $back_btn_text ?>
						</a>
					<?php endif; ?>
					<?php if ($show_viewallprod_btn && ($viewallprod_btn_text != '')): ?>
						<a href="<?php echo $urlP ?>" id="btn-view-all-products" class="cat-btn <?php echo $viewallprod_class ?>" id="view-all-products">
							<?php echo $viewallprod_btn_text ?> &nbsp;
							<i class="fa fa-chevron-right">
							</i>
						</a>
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

		$backoptions = new stdClass;
		$backoptions->button_type = (isset($this->addon->settings->back_btn_type) && $this->addon->settings->back_btn_type) ? $this->addon->settings->back_btn_type : '';
		$backoptions->button_appearance = (isset($this->addon->settings->back_btn_appearance) && $this->addon->settings->back_btn_appearance) ? $this->addon->settings->back_btn_appearance : '';
		$backoptions->button_color = (isset($this->addon->settings->back_btn_color) && $this->addon->settings->back_btn_color) ? $this->addon->settings->back_btn_color : '';
		$backoptions->button_color_hover = (isset($this->addon->settings->back_btn_color_hover) && $this->addon->settings->back_btn_color_hover) ? $this->addon->settings->back_btn_color_hover : '';
		$backoptions->button_background_color = (isset($this->addon->settings->back_btn_background_color) && $this->addon->settings->back_btn_background_color) ? $this->addon->settings->back_btn_background_color : '';
		$backoptions->button_background_color_hover = (isset($this->addon->settings->back_btn_background_color_hover) && $this->addon->settings->back_btn_background_color_hover) ? $this->addon->settings->back_btn_background_color_hover : '';
		$backoptions->button_fontstyle = (isset($this->addon->settings->back_btn_fontstyle) && $this->addon->settings->back_btn_fontstyle) ? $this->addon->settings->back_btn_fontstyle : '';
		$backoptions->button_letterspace = (isset($this->addon->settings->back_btn_letterspace) && $this->addon->settings->back_btn_letterspace) ? $this->addon->settings->back_btn_letterspace : '';

		$viewallprodoptions = new stdClass;
		$viewallprodoptions->button_type = (isset($this->addon->settings->viewallprodnow_btn_type) && $this->addon->settings->viewallprodnow_btn_type) ? $this->addon->settings->viewallprodnow_btn_type : '';
		$viewallprodoptions->button_appearance = (isset($this->addon->settings->viewallprodnow_btn_appearance) && $this->addon->settings->viewallprodnow_btn_appearance) ? $this->addon->settings->viewallprodnow_btn_appearance : '';
		$viewallprodoptions->button_color = (isset($this->addon->settings->viewallprodnow_btn_color) && $this->addon->settings->viewallprodnow_btn_color) ? $this->addon->settings->viewallprodnow_btn_color : '';
		$viewallprodoptions->button_color_hover = (isset($this->addon->settings->viewallprodnow_btn_color_hover) && $this->addon->settings->viewallprodnow_btn_color_hover) ? $this->addon->settings->viewallprodnow_btn_color_hover : '';
		$viewallprodoptions->button_background_color = (isset($this->addon->settings->viewallprodnow_btn_background_color) && $this->addon->settings->viewallprodnow_btn_background_color) ? $this->addon->settings->viewallprodnow_btn_background_color : '';
		$viewallprodoptions->button_background_color_hover = (isset($this->addon->settings->viewallprodnow_btn_background_color_hover) && $this->addon->settings->viewallprodnow_btn_background_color_hover) ? $this->addon->settings->viewallprodnow_btn_background_color_hover : '';
		$viewallprodoptions->button_fontstyle = (isset($this->addon->settings->viewallprodnow_btn_fontstyle) && $this->addon->settings->viewallprodnow_btn_fontstyle) ? $this->addon->settings->viewallprodnow_btn_fontstyle : '';
		$viewallprodoptions->button_letterspace = (isset($this->addon->settings->viewallprodnow_btn_letterspace) && $this->addon->settings->viewallprodnow_btn_letterspace) ? $this->addon->settings->viewallprodnow_btn_letterspace : '';
		$viewallprodoptions->button_size = (isset($this->addon->settings->viewallprodnow_btn_size) && $this->addon->settings->viewallprodnow_btn_size) ? $this->addon->settings->viewallprodnow_btn_size : '';

		$css = '';
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $backoptions, 'id' => 'btn-back'));
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $viewallprodoptions, 'id' => 'btn-view-all-products'));

		return $css;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');

	}
}
