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

class SppagebuilderAddonSL_Product_Attachments extends SppagebuilderAddons
{

	public function render()
	{

		$class              = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title              = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector   = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h5';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';


		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$seller  = $jInput->getInt('s');
		$html    = '';

		$helper = SellaciousHelper::getInstance();
		$attachments = $helper->product->getAttachments($product,$variant,$seller);

		//Options
		if ($attachments)
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

			<div class="media-attachments">
				<ul class="media-attachment-row">
					<?php foreach ($attachments as $attachment): ?>
						<?php $downloadLink = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $attachment->id); ?>
						<li><a href="<?php echo $downloadLink ?>" class="attach-link-view"><?php echo $attachment->original_name ?></a></li>
					<?php endforeach; ?>
				</ul>
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

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');

	}
}
