<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$catId      = $this->state->get('filter.category_id');
$showBanner = $this->helper->config->get('show_category_banner', 0);

if ($showBanner && $catId > 1)
{
	$showCatBnr = $this->helper->category->getCategoryParam($catId, 'banners_on_product_listing');
	$banners    = $showCatBnr ? $this->helper->category->getBanners($catId, false) : null;

	if ($banners)
	{
		$catBannerH = (int) $this->helper->config->get('category_banner_height', 300);
		$style      = ".category-banner .cat-banner { min-height: <?php echo $catBannerH; ?>px; }";

		$this->document->addStyleDeclaration($style);
		?>
		<div class="category-banner">
		<span class="cat-banner bg-rollover" style="background-image: url('<?php echo reset($banners) ?>')"
			  data-rollover="<?php echo htmlspecialchars(json_encode($banners)) ?>"></span>
		</div>
		<?php
	}
}
