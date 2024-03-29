<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Processor\AbstractProcessor;

class PackagePsxProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_package_sellers';

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		parent::__construct($importer);

		try
		{
			$config  = ConfigHelper::getInstance('com_sellacious');
			$allowed = $config->get('allowed_product_package');

			$this->enabled = $allowed;
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	protected function getCsvColumns()
	{
		if ($this->enabled)
		{
			$columns = array();
			$user    = $this->importer->getUser();
			$userId  = $user->id;

			$allow_return   = $this->helper->config->get('purchase_return', 0);
			$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

			if ($this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $userId))
			{
				$columns[] = 'product_listing_type';
				$columns[] = 'product_condition';
				$columns[] = 'whats_in_box';

				if ($allow_return == 2)
				{
					$columns[] = 'order_return_days';
					$columns[] = 'order_return_tnc';
				}

				if ($allow_exchange == 2)
				{
					$columns[] = 'order_exchange_days';
					$columns[] = 'order_exchange_tnc';
				}
			}

			if ($this->helper->access->checkAny(array('shipping', 'shipping.own'),'product.edit.', '', 'com_sellacious', $userId))
			{
				$columns[] = 'is_flat_shipping';
				$columns[] = 'flat_shipping_fee';
				$columns[] = 'shipping_length';
				$columns[] = 'shipping_width';
				$columns[] = 'shipping_height';
				$columns[] = 'shipping_weight';
				$columns[] = 'volumetric_weight';
			}

			return $columns;
		}

		return array();
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	protected function getRequiredColumns()
	{
		return $this->enabled ? array('x__psx_id') : array();
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	protected function getGeneratedColumns()
	{
		return array();
	}

	/**
	 * Method to preprocess the import record that include filtering, typecasting, etc.
	 * No write actions should be carried out at this stage. This is meant for only preparing a CSV record for import.
	 *
	 * @param   \stdClass  $obj  The record from the import CSV
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessRecord($obj)
	{
		if ($obj->product_type !== 'package')
		{
			return;
		}

		$user   = $this->importer->getUser();
		$userId = $user->id;

		if ($this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $userId))
		{
			$booleans = array('TRUE', 'YES', '1', 'PUBLISHED', 'ACTIVE', 'ENABLED', 'T', 'Y');

			if (isset($obj->product_listing_type))
			{
				$obj->product_listing_type = array_search(strtoupper($obj->product_listing_type), array('', 'NEW', 'USED', 'REFURBISHED'));
			}

			if (isset($obj->product_condition))
			{
				$obj->product_condition = array_search(strtoupper($obj->product_condition), array('', 'LIKE NEW', 'AVERAGE', 'GOOD', 'POOR'));
			}
		}

		if ($this->helper->access->checkAny(array('shipping', 'shipping.own'),'product.edit.', '', 'com_sellacious', $userId))
		{
			if (isset($obj->is_flat_shipping))
			{
				$obj->is_flat_shipping = in_array(strtoupper($obj->is_flat_shipping), $booleans) ? 1 : 0;
			}

			$obj->shipping_length   = $obj->shipping_length ? json_encode(array('m' => (float)$obj->shipping_length)) : null;
			$obj->shipping_width    = $obj->shipping_width ? json_encode(array('m' => (float)$obj->shipping_width)) : null;
			$obj->shipping_height   = $obj->shipping_height ? json_encode(array('m' => (float)$obj->shipping_height)) : null;
			$obj->shipping_weight   = $obj->shipping_weight ? json_encode(array('m' => (float)$obj->shipping_weight)) : null;
			$obj->volumetric_weight = $obj->volumetric_weight ? json_encode(array('m' => (float)$obj->volumetric_weight)) : null;
		}
	}

	/**
	 * Method to preprocess the import records.
	 * This can be creating an index of existing records, or any other prerequisites fulfilment before import begins.
	 * No write actions should be carried out at this stage.
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessBatch()
	{
		if ($this->enabled)
		{
			$db    = $this->importer->getDb();
			$query = $db->getQuery(true);

			$query->select('id, psx_id')->from($this->tableName);
			$iterator = $db->setQuery($query)->getIterator();

			foreach ($iterator as $item)
			{
				$this->addIndex((int) $item->psx_id, (int) $item->id);
			}
		}
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if (!$this->enabled || !$obj->x__psx_id || $obj->product_type <> 'package')
		{
			return;
		}

		$user           = $this->importer->getUser();
		$userId         = $user->id;
		$allow_return   = $this->helper->config->get('purchase_return', 0);
		$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

		$record = new \stdClass;

		$record->id     = $this->getIndex($obj->x__psx_id);
		$record->psx_id = $obj->x__psx_id;

		if ($this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $userId))
		{
			$record->listing_type   = $obj->product_listing_type;
			$record->item_condition = $obj->product_condition;
			$record->whats_in_box   = $obj->whats_in_box;

			if ($allow_return == 2)
			{
				$record->return_days = $obj->order_return_days;
				$record->return_tnc  = $obj->order_return_tnc;
			}

			if ($allow_exchange == 2)
			{
				$record->exchange_days = $obj->order_exchange_days;
				$record->exchange_tnc  = $obj->order_exchange_tnc;
			}
		}

		if ($this->helper->access->checkAny(array('shipping', 'shipping.own'),'product.edit.', '', 'com_sellacious', $userId))
		{
			$record->flat_shipping     = $obj->is_flat_shipping;
			$record->shipping_flat_fee = $obj->flat_shipping_fee;
			$record->length            = $obj->shipping_length;
			$record->width             = $obj->shipping_width;
			$record->height            = $obj->shipping_height;
			$record->weight            = $obj->shipping_weight;
			$record->vol_weight        = $obj->volumetric_weight;
		}

		$db = $this->importer->getDb();

		if ($record->id)
		{
			$db->updateObject($this->tableName, $record, array('id'));
		}
		else
		{
			$db->insertObject($this->tableName, $record, 'id');

			$this->addIndex((int) $record->psx_id, (int) $record->id);
		}
	}
}
