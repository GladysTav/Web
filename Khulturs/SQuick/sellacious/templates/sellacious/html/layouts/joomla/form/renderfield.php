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
 * @var  array   $options  Optional parameters
 * @var  string  $label    The html code for the label (not required if $options['hiddenLabel'] is true)
 * @var  string  $input    The input field html code
 */
if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

$class   = empty($options['class']) ? '' : $options['class'];
$rel     = empty($options['rel']) ? '' : $options['rel'];
$onlyLbl = empty($options['onlyLabel']) ? false : $options['onlyLabel'];
$hideLbl = empty($options['hiddenLabel']) ? false : $options['hiddenLabel'];
$lblSize = empty($options['lblSize']) ? 'sm' : $options['lblSize'];
$noWrap  = empty($options['noWrapper']) ? false : $options['noWrapper'];
?>
<?php if (!$noWrap):?>
<div class="hasTooltip row <?php echo $class ?>" <?php echo $rel; ?> style="border-bottom: 1px solid #fafafa">
<?php else: ?>
<div <?php echo $rel; ?>>
<?php endif; ?>
	<?php if ($onlyLbl) : ?>
		<div class="<?php echo $noWrap ? 'col-md-12' : 'controls col-md-12'; ?>"><?php echo $label; ?></div>
	<?php elseif ($hideLbl) : ?>
		<div class="<?php echo $noWrap ? 'col-md-12' : 'controls col-md-12'; ?>"><?php echo $input; ?></div>
	<?php elseif ($lblSize == 'lg'): ?>
		<div class="form-label col-sm-4 col-md-4 col-lg-3"><?php echo $label; ?></div>
		<div class="controls col-sm-8 col-md-8 col-lg-9"><?php echo $input; ?></div>
	<?php elseif ($lblSize): ?>
		<div class="form-label col-xs-12 col-sm-3 col-md-3 col-lg-2"><?php echo $label; ?></div>
		<div class="controls col-xs-12 col-sm-9 col-md-9 col-lg-10"><?php echo $input; ?></div>
	<?php endif; ?>
</div>
<div class="clearfix"></div>
