<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var stdClass[] $displayData */
$addresses = $displayData;

if (!$addresses)
{
	echo JText::_('COM_SELLACIOUSOPC_USER_ADDRESS_NO_SAVED_ITEM');
}

foreach ($addresses as $i => $address)
{
	?>
	<li class="address-item" id="address-item-<?php echo (int) $address->id ?>">
		<div class="ctech-float-right address-action">
			<button type="button" class="ctech-btn ctech-btn-sm ctech-btn-default ctech-text-danger hasTooltip remove-address"
					data-placement="bottom" data-id="<?php echo (int) $address->id ?>"
					title="Delete"><i class="fa fa-trash-alt"></i></button>
			<a href="#address-form-<?php echo (int) $address->id ?>"
			   role="button" data-toggle="ctech-modal" data-placement="bottom"
			   class="ctech-btn ctech-btn-sm ctech-btn-default hasTooltip ctech-text-primary edit-address" title="Edit"><i class="fa fa-edit"></i></a>
		</div>
		<?php echo JLayoutHelper::render('com_sellaciousopc.user.address.box', $address); ?>
	</li>
	<?php
}
