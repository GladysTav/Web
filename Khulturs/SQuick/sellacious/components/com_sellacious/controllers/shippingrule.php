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
use Sellacious\Media\Upload\UploadedFile;
use Sellacious\Media\Upload\Uploader;

defined('_JEXEC') or die;

/**
 * Coupon controller class.
 *
 */
class SellaciousControllerShippingRule extends SellaciousControllerForm
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_SHIPPINGRULE';

	/**
	 * Method to check if you can add a new record. Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function allowAdd($data = array())
	{
		// @12032020@ Condition check for "Shipped by" removed
		return $this->helper->access->check('shippingrule.create');
	}

	/**
	 * Method to get the slabs from csv file upload.
	 *
	 * @return  boolean
	 *
	 * @since   1.5.3
	 */
	public function loadCsvSlabsAjax()
	{
		try
		{
			if (!$this->checkToken('post', false))
			{
				throw new Exception(JText::_('JINVALID_TOKEN_NOTICE'));
			}

			$me       = JFactory::getUser();
			$uploader = new Uploader(array('csv'));
			$uploader->select('jform');

			$files         = $uploader->getSelected();
			$file          = reset($files);
			$rows          = array();
			$rateCol       = $this->input->getString('rate_column', 'shipping');
			$ruleType      = $this->input->getString('rule_type', 'shippingrule');
			$ruleId        = $this->input->getString('rule_id', 0);
			$roundToDigits = $this->input->getString('round_to_digits', 'false');
			$roundToDigits = $roundToDigits == 'false' ? false : (int) $roundToDigits;

			if ($file instanceof UploadedFile)
			{
				$rows = $this->helper->shippingRule->csvToSlabs($file->tmp_name, array('rate' => $rateCol), $roundToDigits);
			}

			// Store the parsed CSV data into a temporary JSON File
			$jsonFile = $ruleType . '_slabs_r' . $ruleId . 'u' . $me->id . '.txt';
			$this->helper->filestorage->storeToFile($rows, $jsonFile);

			$response = array(
				'state'   => 1,
				'message' => null,
				'data'    => array('data' => $jsonFile),
			);
		}
		catch (Exception $e)
		{
			$response = array('state' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		echo json_encode($response);

		jexit();

		return true;
	}

	/**
	 * Method to check if you can edit an existing record.
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$me           = JFactory::getUser();
		$shippingRule = $this->helper->shippingRule->getItem($data[$key]);

		return $this->helper->access->check('shippingrule.edit') ||
			($this->helper->access->check('shippingrule.edit.own') && ($me->id == $shippingRule->owned_by || $me->id == $shippingRule->created_by));
	}
}
