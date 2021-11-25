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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Price\PriceHelper;

/** @var  SellaciousViewCompare $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/fe.view.compare.js', true, true);
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.compare.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);

$me                 = JFactory::getUser();
$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);
$current_url        = JUri::getInstance()->toString();
$login_url          = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

$items      = $this->items;
$c_currency = $this->helper->currency->current('code_3');
$limit      = (int) $this->helper->config->get('compare_limit', 3);
?>
<h2 class="compare-page-title"><i class="fa fa-balance-scale ctech-bg-primary"></i> <?php
	echo JText::_("COM_SELLACIOUS_PRODUCT_COMPARE_LABEL");?> </h2>

<form  class="compare-form" action="<?php echo 'index.php' ?>">
	<div class="table-responsive">
		<table class="table tbl-specifications">
			<thead>
			<tr>
				<th colspan="1">&nbsp;</th>
				<?php
				$item_present   = count($items);
				$item_notpresnt = $limit - $item_present;
				$i              = 1;

				foreach ($this->items as $product)
				{
					$p_code = $this->helper->product->getCode($product->id, $product->variant_id, $product->seller_uid);
					$url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $p_code);
					?>
					<th colspan="1" class="title v-top">
						<label class="remove-compare">
							<input type="checkbox" name="cid[]" value="<?php echo $product->code ?>" class="hidden"/>&times;
						</label>
						<div class="title-compare">
							<?php
							$format = $product->variant_title ? '<a href="%s">%s - %s</a>' : '<a href="%s">%s</a>';

							echo sprintf($format, $url, $product->title, $product->variant_title); ?></div>
						<?php
						if ($i >= $limit)
						{
							break;
						}

						$i++;
						?>
					</th>
					<?php
				}
				?>

				<?php for ($i = 0; $i < $item_notpresnt; $i ++): ?>
					<th colspan="1" class="title v-top">
						<div class="title-compare">
							<a href="index.php"><?php echo JText::_('COM_SELLACIOUS_COMPARE_PRODUCT_NOT_PRESENT'); ?> </a>
						</div>
					</th>
				<?php endfor; ?>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<?php foreach ($this->items as $product): ?>
					<td style="width:25%;" class="center">
						<img class="product-image" src="<?php echo reset($product->images); ?>" alt=""/>
					</td>
				<?php endforeach; ?>

				<?php for ($i = 0; $i < $item_notpresnt; $i++): ?>
					<td style="width:25%;" class="center">
						<div class="no-product-image">
							<div class="far fa-file-image"></div>
						</div>
					</td>
				<?php endfor; ?>
			</tr>
			<tr>
				<th class="com-lbl"><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_PRICE') ?></th>
				<?php foreach ($this->items as $product): ?>
					<th>
						<?php
						$handler  = PriceHelper::getPsxHandler($product->product_id, $product->seller_uid);
						$registry = new Registry($product);

						echo $handler->renderLayout('price.minimal', $registry);

						echo $handler->renderLayout('checkout-buttons.small', $registry);
						?>
					</th>
				<?php endforeach; ?>
				<?php
				for ($i = 0; $i < $item_notpresnt; $i++)
				{
					?><th></th><?php
				}
				?>
			</tr>
			<?php $rating_display = $this->helper->config->inList('product', 'product_rating_display'); ?>

			<?php if ($this->helper->config->get('product_rating') && $rating_display): ?>
				<tr>
					<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_RATING') ?></b></td>
					<?php
					foreach ($this->items as $product)
					{
						$rating = $this->helper->rating->getProductRating($product->id);
						?>
						<td class="center">
							<div class="product-rating rating-stars">

							<?php if ((round($rating->rating, 2) >= 0.1 || $this->helper->config->get('show_zero_rating') != 0)):?>
								<span class="star-<?php echo $rating->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($rating->rating * 2) ?> fa fa-star regular-icon"></span>
							<?php endif ?>

						</td>
						<?php
					}

					for ($i = 0; $i < $item_notpresnt; $i++)
					{
						?><td></td><?php
					}
					?>
				</tr>
			<?php endif;?>

			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_SOLD_BY') ?></b></td>
				<?php
				foreach ($this->items as $product)
				{
					$sold_by = $product->seller_store ? $product->seller_store : ($product->seller_name ? $product->seller_name : ($product->seller_company ? $product->seller_company : $product->seller_username));

					echo '<td class="center">' . $sold_by . '</td>';
				}

				for ($i = 0; $i < $item_notpresnt; $i++)
				{
					?><td></td><?php
				}
				?>
			</tr>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_FEATURES') ?></b></td>
				<?php foreach ($this->items as $product): ?>
				<td>
					<ul class="product-features">
						<?php
						$features = array_filter((array) json_decode($product->variant_features, true), 'trim');

						if (count($features) == 0)
						{
							$features = array_filter((array) json_decode($product->features, true), 'trim');
						}

						foreach ($features as $feature)
						{
							echo '<li>' . htmlspecialchars($feature) . '</li>';
						}
						?>
					</ul>
				</td>
				<?php endforeach; ?>

				<?php
				for ($i = 0; $i < $item_notpresnt; $i++)
				{
					?><td></td><?php
				}
				?>
			</tr>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_PRODUCT_SUMMARY') ?></b></td>
				<?php
				foreach ($this->items as $product)
				{
					echo '<td><span class="readmore">' . $this->escape($product->introtext) . '</span></td>';
				}

				for ($i = 0; $i < $item_notpresnt; $i++)
				{
					?><td></td><?php
				}
				?>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->groups as $group)
			{
				?>
				<tr class="separator">
					<td colspan="<?php echo count($this->items) + 1 ?>"></td>
				</tr>
				<tr>
					<th class="group-header" colspan="<?php echo count($this->items) + 1 ?>"><?php echo $group->title ?></th>
				</tr>
				<?php
				foreach ($group->fields as $field)
				{
					?>
					<tr>
						<th style="width:30%;">
							<?php echo $this->escape($field->title) ?>
						</th>
						<?php
						foreach ($items as $product)
						{
							$obj   = ArrayHelper::getValue($product->specifications, $field->id);
							$value = is_object($obj) ? $obj->value : '';
							$value = $this->helper->field->renderValue($value, $field->type, $field);

							echo '<td>' . $value . '</td>';
						}
						?>
					</tr>
					<?php
				}
			}

			for ($i = 0; $i < $item_notpresnt; $i++)
			{
				?><th></th><?php
			}

			$whatsInBox = $this->helper->config->get('show_whats_in_box');

			if ($whatsInBox): ?>
				<tr>
					<th class="com-lbl"><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_WHATS_IN_BOX') ?></th>
					<?php foreach ($items as $product): ?>
					<td>
						<?php if ($product->whats_in_box): ?>
							<div class="content-text"><?php echo $product->whats_in_box ?></div>
						<?php endif; ?>
					</td>
					<?php endforeach; ?>

					<?php
					for ($i = 0; $i < $item_notpresnt; $i++)
					{
						?><td></td><?php
					}
					?>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="option" value="com_sellacious"/>
	<input type="hidden" name="task" value="" />
</form>
<div class="clearfix"></div>
