<?php
/**
 * @version     1.7.4
 * @package     Sellacious Contact Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'mod_sellacious_contact/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

?>

<div class="mod-sellacious-contact <?php echo $class_sfx ?>">
	<?php if ($contact_name): ?>
		<div class="mod-contact-group">
			<div class="mod-contact-value">
				<i class="fa fa-user" style="color: <?php echo $icon_color; ?>;"></i><span style="color: <?php echo $text_color; ?>;"><?php echo $contact_name; ?></span>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($contact_email): ?>
		<div class="mod-contact-group">
			<div class="mod-contact-value">
				<i class="fa fa-envelope" style="color: <?php echo $icon_color; ?>;"></i><a style="color: <?php echo $text_color; ?>;" href="mail:<?php echo $contact_email; ?>"><?php echo $contact_email; ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($contact_phone): ?>
		<div class="mod-contact-group">
			<div class="mod-contact-value">
				<i class="fa fa-phone" style="color: <?php echo $icon_color; ?>;"></i><a style="color: <?php echo $text_color; ?>;" href="tel:<?php echo $contact_phone; ?>"><?php echo $contact_phone; ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($contact_address): ?>
		<div class="mod-contact-group">
			<div class="mod-contact-value">
				<i class="fa fa-location-arrow" style="color: <?php echo $icon_color; ?>;"></i><span style="color: <?php echo $text_color; ?>;"><?php echo $contact_address; ?></span>
			</div>
		</div>
	<?php endif; ?>
</div>
