<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass  $displayData */
$me      = JFactory::getUser();
$helper  = SellaciousHelper::getInstance();
$message = $displayData['message'];
?>
<div class="message-system">
	<div class="label ctech-btn-secondary label-tag ">
		<?php echo $message->body; ?>
	</div>
</div>
