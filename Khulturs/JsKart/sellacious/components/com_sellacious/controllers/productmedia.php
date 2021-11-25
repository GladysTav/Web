<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Product Media controller class
 *
 * @since   2.0.0
 */
class SellaciousControllerProductMedia extends SellaciousControllerForm
{
	/**
	 * @var	  string  The prefix to use with controller messages
	 *
	 * @since   2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCTMEDIA';

	/**
	 * The URL view list variable
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $view_list = 'productmedia';

	public function __construct(array $config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublishAjax', 'publishAjax');
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function allowAdd($data = array())
	{
		// @Todo: Depend on selected product

		return true;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// @Todo: Depend on selected product

		return true;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object
	 * @param   array         $validData  The validated data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$this->setRedirect('index.php?option=com_sellacious&view=productmedia&layout=close');

		parent::postSaveHook($model, $validData);
	}


	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function publishAjax()
	{
		$this->checkToken('request');

		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('publishAjax' => 1, 'unpublishAjax' => 0);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		try
		{
			if (empty($cid))
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
			}

			/** @var  \SellaciousModelProductMedia  $model */
			$model = $this->getModel();
			$cid   = ArrayHelper::toInteger($cid);

			$model->publish($cid, $value);

			$errors = $model->getErrors();

			if ($errors)
			{
				throw new Exception(JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)));
			}

			if ($value == 1)
			{
				$nText = $this->text_prefix . '_N_ITEMS_PUBLISHED';
			}
			else
			{
				$nText = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
			}

			echo json_encode(array('status' => 1, 'message' => JText::plural($nText, count($cid)), 'data' => null));
		}
		catch (Exception $e)
		{
			echo json_encode(array('status' => 0, 'message' => $e->getMessage(), 'data' => null));
		}

		jexit();
	}
}
