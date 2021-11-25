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

$helper = SellaciousHelper::getInstance();

if ($params->get('link')): ?>
	<div>
		<a target="_blank" href="<?php echo $url ?>" class="product-external-link hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUERY_OPEN_PRODUCT_BUTTON') ?>">
			<i class="fa fa-external-link" aria-hidden="true"></i></a>
	</div>
<?php endif; ?>

<?php foreach ($fields as $field):
	$value = '';

	if (is_object($field->value))
	{
		$fieldItem  = $helper->field->getItem($field->id);

		if ($fieldItem->type == 'unitcombo')
		{
			$value = $helper->unit->explain($field->value, true);
		}
	}
	else
	{
		$value = $field->value;
	}
	?>

	<div>
		<b><?php echo $field->title ?></b>
		<?php echo $value ?>
	</div>
<?php endforeach;
