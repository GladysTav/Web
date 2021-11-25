<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * list controller class
 *
 * @since  1.5.3
 */
class SellaciousControllerStores extends SellaciousControllerBase
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_STORES';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   12.2
	 */
	public function getModel($name = 'Store', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
	
	/**
	 * Function to add a store to user favorites via Ajax
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function favoriteStoreAjax()
	{
		$sellerUid = $this->input->getInt('seller_uid', 0);
		$valid     = $this->helper->seller->getItem(array('user_id' => $sellerUid));
		
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}
			
			if (!$valid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_USER_FAVORITE_ADD_INVALID_STORE'));
			}
			
			$this->helper->userfavorite->addItem($sellerUid, 'store', null);
			
			echo new JResponseJson(array(
				'seller_uid'     => $sellerUid,
				'url' => JRoute::_('index.php?option=com_sellacious&view=favoritestores', false),
			), JText::_('COM_SELLACIOUS_USER_FAVORITE_ADD_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
		
		jexit();
	}
	
	/**
	 * Function to remove an item from favorite stores list via ajax call
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function removeFavoriteStoreAjax()
	{
		$db        = JFactory::getDbo();
		$me        = JFactory::getUser();
		$sellerUid = $this->input->getInt('seller_uid', 0);
		
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}
			
			$valid = $this->helper->seller->getItem(array('user_id' => $sellerUid));
			
			if (!$valid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_USER_FAVORITE_ADD_INVALID_STORE'));
			}
			
			$removed = $this->helper->userfavorite->removeItem($sellerUid, 'store', null);
			
			if (!$removed)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_USER_FAVORITE_REMOVE_STORE_FAILED'));
			}
			
			$total = $this->helper->userfavorite->count(
				array(
					'list.where' => array(
						'a.context = ' . $db->q('store'),
						'a.author_id = ' . $db->q($me->id),
					),
					'list.join' => array(
						array('inner', '#__sellacious_sellers b ON b.user_id = a.record_id')
					)
				)
			);
			
			$totalText = '(' . $total . ' ' . JText::_('COM_SELLACIOUS_USER_FAVORITE_STORE_COUNT') . ')';
			
			echo new JResponseJson(array('seller_uid' => $sellerUid, 'total' => $totalText), JText::_('COM_SELLACIOUS_USER_FAVORITE_REMOVE_STORE_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
		
		jexit();
	}
}
