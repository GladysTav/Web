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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

JHtml::_('jquery.framework');

/** @var  \SellaciousView  $this */
$app    = JFactory::getApplication();
$editId = $app->getUserState('com_sellacious.edit.shoprule.id');

if (!$editId)
{
	echo '<div class="center">' . JText::_('COM_SELLACIOUS_SHOPRULE_SLAB_NOT_EDITING') . '</div>';

	return;
}

$methodName = $app->getUserState('com_sellacious.edit.shoprule.data.method_name');
$ruleTitle  = $app->getUserState('com_sellacious.edit.shoprule.data.title');

if (!$methodName)
{
	$slabs     = $this->helper->shopRule->getSlabs($editId);
	$ruleTitle = $this->helper->shopRule->loadResult(array('id' => $editId, 'list.select' => 'a.title'));
}
elseif ($methodName == 'slabs.price')
{
	$slabs = $app->getUserState('com_sellacious.edit.shoprule.data.slabs.price_slabs');

}
elseif ($methodName == 'slabs.quantity')
{
	$slabs = $app->getUserState('com_sellacious.edit.shoprule.data.slabs.quantity_slabs');
}
else
{
	$slabs = array();
}

if (is_string($slabs))
{
	$slabs = json_decode($slabs, true);
}

$jsonFile = ArrayHelper::getValue($slabs, 'data', null);

if ($jsonFile)
{
	$slabs = $this->helper->filestorage->extractFromFile($jsonFile);
}

$this->state->set('shoprule.title', $ruleTitle);

if ($app->input->getCmd('format') == 'csv')
{
	echo $this->loadTemplate('csv', $slabs);
}
else
{
	echo $this->loadTemplate('html', $slabs);
}
