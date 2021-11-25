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

/** @var  array  $displayData */
extract($displayData);

/**
 * Layout variables
 * ---------------------
 * @var  string  $text         The label text
 * @var  string  $description  An optional description to use in a tooltip
 * @var  string  $for          The id of the input this label is for
 * @var  bool    $required     True if a required field
 * @var  array   $classes      A list of classes
 * @var  string  $position     The tooltip position. Bottom for alias
 */

$classes = array_filter((array) $classes);
$id      = $for . '-lbl';
$title   = '';

if (!empty($description))
{
	if ($text && $text !== $description)
	{
		JHtml::_('bootstrap.popover');

		$classes[] = 'hasPopover';
		$title     = ' title="' . htmlspecialchars(trim($text, ':')) . '" data-content="' . htmlspecialchars($description) . '"';

		if (!$position && JFactory::getLanguage()->isRtl())
		{
			$position = ' data-placement="left" ';
		}
	}
	else
	{
		JHtml::_('bootstrap.tooltip');

		$classes[] = 'hasTooltip';
		$title     = ' title="' . JHtml::_('tooltipText', trim($text, ':'), $description, 0) . '"';
	}
}

if ($required)
{
	$classes[] = 'required';
}

$classes = $classes ? ' class="' . implode(' ', $classes) . '"' : '';
?>
<label id="<?php echo $id; ?>" for="<?php echo $for; ?>" <?php echo $classes . ' ' . $title . ' ' . $position; ?>>
	<?php echo $text; ?>
	<?php if ($required): ?><span class="star">&#160;*</span><?php endif; ?>
</label>
