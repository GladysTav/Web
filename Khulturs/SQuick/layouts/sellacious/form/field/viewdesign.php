<?php
/**
 * @version     2.0.0
 * @package     Sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/** @var  array   $displayData  */
/** @var  array   $options      Options available for this field. */
/** @var  string  $id           DOM id of the field. */
/** @var  string  $name         Name of the input field. */
/** @var  string  $value        Value of the field. */
extract($displayData);

JHtml::_('jquery.framework');

JHtml::_('script', 'sellacious/field.viewdesign.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/field.viewdesign.css', array('relative' => true, 'version' => S_VERSION_CORE));
?>

<div class="view-selector">
	<?php
	foreach ($options as $option)
	{
		?>
		<div class="view-option <?php echo $option['design'] === $value ? ' selected' : ''; ?>" data-design="<?php echo $option['design']; ?>">
			<span class="view-preview" style="background-image: url('<?php echo $option['preview']; ?>')"></span>
			<span class="view-name"><?php echo ucfirst($option['design']); ?></span>
		</div>
		<?php
	}
	?>
	<input type="hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
</div>
