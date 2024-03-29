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

use Joomla\Utilities\ArrayHelper;
use Sellacious\Import\Processor\AbstractProcessor;
use Sellacious\Price\PriceHelper;

class PsxProcessor extends AbstractProcessor
{
	protected $tableName = '#__sellacious_product_sellers';

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
		$columns = array();
		$user    = $this->importer->getUser();

		if ($this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $user->id))
		{
			$columns = array(
				'seller_sku',
				'pricing_type',
				'min_order_qty',
				'max_order_qty',
				'product_current_stock',
				'product_over_stock_sale_limit',
				'product_reserved_stock',
				'product_stock_sold',
			);
		}

		return $columns;
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
		return array(
			'x__product_id',
			'x__seller_uid',
		);
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
		return array(
			'x__psx_id',
		);
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
		$user = $this->importer->getUser();

		if (!$this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $user->id))
		{
			return;
		}

		$this->processPricingType($obj->pricing_type);

		$key   = $obj->pricing_type;
		$price = isset($obj->price_amount_flat) ? $obj->price_amount_flat : 0.00;

		if (!array_key_exists($key, PriceHelper::getHandlers()))
		{
			$obj->pricing_type = 'hidden';

			if (empty($key) & $price !== '')
			{
				if ($price >= 0.01)
				{
					$obj->pricing_type = 'dynamic';
				}
				else
				{
					$obj->pricing_type = 'free';
				}
			}
		}

		if (!$obj->product_current_stock)
		{
			$obj->product_current_stock = null;
		}

		if (!$obj->product_over_stock_sale_limit)
		{
			$obj->product_over_stock_sale_limit = null;
		}

		if (!$obj->product_reserved_stock)
		{
			$obj->product_reserved_stock = null;
		}

		if (!$obj->product_stock_sold)
		{
			$obj->product_stock_sold = null;
		}
	}

	/**
	 * Method to process pricing type
	 *
	 * @param   string  $pricingType  The pricing type
	 *
	 * @since   2.0.0
	 */
	protected function processPricingType(&$pricingType)
	{
		$pricingType = preg_replace('/\s+/', '', $pricingType);
		$pricingType = strtolower($pricingType);

		$handlers = PriceHelper::getHandlers();

		if (!array_key_exists($pricingType, $handlers))
		{
			foreach ($handlers as $handler)
			{
				$priceHandler = PriceHelper::getHandler($handler->name);
				
				// Find the handler where the pricing type matches and get the handler name
				if ($priceHandler && $priceHandler->matchPricingType($pricingType))
				{
					$pricingType = $handler->name;
					break;
				}
			}
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
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		$query->select('id, product_id, seller_uid')->from($this->tableName);

		$iterator = $db->setQuery($query)->getIterator();

		foreach ($iterator as $item)
		{
			$this->addIndex(sprintf('%d:%d', $item->product_id, $item->seller_uid), (int) $item->id);
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
		if (!$obj->x__product_id || !$obj->x__seller_uid)
		{
			return;
		}

		$user = $this->importer->getUser();

		if (!$this->helper->access->checkAny(array('seller', 'seller.own'),'product.edit.', '', 'com_sellacious', $user->id))
		{
			return;
		}

		$key = sprintf('%d:%d', $obj->x__product_id, $obj->x__seller_uid);

		$record = new \stdClass;

		$record->id             = ArrayHelper::getValue($this->index, $key);
		$record->product_id     = $obj->x__product_id;
		$record->seller_uid     = $obj->x__seller_uid;
		$record->pricing_type   = $obj->pricing_type;
		$record->quantity_min   = (int) $obj->min_order_qty;
		$record->quantity_max   = (int) $obj->max_order_qty;
		$record->stock          = (int) $obj->product_current_stock;
		$record->over_stock     = (int) $obj->product_over_stock_sale_limit;
		$record->stock_reserved = (int) $obj->product_reserved_stock;
		$record->stock_sold     = (int) $obj->product_stock_sold;
		$record->state          = 1;

		$sellerSku = $obj->seller_sku;

		if (empty($sellerSku) || $this->helper->product->isSkuUnique($obj->x__product_id, $obj->x__seller_uid, $sellerSku))
		{
			$record->seller_sku = $sellerSku;
		}

		$db = $this->importer->getDb();

		if ($record->id)
		{
			$db->updateObject($this->tableName, $record, array('id'));
		}
		else
		{
			$db->insertObject($this->tableName, $record, 'id');

			$this->index[$key] = (int) $record->id;
		}

		$obj->x__psx_id = $record->id;
	}
}
