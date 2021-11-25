<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\Upload\UploadedFile;
use Sellacious\Media\Upload\Uploader;

/**
 * Sellacious model.
 *
 * @since   2.0.0
 */
class SellaciousModelProductMedia extends SellaciousModelAdmin
{
	protected function populateState()
	{
		parent::populateState();

		$pCode = $this->app->input->getCmd('p');
		$valid = $this->helper->product->parseCode($pCode, $productId, $variantId, $sellerUid);
		$id    = $this->app->getUserStateFromRequest('com_sellacious.edit.productmedia.id', 'id', 0, 'int');

		if ($valid)
		{
			$this->state->set('productmedia.id', $id);
			$this->state->set('productmedia.code', $pCode);
			$this->state->set('productmedia.product_id', $productId);
			$this->state->set('productmedia.variant_id', $variantId);
			$this->state->set('productmedia.seller_uid', $sellerUid);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   2.0.0
	 */
	protected function canDelete($record)
	{
		// @Todo: Depend on selected product

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  bool  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   2.0.0
	 */
	protected function canEditState($record)
	{
		// @Todo: Depend on selected product

		return true;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getTable($type = 'EProductMedia', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  The form data
	 *
	 * @return  bool  True on success
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function save($data)
	{
		$dispatcher = $this->helper->core->loadPlugins('sellacious');

		$isNew = true;
		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState('productmedia.id');

		if ($pk > 0)
		{
			$table->load($pk);

			$isNew = false;
		}

		if (is_array($data['tags']))
		{
			$data['tags'] = implode(',', $data['tags']);
		}

		$extensions = $this->helper->config->get('productmedia_extensions');
		$extensions = explode(',', $extensions);
		$opt        = Uploader::UNSAFE_OPT_ALLOW_PHP_TAG_IN_CONTENT |
		              Uploader::UNSAFE_OPT_ALLOW_SHORT_TAG_IN_CONTENT |
		              Uploader::UNSAFE_OPT_ALLOW_FORBIDDEN_EXT_IN_CONTENT;

		// Preprocess Media
		$media    = ArrayHelper::getValue($data, 'media', array(), 'array');
		$uploader = new Uploader($extensions);
		$medias   = $uploader->allowUnsafe($opt)->select('jform.media');

		// Preprocess Sample
		$sample   = ArrayHelper::getValue($data, 'sample', array(), 'array');
		$uploader = new Uploader($extensions);
		$samples  = $uploader->allowUnsafe($opt)->select('jform.sample', 1);

		unset($data['media'], $data['sample']);

		$data['product_id'] = $this->getState('productmedia.product_id');
		$data['variant_id'] = $this->getState('productmedia.variant_id');
		$data['seller_uid'] = $this->getState('productmedia.seller_uid');

		$table->bind($data);

		$table->check();

		$dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		$table->store();

		$this->setState('productmedia.id', $table->get('id'));

		try
		{
			// Save Media
			$this->handleUploader($medias, 'eproduct_media', 'media', $table->get('id'), $media);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		try
		{
			// Save Sample
			$this->handleUploader($samples, 'eproduct_media', 'sample', $table->get('id'), $sample);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		// Mark related media as protected to prevent direct downloads
		$this->helper->media->protect('eproduct_media', $pk, true);

		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		return true;
	}

	/**
	 * Handle the upload for the record when the response is from the Uploader form field
	 *
	 * @param   UploadedFile[]  $files      The form control
	 * @param   string          $tableName  The table name for media reference
	 * @param   string          $context    The field context for media reference
	 * @param   int             $recordId   The record id for media reference
	 * @param   array           $options    The remove/rename options as an object array / 2d array
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 *
	 * @see     SellaciousHelperMedia::handleUploader()
	 */
	public function handleUploader($files, $tableName, $context, $recordId, $options)
	{
		if (is_array($options) || is_object($options))
		{
			foreach ($options as $idx => $image)
			{
				$image = (object) $image;

				if (isset($image->id) && isset($image->remove))
				{
					// Todo: Match reference
					$this->helper->media->remove($image->id);
				}
				elseif (isset($image->title) && trim($image->title) !== '')
				{
					if (isset($image->id))
					{
						// Todo: Match reference, and preserve file extension
						$mo = (object) array('id' => $image->id, 'original_name' => $image->title);

						$this->_db->updateObject('#__sellacious_media', $mo, array('id'));
					}
					elseif (isset($files[$idx . '.file']))
					{
						$files[$idx . '.file']->name = $image->title;
					}
				}
			}
		}

		$pathName = strpos($tableName, '/') === false ? 'com_sellacious/' . $tableName : $tableName;

		foreach ($files as $index => $file)
		{
			$file->moveTo('images/' . $pathName . '/' . $context . '/' . $recordId, '@@-*', true);
			$file->saveTo($tableName, $context, $recordId);

			// Remove local file if on cloud?
			$keep = $this->helper->config->get('keep_cloud_synced_local_media');

			if (!$keep && $file->uploaded && $file->id)
			{
				$f = array('list.select' => 'a.on_cloud', 'id' => $file->id);

				if ($this->helper->media->loadResult($f))
				{
					JFile::delete($file->location);
				}
			}
		}

		return true;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception  If there is an error in the form event.
	 *
	 * @see     JFormField
	 *
	 * @since   2.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_object($data) ? $data : ArrayHelper::toObject($data);

		$extensions = $this->helper->config->get('productmedia_extensions');
		$version    = $this->helper->config->get('productmedia_versions');

		$form->setFieldAttribute('media', 'recordId', $obj->id);
		$form->setFieldAttribute('sample', 'recordId', $obj->id);

		$form->setFieldAttribute('media', 'extensions', $extensions);
		$form->setFieldAttribute('sample', 'extensions', $extensions);

		if ($version)
		{
			$form->setFieldAttribute('files_group', 'required', 'true');
		}
		else
		{
			// Use as unique alias
			$form->setFieldAttribute('files_group', 'label', 'COM_SELLACIOUS_PRODUCT_MEDIA_FIELD_ALIAS_LABEL');
			$form->setFieldAttribute('files_group', 'description', 'COM_SELLACIOUS_PRODUCT_MEDIA_FIELD_ALIAS_DESC');
			$form->setFieldAttribute('files_group', 'required', 'false');

			$form->removeField('version');
			$form->removeField('is_latest');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Get media files for e-products
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function getItems()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$where = array(
			'a.product_id = ' . (int) $this->getState('productmedia.product_id'),
			'a.variant_id = ' . (int) $this->getState('productmedia.variant_id'),
			'a.seller_uid = ' . (int) $this->getState('productmedia.seller_uid'),
		);

		$query->select('a.*')->from($db->qn('#__sellacious_eproduct_media', 'a'))->where($where);

		$query->order('a.files_group ASC, a.is_latest DESC, a.released DESC');

		$db->setQuery($query);

		try
		{
			$helper = SellaciousHelper::getInstance();
			$items  = $db->loadObjectList();

			if ($items)
			{
				$filter = array(
					'list.select' => 'a.id, a.path, a.state, a.original_name',
					'table_name'  => 'eproduct_media',
					'context'     => null,
					'record_id'   => null,
					'list.group'  => 'a.id',
				);

				foreach ($items as &$item)
				{
					$filter['record_id'] = $item->id;

					$filter['context'] = 'media';
					$item->media       = $helper->media->loadObject($filter);

					$filter['context'] = 'sample';
					$item->sample      = $helper->media->loadObject($filter);
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();

			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return $items;
	}
}
