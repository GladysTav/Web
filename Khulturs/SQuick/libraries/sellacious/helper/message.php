<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Message\AbstractReference;

/**
 * Sellacious message helper.
 *
 * @since  1.0
 */
class SellaciousHelperMessage extends SellaciousHelperBase
{
	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  stdClass[]
	 */
	public function getRecipientGroups()
	{
		$db     = $this->db;
		$groups = array();
		$types  = array('client', 'seller', 'staff', 'manufacturer');
		$filter = array(
			'list.select' => 'a.id, a.title, a.type',
			'list.order'  => 'a.type ASC, a.title ASC',
		);

		$item           = new stdClass();
		$item->id       = 'cat:all';
		$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_ALL');
		$item->bulk     = true;
		$item->optgroup = true;
		$groups[]       = $item;

		foreach ($types as $type)
		{
			$filter['list.where'] = array('a.state = 1', 'a.type = ' . $db->q($type));

			$cats = $this->helper->category->loadObjectList($filter);

			if (count($cats))
			{
				$item           = new stdClass();
				$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_' . strtoupper($type));
				$item->bulk     = true;
				$item->optgroup = true;
				$item->disabled = true;
				$groups[]       = $item;

				$item           = new stdClass();
				$item->id       = 'cat:' . $type;
				$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_ALL_' . strtoupper($type));
				$item->bulk     = true;
				$item->optgroup = true;
				$groups[]       = $item;

				foreach ($cats as $cat)
				{
					$item       = new stdClass();
					$item->id   = 'cat:' . $cat->id;
					$item->text = $cat->title;
					$item->bulk = true;
					$groups[]   = $item;
				}
			}
		}

		return $groups;
	}

	/**
	 * Get the user id of all users that fall into the given group-id.
	 *
	 * @param   string $group Group identifier as defined in the function above
	 *
	 * @return  int[]
	 * @see     getRecipientGroups()
	 */
	public function getRecipientsByGroup($group)
	{
		$pks = array();

		switch (true)
		{
			case $group == 'cat:all':
				$pks = $this->helper->profile->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:seller':
				$pks = $this->helper->seller->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:client':
				$pks = $this->helper->client->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:staff':
				$pks = $this->helper->staff->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:manufacturer':
				$pks = $this->helper->manufacturer->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case is_numeric($cid = substr($group, 4)):
				$pka[] = $this->helper->seller->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->client->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->staff->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->manufacturer->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pks   = array_reduce($pka, 'array_merge', array());
				break;

			default:
				// ignore
		}

		return $pks;
	}

	/**
	 * Get the entire thread for any selected message.
	 *
	 * @param  int|object  $item     The message object or the entire record from the db
	 * @param  array       $options  Array of options
	 *
	 * @return  stdClass[]
	 */
	public function getThread($item, $options = array())
	{
		if (is_numeric($item))
		{
			$item = $this->getItem($item);
		}
		
		$order       = ArrayHelper::getValue($options, 'order', 'DESC');
		$startId     = ArrayHelper::getValue($options, 'start_id', 0);
		$startSign   = ArrayHelper::getValue($options, 'start_sign', '>');
		$limit       = ArrayHelper::getValue($options, 'limit', 0);
		$secondOrder = ArrayHelper::getValue($options, 'second_order', false);

		$db    = $this->db;
		$query = $db->getQuery(true);

		$where = array(
			'(a.lft <= ' . $db->q($item->lft) . ' AND ' . $db->q($item->rgt) . ' <= a.rgt)',
			'(a.lft >= ' . $db->q($item->lft) . ' AND ' . $db->q($item->rgt) . ' >= a.rgt)',
		);

		$query->select('a.*')
			->from($db->qn('#__sellacious_messages', 'a'))
			->where('(' . implode(' OR ', $where) . ')')
			->where('a.level > 0')
			->order('a.date_sent ' . $order);
		
		// If records need to be fetched before or after a message id
		if ($startId > 0 && $startSign != '')
		{
			$query->where('a.id ' . $startSign . ' ' . (int) $startId);
		}

		try
		{
			if ($limit > 0)
			{
				// If recordset needs to be ordered again (to be used with limit)
				if ($secondOrder)
				{
					$parentOrder = $order == 'ASC' ? 'DESC' : 'ASC';
					$parentQuery = $db->getQuery(true);
					$parentQuery->select('a.*')
						->from('(' . $query->__toString() . ' LIMIT ' . $limit . ') AS a')
						->order('a.date_sent ' . $parentOrder);
					
					$db->setQuery($parentQuery);
				}
				else
				{
					$db->setQuery($query, 0, $limit);
				}
			}
			else
			{
				$db->setQuery($query);
			}
			
			$items = $db->loadObjectList();

			if (is_array($items))
			{
				// Prepare the content before rendering
				$dispatcher = $this->helper->core->loadPlugins();
				$params     = array('link' => true);

				foreach ($items as $item)
				{
					$item->text = $item->body;
					$dispatcher->trigger('onContentPrepare', array('com_sellacious.message', &$item, &$params));
					$item->body = $item->text;
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $items;
	}

	/**
	 * Get the last message of a thread.
	 *
	 * @param   int  $threadId Id of thread
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public function getLastMessageByThread($threadId)
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from('#__sellacious_messages')
			->where('parent_id = ' . $threadId)
			->order('id DESC');

		try
		{
			$result = $this->db->setQuery($query, 0, 1)->loadAssoc();

			if (!$result) {
				$nQuery = $this->db->getQuery(true);
				$nQuery->select('*')
					->from('#__sellacious_messages')
					->where('id = ' . $threadId)
					->order('id DESC');
				
				$result = $this->db->setQuery($nQuery, 0, 1)->loadAssoc();
			}

			$result['created'] = $this->helper->core->relativeDateTime($result['created']);
		}
		catch (Exception $e)
		{
			$result = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		if (isset($result['parent_id']) && $result['parent_id'] == '0') return array();

		return $result;
	}

	/**
	 * Get the recipients list for the selected message. Only applicable for broadcast messages.
	 *
	 * @param   int  $msg_id  Message id for the query
	 *
	 * @return  int[]
	 *
	 * @since   1.2.0
	 */
	public function getRecipients($msg_id)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('recipient')
			->from('#__sellacious_message_recipients')
			->where('message_id = ' . (int) $msg_id);

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$result = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $result;
	}

	/**
	 * Get unread messages count for a particular user
	 *
	 * @param   int  $userId Id of the user whose unread count is requested for
	 *
	 * @return  int
	 *
	 * @since   1.2.0
	 */
	public function getUnreadCount($userId)
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__sellacious_messages')
			->where('recipient = ' . (int) $userId)
			->where('is_read = 0')
			->where('state = 1');

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$result = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return count($result);
	}

	/**
	 * Get unread messages count for a particular thread
	 *
	 * @param   int  $threadId   Id of the thread whose unread count is requested for
	 * @param   int  $recipient  Id of the recipient whose unread count is requested for
	 *
	 * @return  int
	 *
	 * @since   1.2.0
	 */
	public function getUnreadCountByThread($threadId, $recipient = null)
	{
		$me = JFactory::getUser();
		$db = $this->db;
		$query = $db->getQuery(true);

		$recipient = $recipient ?: (int) $me->id;

		$query->select('id')
			->from('#__sellacious_messages')
			->where('parent_id = ' . (int) $threadId)
			->where('is_read = 0')
			->where('state = 1')
			->where('recipient = ' . $recipient);

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$result = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return count($result);
	}

	/**
	 * Method to set unread messages in a thread as read
	 *
	 * @param   int  $messageId  The message id
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	public function markThreadAsRead($messageId)
	{
		$messageId = (int) $messageId;
		$me        = JFactory::getUser();
		$date      = JFactory::getDate();
		$db        = $this->db;
		$query     = $db->getQuery(true);

		$query->update($db->qn('#__sellacious_messages'))
			->set(array('is_read = 1', 'modified = ' . $db->q($date->toSql()), 'modified_by = ' . $me->get('id', 0)))
			->where('(parent_id = ' . $messageId . ' OR id = ' . $messageId . ')')
			->where('recipient = ' . $me->id)
			->where('is_read = 0');

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to save a reference message (System message)
	 *
	 * @param   array   $messageData  The message data
	 * @param   string  $refContext   Context of the reference
	 * @param   mixed   $refValue     Value of the reference
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function saveMessageReference($messageData, $refContext, $refValue)
	{
		$date     = JFactory::getDate();
		$app      = JFactory::getApplication();
		$parentId = ArrayHelper::getValue($messageData, 'parent_id', 1);
		$parentId = $parentId == 0 ? 1 : $parentId;

		$reference = $this->getReferenceAsText($refContext, $refValue);

		/** @var SellaciousTableMessage $table */
		$table = JTable::getInstance('Message', 'SellaciousTable');

		if ($reference)
		{
			if (empty($messageData['title']))
			{
				$messageData['title'] = substr($messageData['body'], 0, 15);
			}

			$messageData['body']      = $reference;
			$messageData['parent_id'] = $parentId;

			$array = array(
				'context'   => 'system_message',
				'date_sent' => $date->toSql(),
				'state'     => 1,
				'remote_ip' => $app->input->server->getString('REMOTE_ADDR'),
			);

			$messageData = array_merge($messageData, $array);

			$table->bind($messageData);

			$table->setLocation($table->parent_id, 'last-child');

			$table->check();
			$table->store();
		}

		return $table->get('id');
	}

	/**
	 * Method to get the last system message for sender+recipient pair
	 *
	 * @param   int  $sender     User id of the sender
	 * @param   int  $recipient  User id of the recipient
	 *
	 * @return  object
	 *
	 * @since   2.0.0
	 */
	public function getLastSystemMessage($sender, $recipient)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_messages' ,'a'))
			->where('a.sender = ' . (int) $sender)
			->where('a.recipient = ' . (int) $recipient)
			->where('a.context = ' . $db->q('system_message'))
			->order('a.created DESC');

		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * Method to get reference value as text depending on the context
	 *
	 * @param   string  $context  Context of the reference
	 * @param   mixed   $value    Value of the reference
	 *
	 * @return  bool|string
	 *
	 * @since   2.0.0
	 */
	public function getReferenceAsText($context, $value)
	{
		if (!$context || !$value)
		{
			return false;
		}

		$class = 'Sellacious\Message\\' . ucfirst($context) . 'Reference';

		/** @var AbstractReference $obj */
		$obj  = new $class($value);

		return $obj->asText();
	}
}
