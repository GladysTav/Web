<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Access\AccessHelper;

/** @var  stdClass[] $displayData */
$data = $displayData;

$helper     = SellaciousHelper::getInstance();
$records    = ArrayHelper::getValue($displayData, 'log');
$sellerUids = array_unique(array_filter(ArrayHelper::getColumn($records, 'seller_uid')));
$order      = (object)ArrayHelper::getValue($displayData, 'order');
$item       = (object) ArrayHelper::getValue($displayData, 'item', null);
?>
<table class="w100p table-bordered table">
	<thead>
	<tr>
		<th style="width:10%;">Date        	 </th>
		<th style="width:10%;">Status      	 </th>
		<?php if (!empty($sellerUids)): ?>
            <th style="width:10%;">Seller      	 </th>
		<?php endif; ?>
		<th style="width:20%;">Customer Note </th>
		<th style="width:20%;">Note          </th>
		<th style="width:30%;">Details       </th>
		<th style="width:10%;">Updated By    </th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($records as $record)
	{
		?>
		<tr>
			<td>
				<?php echo JHtml::_('date', $record->created, 'F d, Y'); ?><br>
				<?php echo JHtml::_('date', $record->created, 'H:i A'); ?>
			</td>
			<td><?php echo htmlspecialchars($record->s_title); ?></td>
			<?php if (!empty($sellerUids)): ?>
                <td>
					<?php if ($record->seller_uid > 0):
						$seller = JFactory::getUser($record->seller_uid);
						echo $seller->name;
					endif; ?>
                </td>
			<?php endif; ?>
			<td><?php echo htmlspecialchars($record->customer_notes); ?></td>
			<td><?php echo htmlspecialchars($record->notes); ?></td>
			<td style="padding: 0; border: 0;">
				<table class="table table-bordered table-hover" style="margin: -1px; width: calc(100%+2px);">
				<?php
				if (!empty($record->shipment))
				{
					$info = array();

					foreach ($record->shipment as $key => $value)
					{
						$label  = JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_' . strtoupper($key) . '_LBL');
						$info[] = sprintf('<tr><th style="width:20%%;" class="nowrap">%s:</th><td>%s</td></tr>', $label, htmlspecialchars($value));
					}

					echo implode($info);
				}
				?>
				</table>
			</td>
			<td>
			<?php
			if ($record->created_by == $order->customer_uid)
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
			}
			elseif (isset($item->seller_uid) && $record->created_by == $item->seller_uid)
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
			}
			elseif ($helper->staff->getCategory($record->created_by))
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_STAFF');
			}
			elseif ($helper->manufacturer->getCategory($record->created_by))
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_MANUFACTURER');
			}
			else
			{
				$user = JFactory::getUser($record->created_by);

				if (AccessHelper::allow('app.manage'))
				{
					echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
				}
                elseif ($helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.', $user->id))
				{
					echo $user->get('name', 'N/A');
				}
				else
				{
					echo JText::sprintf('COM_SELLACIOUS_ORDER_USERTYPE_UNKNOWN', $user->get('name', 'N/A'));
				}
			}
			?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
