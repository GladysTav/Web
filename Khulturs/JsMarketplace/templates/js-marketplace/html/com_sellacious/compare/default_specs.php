<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewCompare $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/fe.view.compare.js', true, true);
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.compare.css', null, true);

$me                 = JFactory::getUser();
$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);
$current_url        = JUri::getInstance()->toString();
$login_url          = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

$items      = $this->items;
$c_currency = $this->helper->currency->current('code_3');
$limit = (int) $this->helper->config->get('compare_limit', 3);
?>


<h2 class="comapre-page-title"><i class="fa fa-balance-scale "></i> <?php echo JText::_("SELLA_SELLACIOUS_COMPARE_LABEL");?> </h2>

<form  class="compare-form" action="<?php echo 'index.php' ?>">
	<div class="table-responsive">
		<table class="table tbl-specifications">
			<thead>
			<tr>
				<th colspan="1">&nbsp;</th>
				<?php
                $item_present =count($items);
                $item_notpresnt = $limit - $item_present;
                $i =1;
				foreach ($this->items as $item)
				{
					$p_code = $this->helper->product->getCode($item->id, $item->variant_id, $item->seller_uid);
					$url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $p_code);
					?>
					<th colspan="1" class="title v-top">
						<label class="remove-compare">
							<input type="checkbox" name="cid[]" value="<?php echo $item->code ?>" class="hidden" />&times;
						</label>
                        <div class="title-compare">
						<?php
						$format = $item->variant_title ? '<a href="%s">%s - %s</a>' : '<a href="%s">%s</a>';
						echo sprintf($format, $url, $item->title, $item->variant_title); ?></div>
                        <?php
                        if($i >= $limit) break;
                        $i++ ;?>

					</th>
					<?php
				}
				?>

               <?php
               for ($i = 0; $i <  $item_notpresnt; $i++) { ?>
                <th colspan="1" class="title v-top">
                    <div class="title-compare">
                       <a href="index.php"><?php  echo JText::_("COM_SELLACIOUS_COMPARE_PRODCUT_NOT_PRESENT"); ?> </a>
                    </div>
                </th>
                <?php } ?>

			</tr>
			<tr>
				<td>&nbsp;</td>
				<?php
				foreach ($this->items as $item)
				{
					?>
					<td style="width:25%;" class="center">
						<img class="product-image" src="<?php echo reset($item->images); ?>"/>
					</td><?php
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td style="width:25%;" class="center">
                        <div class="no-product-image">
                            <div class="icon-no-img fa fa-image"></div>
                        </div>
                    </td>
                <?php
                } ?>

			</tr>
			<tr>
				<th class="com-lbl"><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_PRICE') ?></th>
				<?php
				foreach ($this->items as $item)
				{
					$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
					$price      = $this->helper->currency->display($item->sales_price, $s_currency, $c_currency, true);
					?>
					<th>
						<?php if ($login_to_see_price && $me->guest): ?>
							<a href="<?php echo $login_url ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
						<?php else: ?>
							<span class="item-price"><?php echo round($item->sales_price, 2) >= 0.01 ? $price : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE') ?></span>
							<span><button type="button" class="btn btn-info btn-cart-sm btn-sm btn-comp-buy btn-add-cart pull-right"
										  data-item="<?php echo $item->code ?>" data-checkout="true"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); ?></button></span>
						<?php endif; ?>
					</th>
					<?php
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <th>
                    </th>
                    <?php
                } ?>

            </tr>
			<?php $rating_display = (array) $this->helper->config->get('product_rating_display'); ?>
			<?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display) || in_array('product_modal', $rating_display))): ?>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_RATING') ?></b></td>
				<?php
				foreach ($this->items as $item)
				{
					$rating = $this->helper->rating->getProductRating($item->id);
					$rated  = $rating ? round($rating->rating, 0) : 0;
					?>
					<td class="center">
						<div class="product-rating rating-stars star-<?php echo $rated * 2 ?>"><?php echo number_format($rated, 1) ?></div>
					</td>
					<?php
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td></td>
                    <?php
                } ?>

			</tr>
			<?php endif; ?>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_SOLD_BY') ?></b></td>
				<?php
				foreach ($this->items as $item)
				{
					$sold_by = $item->seller_store ? $item->seller_store : ($item->seller_name ? $item->seller_name : ($item->seller_company ? $item->seller_company : $item->seller_username));
					echo '<td class="center">' . $sold_by . '</td>';
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td></td>
                    <?php
                } ?>
			</tr>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_FEATURES') ?></b></td>
				<?php
				foreach ($this->items as $item)
				{
					echo '<td>';
					?>
					<ul class="product-features">
						<?php
						$features = array_filter((array) json_decode($item->variant_features, true), 'trim');

						if (count($features) == 0)
						{
							$features = array_filter((array) json_decode($item->features, true), 'trim');
						}

						foreach ($features as $feature)
						{
							echo '<li>' . htmlspecialchars($feature) . '</li>';
						}
						?>
					</ul>
					<?php
					echo '</td>';
				}
				?>


                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td></td>
                    <?php
                } ?>
			</tr>
			<tr>
				<td class="com-lbl"><b><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_PRODUCT_SUMMARY') ?></b></td>
				<?php
				foreach ($this->items as $item)
				{
					echo '<td><span class="readmore">' . $this->escape($item->introtext) . '</span></td>';
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td></td>
                    <?php
                } ?>
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
						foreach ($items as $item)
						{
							$obj   = ArrayHelper::getValue($item->specifications, $field->id);
							$value = is_object($obj) ? $obj->value : '';
							$value = $this->helper->field->renderValue($value, $field->type, $field);

							echo '<td>' . $value . '</td>';
						}
						?>
					</tr>
					<?php
				}
			}
			?>

            <?php
            for ($i = 0; $i <  $item_notpresnt; $i++)
            { ?>
                <th></th>
                <?php
            } ?>
			<tr>
				<th class="com-lbl"><?php echo JText::_('COM_SELLACIOUS_COMPARE_LIST_HEADING_WHATS_IN_BOX') ?></th>
				<?php
				foreach ($items as $item)
				{
					?><td><?php if ($item->whats_in_box): ?>
						<div class="content-text"><?php echo $item->whats_in_box ?></div><?php
					endif;
					?></td><?php
				}
				?>

                <?php
                for ($i = 0; $i <  $item_notpresnt; $i++)
                { ?>
                    <td></td>
                    <?php
                } ?>
			</tr>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="option" value="com_sellacious" />
	<input type="hidden" name="task" value="" />
</form>
<div class="clearfix"></div>
