<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * list controller class
 *
 * @since  2.0.0
 */
class SellaciousControllerMessages extends SellaciousControllerBase
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_MESSAGES';

	/**
	 * AJAX Method to send Message
	 *
	 * @since   2.0.0
	 */
	public function sendMessageAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$data  = $this->app->input->get('jform', array(), 'Array');
			$model = $this->getModel('Messages', 'SellaciousModel');

			$message = $model->saveMessage($data);
			$bubbles = array();
			$ref     = array_filter(ArrayHelper::getValue($data, 'ref', array()));

			if (!empty($ref))
			{
				$sender    = ArrayHelper::getValue($data, 'sender', 0);
				$recipient = ArrayHelper::getValue($data, 'recipient', 0);

				$referenceMessage = $this->helper->message->getLastSystemMessage($sender, $recipient);

				if ($referenceMessage)
				{
					$bubbles[] = JLayoutHelper::render('com_sellacious.messages.bubbles.system_message', array('message' => $referenceMessage));
				}
			}

			$bubbles[] = JLayoutHelper::render('com_sellacious.messages.bubbles.bubble', array('message' => $message));

			echo new JResponseJson(array('bubbles' => $bubbles, 'message' => $message), '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}

	/**
	 * AJAX Method to get message thread
	 *
	 * @since   2.0.0
	 */
	public function getMessagesAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}
			
			$loadLimit = $this->helper->config->get('messages_load_limit', 25);

			$threadId       = $this->app->input->getInt('thread_id', 0);
			$lastMessageId  = $this->app->input->getInt('last_message_id', 0);
			$firstMessageId = $this->app->input->getInt('first_message_id', 0);
			$threadOptions  = array(
				'order'        => 'DESC',
				'limit'        => $loadLimit,
				'second_order' => true,
			);
			
			if ($lastMessageId > 0)
			{
				$threadOptions['start_id']   = $lastMessageId;
				$threadOptions['start_sign'] = '>';
			}
			elseif ($firstMessageId > 0)
			{
				$threadOptions['start_id']   = $firstMessageId;
				$threadOptions['start_sign'] = '<';
			}
			
			$thread      = $this->helper->message->getThread($threadId, $threadOptions);
			$lastMessage = $this->helper->message->getLastMessageByThread($threadId);
			$layout      = '';
			
			if (!empty($thread))
			{
				$layout .= JLayoutHelper::render('com_sellacious.messages.bubbles.thread', array('thread' => $thread));
				
				$lastMsg        = end($thread);
				$lastMessageId  = $lastMsg->id;
				$firstMsg       = reset($thread);
				$firstMessageId = $firstMsg->id;
			}

			echo new JResponseJson(array('thread' => $layout, 'last_message' => $lastMessage, 'last_message_id' => $lastMessageId, 'first_message_id' => $firstMessageId, 'message_count' => count($thread)), '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}

	/**
	 * AJAX Method to read all messages in thread
	 *
	 * @since   2.0.0
	 */
	public function readThreadAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$threadId = $this->app->input->getInt('thread_id', 0);
			$model    = $this->getModel('Messages', 'SellaciousModel');
			
			$model->readThread($threadId);

			echo new JResponseJson('');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}

	/**
	 * AJAX Method to get unread count of all active threads
	 *
	 * @since   2.0.0
	 */
	public function getUnreadCountByThread()
	{
		$threadIds = $this->app->input->getString('threadIds');
		$threadIds = json_decode($threadIds);
		$count     = array();

		foreach ($threadIds as $id)
		{
			$count[$id]['last']   = $this->helper->message->getLastMessageByThread($id);
			$count[$id]['unread'] = $this->helper->message->getUnreadCountByThread($id);
		}

		echo new JResponseJson($count);

		$this->app->close();
	}
}
