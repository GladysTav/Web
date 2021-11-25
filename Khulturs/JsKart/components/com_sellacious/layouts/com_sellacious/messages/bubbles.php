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
use Sellacious\Media\Image\ImageHelper;

defined('_JEXEC') or die;

/** @var  stdClass  $displayData */
$me            = JFactory::getUser();
$helper        = SellaciousHelper::getInstance();
$message       = $displayData;
$thread        = $message->thread;
$recipient     = $message->sender == $me->id ? $message->recipient : $message->sender;
$sender        = $message->recipient == $me->id ? $message->recipient : $message->sender;
$recipientName = $message->sender == $me->id ? $message->recipient_name : $message->sender_name;

if ($helper->seller->is($recipient))
{
	$filter = array('list.select' => 'store_name', 'user_id' => $recipient);
	$name   = $helper->seller->loadResult($filter);
	if ($name) $recipientName = $name;
}
$recipientUser = JFactory::getUser($recipient);

$lastMsgId  = 0;
$firstMsgId = 0;

if (!empty($thread))
{
    $lastMsg    = end($thread);
	$lastMsgId  = $lastMsg->id;
	$firstMsg   = reset($thread);
	$firstMsgId = $firstMsg->id;
}

$filter    = array('list.select' => '*', 'user_id' => $recipient);
$record_id = $helper->seller->loadResult($filter);

$logo =  $record_id ? ImageHelper::getImage('sellers', $record_id, 'logo') ?: ImageHelper::getImage('user', $recipient, 'avatar') : ImageHelper::getImage('user', $recipient, 'avatar');

$image = $logo ? '<img class="user-avatar" src="' . $logo->getUrl() . '" alt="">' : '<img class="user-avatar" src="' . ImageHelper::getBlank('sellers', 'logo') . '" alt="">';
?>
<div class="chat">
	<div class="chat-header ctech-clearfix">
		<?php echo $image; ?>
		<div class="chat-about">
			<div class="chat-with">
				<?php echo JText::sprintf('COM_SELLACIOUS_MESSAGE_CHAT_SENDER', $recipientName); ?>
				<?php if ($recipientUser->block): ?>
					<span>
						<i class="fa fa-exclamation-triangle ctech-text-warning"></i>
						<small class="ctech-text-warning"><?php echo JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_USER_INACTIVE')?></small>
					</span>
				<?php endif; ?>
			</div>
			<?php if (is_array($thread) && count($thread)): ?>
                <div class="ctech-text-success"><?php echo JText::sprintf('COM_SELLACIOUS_MESSAGE_TOTAL_MESSAGES', count($thread)) ;?></div>
            <?php endif; ?>
		</div>
	</div>
	<div class="chat-history chat-history-<?php echo $message->id ?>" data-thread-id="<?php echo $message->id ?>" data-last-message-id="<?php echo $lastMsgId ?>" data-first-message-id="<?php echo $firstMsgId ?>">
        <ul>
            <li class="ctech-clearfix loading-messages ctech-text-center" style="display:none;">
				<i class="fa fa-spinner fa-spin"></i>
            </li>
			<li class="ctech-clearfix no-more-messages ctech-text-center" style="display:none;">
                <?php echo JText::_('COM_SELLACIOUS_MESSAGE_NO_MORE_MESSAGES'); ?>
            </li>
            <?php echo JLayoutHelper::render('com_sellacious.messages.bubbles.thread', array('thread' => $thread));?>
        </ul>
	</div>
    <div class="chat-message clearfix">
        <form action="" method="post" name="chatForm<?php echo $message->id ?>" id="chatForm<?php echo $message->id ?>" enctype="multipart/form-data">
            <textarea name="jform[body]" id="message-to-send-<?php echo $message->id ?>" placeholder="<?php echo JText::_('COM_SELLACIOUS_MESSAGE_TYPE_CHAT'); ?>" rows="3"></textarea>
            <input type="hidden" name="jform[sender]" value="<?php echo $sender; ?>">
            <input type="hidden" name="jform[recipient]" value="<?php echo $recipient; ?>">
            <input type="hidden" name="jform[parent_id]" value="<?php echo $message->id; ?>">
            <input type="hidden" name="jform[ref][context]" value="<?php echo isset($message->ref_context) ? $message->ref_context : ''; ?>">
            <input type="hidden" name="jform[ref][value]" value="<?php echo isset($message->ref_value) ? $message->ref_value : ''; ?>">
            <input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1"/>
            <button type="button" class="ctech-float-right ctech-btn ctech-btn-primary btn-send-<?php echo $message->id ?>"><?php echo JText::_('COM_SELLACIOUS_MESSAGE_REPLY_TITLE')?> </button>
        </form>
    </div>
</div>
