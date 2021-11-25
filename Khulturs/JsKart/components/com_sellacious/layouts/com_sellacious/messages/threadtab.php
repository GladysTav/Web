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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

$helper        = SellaciousHelper::getInstance();
$me = JFactory::getUser();

/** @var  stdClass  $displayData */
$message         = $displayData;
$senderName      = $message->sender == $me->id ? $message->recipient_name : $message->sender_name;
$recipientName = $message->sender == $me->id ? $message->recipient_name : $message->sender_name;
$chatWith        = $message->sender == $me->id ? $message->recipient : $message->sender;
$chatWithUser    = JFactory::getUser($chatWith);
$lastMessage     = isset($message->last) ? $message->last : array();
$lastMessageBody = ArrayHelper::getValue($lastMessage, 'body', $message->title);
$lastMessageDate = ArrayHelper::getValue($lastMessage, 'created', '');
$unreadClass     = (int) $message->unread > 0 ? '' : 'ctech-d-none';

if ($helper->seller->is($chatWith))
{
    $filter = array('list.select' => 'store_name', 'user_id' => $chatWith);
    $name   = $helper->seller->loadResult($filter);
    if ($name) $recipientName = $name;
}
?>
<div class="message-tab-details" data-thread="<?php echo $message->id; ?>">
	<span class="message-sender"><?php echo $recipientName; ?></span>
	<span class="message-count ctech-badge-primary ctech-float-right ctech-rounded-circle ctech-p-1 <?php echo $unreadClass; ?>"><?php echo $message->unread; ?></span>
	<?php if ($chatWithUser->block): ?>
		<span class="ctech-text-warning"><i class="fa fa-exclamation-triangle"></i></span>
	<?php endif; ?>
	<div class="message-last">
		<span class="last-message-body"><?php echo $lastMessageBody; ?></span>
		<span class="last-message-time ctech-float-right ctech-text-secondary"><?php echo $lastMessageDate; ?></span>
	</div>
</div>
