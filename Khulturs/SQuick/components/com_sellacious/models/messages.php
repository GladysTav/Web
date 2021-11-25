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
 * Methods supporting a list of Sellacious records.
 *
 * @since  2.0.0
 */
class SellaciousModelMessages extends SellaciousModelList
{
	/** @var  array */
	protected $items;

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$context = $this->app->input->get('context', null, 'string');
		$this->state->set('filter.ref_context', $context);

		$ref = $this->app->input->get('ref', null, 'string');
		$this->state->set('filter.ref_value', $ref);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db     = $this->getDbo();
		$me     = JFactory::getUser();
		$userId = (int)$me->id;
		$query  = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_messages', 'a'))
			->where('a.level > 0');

		// Add the level in the tree.
		$query->select('(CASE WHEN c3.date_sent IS NOT NULL THEN MAX(c3.date_sent) ELSE a.date_sent END) AS last_update')
			->join('LEFT', '#__sellacious_messages AS c3 ON a.lft < c3.lft AND c3.rgt < a.rgt')
			->group('a.id, a.lft, a.rgt, a.parent_id')
			->order('last_update DESC');

		// Sender name
		$query->select('s.name as sender_name')
			->join('LEFT', '#__users s ON s.id = a.sender');

		// Recipient name
		$query->select('r.name as recipient_name')
			->join('LEFT', '#__users r ON r.id = a.recipient');

		// messages where current user is the recipient
		$sub = $db->getQuery(true)
			->select('r.message_id')
			->from($db->qn('#__sellacious_message_recipients', 'r'))
			->where('r.recipient = ' . (int) $userId);

		$or = array(
			'a.sender = ' . (int) $userId,
			'a.recipient = ' . (int) $userId,
			'a.id IN (' . (string) $sub . ')',
		);

		$query->where('(' . implode(' OR ', $or) . ')');

		// First message will be the beginning of the thread, rest will be parent of the first message
		$query->where('a.parent_id = 1');

		return $query;
	}

	/**
	 * Process list to add items in message
	 *
	 * @param   array  $items
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function processList($items)
	{
		$recipient = $this->app->input->get('recipient', 0);
		$me        = JFactory::getUser();
		
		// Number of messages to load at first page load
		$loadLimit = $this->helper->config->get('messages_load_limit', 25);

		if (is_array($items))
		{
			$recipientThread = false;

			foreach ($items as &$item)
			{
				// Get all messages in a thread
				$threadOptions = array(
					'order'        => 'DESC',
					'limit'        => $loadLimit,
					'second_order' => true,
				);
				$item->thread  = $this->helper->message->getThread($item->id, $threadOptions);
				$item->last    = $this->helper->message->getLastMessageByThread($item->id);
				$item->unread  = $this->helper->message->getUnreadCountByThread(($item->id));

				if ($recipient && (($item->sender == $me->id && $item->recipient == $recipient) || ($item->recipient == $me->id && $item->sender == $recipient)))
				{
					$recipientThread = true;

					$item->ref_context = $this->state->get('filter.ref_context', '');
					$item->ref_value   = $this->state->get('filter.ref_value', '');
				}
			}

			// When recipient is sent in the URL but there is no thread, show an empty thread
			if ($recipient && !$recipientThread)
			{
				$thread                 = new stdClass();
				$thread->thread         = null;
				$thread->last           = null;
				$thread->unread         = null;
				$thread->id             = 0;
				$thread->parent_id      = 1;
				$thread->sender         = $recipient;
				$thread->recipient      = $me->id;
				$thread->is_read        = 0;
				$thread->title          = '';
				$thread->body           = '';
				$thread->sender_name    = JFactory::getUser($recipient)->get('name');
				$thread->recipient_name = $me->get('name');
				$thread->ref_context    = $this->state->get('filter.ref_context', '');
				$thread->ref_value      = $this->state->get('filter.ref_value', '');

				$items = array_merge(array($thread), $items);
			}
		}

		return parent::processList($items);
	}

	/**
	 * Method to save Message
	 *
	 * @param   array  $data  The message data
	 *
	 * @return  object
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function saveMessage($data)
	{
		$date       = JFactory::getDate();
		$parentId   = ArrayHelper::getValue($data, 'parent_id', 0);
		$sender     = ArrayHelper::getValue($data, 'sender', 0);
		$ref        = array_filter(ArrayHelper::getValue($data, 'ref', array()));
		$senderUser = JFactory::getUser($sender);

		unset($data['ref']);

		// Save reference
		if (!empty($ref))
		{
			$context      = ArrayHelper::getValue($ref, 'context', '');
			$value        = ArrayHelper::getValue($ref, 'value', '');
			$refMessageId = $this->helper->message->saveMessageReference($data, $context, $value);

			if ($refMessageId && !$data['parent_id'])
			{
				$data['parent_id'] = $refMessageId;
			}
		}

		if ($senderUser->block)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_MESSAGE_USER_BLOCKED_ERROR'));
		}

		$parentMessage = JTable::getInstance('Message', 'SellaciousTable');
		$parentMessage->load($parentId);

		$params = new Registry($parentMessage->get('params'));

		$array = array(
			'title'     => $parentMessage->get('title', ''),
			'context'   => 'message',
			'date_sent' => $date->toSql(),
			'state'     => 1,
			'remote_ip' => $this->app->input->server->getString('REMOTE_ADDR'),
			'params'    => $params->toArray(),
		);

		$data = array_merge($data, $array);

		if (!isset($data['parent_id']) || !$data['parent_id'])
		{
			$data['parent_id'] = 1;
		}

		if (empty($data['title']))
		{
			$data['title'] = substr($data['body'], 0, 15);
		}

		if (empty($data['params']))
		{
			$sel_cats = array($data['sender'], $data['recipient']);
			$user_ids = array($data['sender'], $data['recipient']);

			$data['params'] = array('recipients' => $sel_cats, 'users' => $user_ids);
		}

		/** @var SellaciousTableMessage $table */
		$table = JTable::getInstance('Message', 'SellaciousTable');
		$table->bind($data);

		$table->setLocation($table->parent_id, 'last-child');

		$table->check();
		$table->store();

		$message = (object) $table->getProperties();

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.message', $message, $isNew = true));

		return $message;
	}

	/**
	 * Method to read all messages of one thread
	 *
	 * @param  int  $parentId  The parent id
	 *
	 * @since  2.0.0
	 */
	public function readThread($parentId)
	{
		$me    = JFactory::getUser();
		$date  = JFactory::getDate();
		$db    = $this->_db;
		$query = $db->getQuery(true);
		$query->update($db->qn('#__sellacious_messages'))
			->set(array('is_read = 1', 'modified = ' . $db->q($date->toSql()), 'modified_by = ' . $me->get('id', 0)))
			->where('parent_id = ' . (int) $parentId . ' OR id = ' . (int) $parentId);

		$db->setQuery($query);
		$db->execute();
	}
}
