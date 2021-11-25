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

class SppagebuilderAddonSL_Category_Subcategories extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$showpcount       = (isset($this->addon->settings->showpcount) && $this->addon->settings->showpcount) ? $this->addon->settings->showpcount : '0';
		$showccount       = (isset($this->addon->settings->showccount) && $this->addon->settings->showccount) ? $this->addon->settings->showccount : '0';

		$app      = JFactory::getApplication();
		$input   = $app->input;
		$category = $input->getInt('category');
		$html     = '';
		$children = array();
		$helper = SellaciousHelper::getInstance();

		if ($category)
		{
			$catItem = $helper->category->getItem($category);
			if ($catItem->level > 0)
			{
				$childLevel = $catItem->level +1;
				$children = $helper->category->getChildren($catItem->id, false, array('a.state = 1', 'a.level = '.$childLevel));
 				sort($children);
			}

			if ($children)
			{
				$html     .='<div class="catogry-childblocks">';
				foreach ($children as $child)
				{
					$item = $helper->category->getItem($child);
					$cAll = $helper->category->getChildren($item->id, false, array('a.state = 1'));
					$item->subcat_count  = count($cAll);
					$item->product_count = $helper->category->countItems($item->id, false);

					$paths = $helper->media->getImages('categories', $item->id, true);
					$url   = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item->id);

					ob_start();
					?>
					<div class="sl-catbox category-box" data-rollover="container">
						<a href="<?php echo $url ?>">
							<h6><?php echo $item->title; ?></h6>
							<div class="image-wrap">
								<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
									  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
							</div>
						</a>
						<?php if (($showpcount) || ($showccount)): ?>
							<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
								<div class="item-counts-strip">
									<?php if ($showpcount): ?>
										<div class="tip-left"><?php
											if (isset($item->product_count))
											{
												echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $item->product_count);
											}
											?>
										</div>
									<?php endif; ?>
									<?php if ($showccount): ?>
										<div class="tip-right"><?php
											if (isset($item->subcat_count))
											{
												echo JText::plural('COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count);
											}
											?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<?php
					$html       .= ob_get_clean();

				}
				$html  .=	'</div>';
				$html  .=	'<div class="clearfix"></div>';
			}

		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-subcategories ' . $class . '">';
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
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categorystyle.css');

	}

}
