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
use Sellacious\Media\File\MediaFile;

defined('_JEXEC') or die;

/**
 * list controller class
 *
 * @since  1.2.0
 */
class SellaciousControllerDownload extends SellaciousControllerBase
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_DOWNLOAD';

	/**
	 * Verify only the file existence for the file to be sent as download
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 *
	 * @see  download()
	 */
	public function check()
	{
		$fileId     = $this->input->getInt('id');
		$deliveryId = $this->input->getInt('delivery_id');

		try
		{
			// Exception is thrown internally
			$delivery = $this->helper->order->checkEProductDelivery($deliveryId, $fileId);

			if (!is_object($delivery) || !is_array($delivery->files) || !in_array($fileId, $delivery->files))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$item = $this->helper->media->getItem($fileId);

			if (!$item->id)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$file = new MediaFile($item->path, $item->id);
			$url  = $file->getCloudUrl();

			if (!$url && !is_file($file->getPath(true)))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$data = array(
				'data'    => array('link' => $url),
				'message' => JText::_('COM_SELLACIOUS_FILE_FOUND_REDIRECTING'),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$data = array(
				'data'    => array('link' => null),
				'message' => $e->getMessage(),
				'status'  => 0,
			);
		}

		echo json_encode($data);

		$this->app->close();
	}

	/**
	 * Initiate the actual file download
	 *
	 * @return  void
	 *
	 * @since  1.3.5
	 */
	public function download()
	{
		$me         = JFactory::getUser();
		$fileId     = $this->input->getInt('id');
		$deliveryId = $this->input->getInt('delivery_id');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=downloads'));

		try
		{
			// Exception is thrown internally
			$delivery = $this->helper->order->checkEProductDelivery($deliveryId, $fileId);

			if (!is_object($delivery) || !is_array($delivery->files) || !in_array($fileId, $delivery->files))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$item = $this->helper->media->getItem($fileId);

			if (!$item->id)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$file = new MediaFile($item->path, $item->id);
			$url  = $file->getCloudUrl();

			if ($url)
			{
				$this->helper->media->addDownloadEntry($item, $me->id, $delivery->id, null);

				$this->app->redirect($url);

				$this->app->close();
			}

			if (!is_file($file->getPath(true)))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			$this->helper->media->addDownloadEntry($item, $me->id, $delivery->id, null);

			$this->helper->media->downloadFile($item->path, $item->original_name, $item->type);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}
	}
}
