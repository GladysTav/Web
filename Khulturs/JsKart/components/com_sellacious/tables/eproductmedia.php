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
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Product Media Table class
 *
 * @property   int     $id
 * @property   string  $product_id
 * @property   string  $variant_id
 * @property   string  $seller_uid
 * @property   string  $media_url
 * @property   string  $sample_url
 * @property   string  $media_type
 * @property   string  $sample_type
 * @property   string  $files_group
 * @property   string  $version
 * @property   string  $released
 * @property   bool    $is_latest
 * @property   bool    $hotlink
 * @property   bool    $state
 * @property   string  $tags
 * @property   string  $notes
 * @property   string  $created
 * @property   int     $created_by
 * @property   string  $modified
 * @property   int     $modified_by
 * @property   string  $params
 *
 * @since   1.2.0
 */
class SellaciousTableEProductMedia extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_eproduct_media', 'id', $db);
	}

	/**
	 * Assess that the nested set data is valid.
	 *
	 * @return  bool  True if the instance is sane and able to be stored in the database.
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		$ver = $this->helper->config->get('productmedia_versions');

		$this->files_group = JFilterOutput::stringURLSafe($this->files_group);
		$this->files_group = trim($this->files_group, ' -');

		if ($ver)
		{
			// Treat as file-set
			if ($this->files_group === '')
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_ALERT_INVALID_FILES_GROUP'));
			}

			$table = static::getInstance($this->getName());
			$k     = $this->_tbl_key;

			$keys = array(
				'product_id'  => $this->product_id,
				'variant_id'  => $this->variant_id,
				'seller_uid'  => $this->seller_uid,
				'files_group' => $this->files_group,
				'version'     => $this->version,
			);

			if ($table->load($keys) && ($this->$k == 0 || $table->$k != $this->$k))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_MEDIA_ALERT_DUPLICATE_FILE_VERSION', $this->files_group, $this->version));
			}
		}
		else
		{
			// Treat as alias
			if ($this->files_group === '')
			{
				$this->files_group = 'files';
			}

			$table = static::getInstance($this->getName());
			$k     = $this->_tbl_key;

			$keys = array(
				'product_id'  => $this->product_id,
				'variant_id'  => $this->variant_id,
				'seller_uid'  => $this->seller_uid,
				'files_group' => $this->files_group,
			);

			while ($table->load($keys) && ($this->$k == 0 || $table->$k != $this->$k))
			{
				$this->files_group   = StringHelper::increment($this->files_group, 'dash');
				$keys['files_group'] = $this->files_group;
			}
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   bool  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   2.0.0
	 */
	public function store($updateNulls = false)
	{
		$saved = parent::store($updateNulls);

		if ($saved && $this->is_latest)
		{
			$query = $this->_db->getQuery(true);

			$query->update($this->getTableName())->set('is_latest = 0');

			$query->where('files_group = ' . $this->_db->q($this->files_group))
				  ->where('product_id = ' . (int) $this->product_id)
				  ->where('variant_id = ' . (int) $this->variant_id)
				  ->where('seller_uid = ' . (int) $this->seller_uid);

			$query->where('id != ' . (int) $this->id);

			$this->_db->setQuery($query)->execute();
		}

		return $saved;
	}
}
