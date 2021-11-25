<?php
/**
* @version     2.0.0
* @package     sellacious
*
* @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
* @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
* @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
*/
namespace Sellacious\Communication\Queue;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JFactory;
use JLog;
use JTable;
use JText;
use Sellacious\Communication\CommunicationHelper;
use SellaciousTableMailQueue;

class MessageQueue
{
	/**
	 * The lock token
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $token;

	/**
	 * The locked items primary key values
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $pks = array();

	/**
	 * The message footers
	 *
	 * @var  string[]
	 *
	 * @since   2.0.0
	 */
	protected $footers;

	/**
	 * Method to lock the communication queue in a batch
	 *
	 * @param   int  $limit
	 *
	 * @return  string  The lock token if locked, null if lock failed
	 *
	 * @since   2.0.0
	 */
	public function lock($limit = 10)
	{
		JTable::getInstance('MailQueue', 'SellaciousTable');

		$db    = JFactory::getDbo();
		$now   = JFactory::getDate()->toUnix();
		$query = $db->getQuery(true);
		$token = uniqid();

		// Attempt lock using atomic query to check and update atomically
		$query->update('#__sellacious_mailqueue')
			->set('lock_token = ' . $db->q($token))
			->set('lock_time = ' . $db->q($now))
			->where('state = ' . (int) SellaciousTableMailQueue::STATE_QUEUED);

		// Lock only if unlocked or lock expired (120 sec)
		$query->where('(lock_token = ' . $db->q('') . ' OR lock_time + 120 < ' . $db->q($now) . ')');

		$db->setQuery($query, 0, $limit)->execute();

		// Check whether the lock was acquired
		$locked = $db->getAffectedRows();

		$this->token = $locked ? $token : null;

		return $this->token;
	}

	/**
	 * Method to send the messages locked with this instance
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function send()
	{
		if (!$this->token)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COMMUNICATION_QUEUE_BATCH_NOT_LOCKED'));
		}

		JTable::getInstance('MailQueue', 'SellaciousTable');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get records locked by current thread
		$query->select('id, type')
			->from('#__sellacious_mailqueue')
			->where('state = ' . (int) SellaciousTableMailQueue::STATE_QUEUED)
			->where('lock_token =' . $db->quote($this->token));

		$iterator = $db->setQuery($query)->getIterator();

		if ($iterator->count())
		{
			foreach ($iterator as $item)
			{
				try
				{
					$message = CommunicationHelper::getHandler($item->type);
					$footer  = $this->getFooter($item->type);

					if ($footer)
					{
						$message->setBody($message->get('body') . $footer);
					}

					$message->loadFromQueue($item->id);

					$message->send(5, true);
				}
				catch (Exception $e)
				{
					JLog::add('Message failed: ' . $e->getMessage(), JLog::INFO);
				}
			}
		}
	}

	/**
	 * Method to set additional footer for messages in the queue based on the message handler
	 *
	 * @param   string  $type
	 * @param   string  $content
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function setFooter($type, $content)
	{
		$this->footers[$type] = $content;
	}

	/**
	 * Method to get additional footer for messages in the queue based on the message handler
	 *
	 * @param   string  $type
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getFooter($type)
	{
		if (isset($this->footers[$type]))
		{
			return $this->footers[$type];
		}
		elseif (isset($this->footers['default']))
		{
			return $this->footers['default'];
		}

		return null;
	}
}
