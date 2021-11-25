<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Sellacious User Favorite helper.
 *
 * @since   2.0.0
 */
class SellaciousHelperUserFavorite extends SellaciousHelperBase
{
	/**
	 * Add an item to selected user's favorites
	 *
	 * @param   int     $record_id
	 * @param   string  $context
	 * @param   int     $author_id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function addItem($record_id, $context = 'store', $author_id = null)
	{
		$table   = $this->getTable();
		$user    = JFactory::getUser($author_id);
		$values  = array('record_id' => $record_id, 'context' => $context, 'author_id' => $user->id);
		
		$table->load($values);
		$table->bind($values);
		$table->check();
		
		if (!$table->store())
		{
			throw new Exception(JText::_('COM_SELLACIOUS_USER_FAVORITE_ADD_FAILED'));
		}
		
		return true;
	}
	
	/**
	 * Remove an item from favorites
	 *
	 * @param   int     $record_id
	 * @param   string  $context
	 * @param   int     $author_id
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function removeItem($record_id, $context = 'store', $author_id = null)
	{
		$table   = $this->getTable();
		$user    = JFactory::getUser($author_id);
		$user_id = $user->id;
		
		$values = array('record_id' => $record_id, 'context' => $context, 'author_id' => $user_id);
		$table->load($values);
		
		if ($table->get('id'))
		{
			$table->delete();
		}
		
		return true;
	}
	
	/**
	 * Check an item if it exists in selected user's favorites list
	 *
	 * @param   int     $store_id
	 * @param   string  $context
	 * @param   int     $user_id
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function check($record_id, $context = 'store', $user_id = null)
	{
		$user    = JFactory::getUser($user_id);
		$user_id = $user->id;
		
		if ($user->guest)
		{
			return false;
		}
		
		$values = array('context' => $context, 'record_id' => $record_id, 'author_id' => $user_id);
		
		return $this->count($values);
	}
}