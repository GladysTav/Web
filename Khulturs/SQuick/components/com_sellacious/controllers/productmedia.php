<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Media\File\MediaFile;

defined('_JEXEC') or die;

/**
 * Product media controller class.
 *
 * @since   2.0.0
 */
class SellaciousControllerProductMedia extends SellaciousControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCTMEDIA';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @see     JControllerForm
	 *
	 * @since   2.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('sample', 'download');
	}

	/**
	 * Method to download an eproduct file using its hotlink, if enabled
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function download()
	{
		$code  = $this->input->getString('p');
		$group = $this->input->getString('file');
		$ver   = $this->input->getString('version');

		$this->setRedirect($this->getRedirectURL());

		try
		{
			$media = $this->getMedia($code, $group, $ver);

			if (!$media)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_MEDIA_NOT_FOUND'));
			}

			$context = $this->getTask() === 'sample' ? 'sample' : 'media';

			// Allow sample hotlink always
			if ((int) $media->hotlink !== 1 && $context !== 'sample')
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_HOTLINK_DISABLED'));
			}

			$mType = $context === 'sample' ? $media->sample_type : $media->media_type;

			if ($mType === 'link')
			{
				$this->downloadLink($media, $context);
			}
			else
			{
				$this->downloadFile($media, $context);
			}

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			return false;
		}
	}

	/**
	 * Method to add an eproduct media (url) to a given product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addAjax()
	{
		ob_start();

		try
		{
			$productId = $this->app->input->getInt('product_id');
			$variantId = $this->app->input->getInt('variant_id');
			$sellerUid = $this->app->input->getInt('seller_uid');
			$mediaUrl  = $this->app->input->getString('media_url');
			$version   = $this->app->input->getString('version');
			$released  = $this->app->input->getString('released');
			$hotlink   = $this->app->input->getInt('hotlink');
			$isLatest  = $this->app->input->getInt('is_latest');
			$notes     = $this->app->input->getInt('notes');

			if (!$productId || !$sellerUid || !$mediaUrl)
			{
				throw new Exception('Insufficient parameter. Required - product_id, seller_uid, media_url');
			}

			$table = SellaciousTable::getInstance('EProductMedia');

			$table->set('product_id', $productId);
			$table->set('variant_id', $variantId);
			$table->set('seller_uid', $sellerUid);
			$table->set('media_url', $mediaUrl);
			$table->set('version', $version);
			$table->set('released', $released);
			$table->set('hotlink', $hotlink);
			$table->set('is_latest', $isLatest);
			$table->set('notes', $notes);
			$table->set('state', 1);

			if (!$table->check() || !$table->store())
			{
				throw new Exception($table->getError());
			}

			echo new JResponseJson($table->getProperties());
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		ob_get_clean();

		jexit();
	}

	/**
	 * Method to handle download productmedia attached using a URL
	 *
	 * @param   stdClass  $media
	 * @param   string    $context
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function downloadLink($media, $context = 'media')
	{
		$link = $context === 'sample' ? $media->sample_url : $media->media_url;

		if (!$link)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LINK_NOT_FOUND'));
		}

		$me = JFactory::getUser();
		$f  = (object) array('id' => 0, 'original_name' => 'External Link', 'context' => $context);

		$this->helper->media->addDownloadEntry($f, $me->id, - 1, $media->id);

		$this->app->redirect($link);

		$this->app->close();
	}

	/**
	 * Method to handle download productmedia attached using a file upload
	 *
	 * @param   stdClass  $media
	 * @param   string    $context
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function downloadFile($media, $context = 'media')
	{
		$filter = array(
			'table_name' => 'eproduct_media',
			'record_id'  => $media->id,
			'context'    => $context,
		);
		$item   = $this->helper->media->loadObject($filter);

		if (!$item)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_FILE_NOT_FOUND'));
		}

		$me   = JFactory::getUser();
		$file = new MediaFile($item->path, $item->id);
		$url  = $file->getCloudUrl();

		if ($url)
		{
			$this->helper->media->addDownloadEntry($item, $me->id, - 1, $media->id);

			$this->app->redirect($url);

			$this->app->close();
		}

		// Check file existence only after cloud url match fails
		if (!is_file($file->getPath(true)))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_FILE_NOT_FOUND'));
		}

		$this->helper->media->addDownloadEntry($item, $me->id, - 1, $media->id);

		$this->helper->media->downloadFile($item->path, $item->original_name, $item->type);

		$this->app->close();
	}

	/**
	 * Get the requested media for download processing
	 *
	 * @param   string  $code
	 * @param   string  $group
	 * @param   string  $ver
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getMedia($code, $group, $ver = null)
	{
		if (!$this->helper->product->parseCode($code, $productId, $variantId, $sellerUid))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LINK_INVALID'));
		}

		$filter = array(
			'list.from'   => '#__sellacious_eproduct_media',
			'product_id'  => $productId,
			'variant_id'  => $variantId,
			'seller_uid'  => $sellerUid,
			'files_group' => $group,
			'state'       => 1,
		);

		if ($this->helper->config->get('productmedia_versions'))
		{
			if ($ver === 'latest')
			{
				$filter['is_latest'] = 1;
			}
			elseif (strlen($ver))
			{
				$filter['version'] = $ver;
			}
		}

		return $this->helper->product->loadObject($filter);
	}
}
