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
$me     = JFactory::getUser();
$thread = $displayData['thread'];

if (is_array($thread) && count($thread)):
	foreach ($thread as $item):
		$layout = $item->context == 'system_message' ? 'com_sellacious.messages.bubbles.system_message' : 'com_sellacious.messages.bubbles.bubble';
		?>
        <li class="clearfix">
			<?php echo JLayoutHelper::render($layout, array('message' => $item)); ?>
        </li>

	<?php endforeach;
endif;
