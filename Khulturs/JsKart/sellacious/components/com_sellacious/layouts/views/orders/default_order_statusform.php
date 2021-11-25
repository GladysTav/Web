<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var  SellaciousViewOrders $this */
/** @var  stdClass $tplData */
$order    = $tplData;
$statuses = $order->statuses;
$sellers  = $order->sellers;
$me       = JFactory::getUser();
?>
<table class="status-form-table w100p">
    <thead>
	<?php if (!$this->helper->access->check('order.item.edit.status') || (count($sellers) == 1)):
	$defaultSeller = count($sellers) == 1 ? $sellers[0]->seller_uid : $me->id; ?>
    <tr>
        <input type="hidden" name="jform[seller_exclusive]" value="1"/>
        <input type="hidden" name="jform[seller_uid]" value="<?php echo $defaultSeller ?>"/>
		<?php else:
		$defaultSeller = $order->sellers[0]->seller_uid;
		$sellerStatus  = $this->helper->order->getStatus($order->id, null, $defaultSeller);
		$statuses      = $this->helper->order->getStatuses($sellerStatus->s_context, $sellerStatus->s_id, false, true);
		?>
    <tr>
        <td style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_SELLER'); ?> </td>
        <td>
            <input type="hidden" name="jform[seller_exclusive]" value="1"/>
            <select name="jform[seller_uid]" title="" class="w100p order-sellers-list">
				<?php
				$params = array(
					'option.key'  => 'seller_uid',
					'option.text' => 'seller_name',
					'list.select' => $defaultSeller,
				);
				echo JHtml::_('select.options', $order->sellers, $params); ?>
            </select>
        </td>
    </tr>
    <tr>
		<?php endif; ?>
        <td style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_NEW_STATUS'); ?> </td>
        <td style="width:70%;">
            <input type="hidden" name="jform[order_id]" value="<?php echo $order->id ?>"/>
            <select name="jform[status]" title="" class="w100p order-status-list">
                <option value=""><?php echo JText::_('COM_SELLACIOUS_ORDER_STATUS_KEEP_CURRENT') ?></option>
				<?php
				$params = array(
					'option.key'  => 'id',
					'option.text' => 'title',
				);
				echo JHtml::_('select.options', $statuses, $params); ?>
            </select>
        </td>
    </tr>
    </thead>
    <tbody>
    <!-- Ajax loaded content here -->
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2" class="text-right">
            <button type="button" class="btn btn-default btn-order-status-close">
                <i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_CLOSE'); ?>
            </button>
            <button type="button" class="btn btn-primary btn-order-status-save">
                <i class="fa fa-save"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_SAVE'); ?>
            </button>
			<?php echo JHtml::_('form.token'); ?>
        </td>
    </tr>
    </tfoot>
</table>
