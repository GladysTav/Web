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

/** @var stdClass[] $displayData */
$addresses = $displayData;

if (!$addresses)
{
	echo JText::_('COM_SELLACIOUS_USER_ADDRESS_NO_SAVED_ITEM');
}

foreach ($addresses as $i => $address)
{
	?>
	<li class="address-item" id="address-item-<?php echo (int) $address->id ?>">
		<div class="ctech-float-right address-action">
			<button type="button" class="ctech-btn ctech-btn-small ctech-btn-default hasTooltip remove-address"
					data-placement="bottom" data-id="<?php echo (int) $address->id ?>"
					title="Delete"><i class="fa fa-trash-alt"></i></button>
			<a href="#address-form-<?php echo (int) $address->id ?>"
			   role="button" data-toggle="ctech-modal" data-placement="bottom"
			   class="ctech-btn  ctech-btn-small ctech-btn-default hasTooltip" title="Edit"><i class="fa fa-edit"></i></a>
		</div>
		<?php echo JLayoutHelper::render('com_sellacious.user.address.box', $address); ?>
	</li>
	<?php
}

if (count($addresses) % 2 !== 0)
{
	?>
	<li class="address-item odd-address-item">
		<a href="#address-form-0" role="button" data-toggle="ctech-modal" class="btn-new-address"><i class="fa fa-plus"></i></a>
	</li>
	<?php
}

