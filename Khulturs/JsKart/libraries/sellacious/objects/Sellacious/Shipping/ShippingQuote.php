<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Shipping;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use SellaciousHelper;

/**
 * This base object will be immutable, however this can be extended
 * and the child classes may allow property write if needed.
 *
 * @package  Sellacious\Shipping
 *
 * @since   1.5.2
 */
class ShippingQuote
{
	/**
	 * @var   int
	 *
	 * @since   1.5.2
	 */
	public $id;

	/**
	 * @var   int
	 *
	 * @since   1.5.2
	 */
	public $ruleId;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $ruleTitle;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $ruleHandler;

	/**
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	public $ruleOwner;

	/**
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	public $sellerUid;

	/**
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	public $sellerName;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $label;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $service;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $serviceName;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	public $tbd;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	public $free;

	/**
	 * @var   float
	 *
	 * @since   1.5.2
	 */
	public $amount;

	/**
	 * @var   float
	 *
	 * @since   1.5.2
	 */
	public $total;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $note;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $deliveryDate;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	public $transitTime;

	/**
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	public $creditAmountTo;

	/**
	 * Constructor
	 *
	 * @param   string  $identity
	 *
	 * @since   1.5.2
	 */
	public function __construct($identity)
	{
		$this->id = (string) $identity;

		$this->reset();
	}

	/**
	 * Setup the instance as a free shipping
	 *
	 * @return  static  Allows chaining
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function reset()
	{
		$helper = SellaciousHelper::getInstance();

		$this->ruleId         = 0;
		$this->ruleTitle      = null;
		$this->ruleHandler    = null;
		$this->ruleOwner      = 0;
		$this->sellerUid      = null;
		$this->service        = null;
		$this->serviceName    = null;
		$this->label          = null;
		$this->tbd            = true;
		$this->free           = false;
		$this->amount         = null;
		$this->total          = null;
		$this->note           = null;
		$this->deliveryDate   = null;
		$this->transitTime    = null;
		$this->creditAmountTo = $helper->config->get('shipping_credit_to', 'seller');

		return $this;
	}

	/**
	 * Setup the instance as a undecided/tbd shipping
	 *
	 * @param   int    $sellerUid  Seller user id
	 *
	 * @return  static  Allows chaining
	 *
	 * @since   1.5.2
	 */
	public function setupTbd($sellerUid = null)
	{
		$this->reset();

		$this->ruleId    = 0;
		$this->ruleTitle = \JText::_('COM_SELLACIOUS_CART_SHIPPING_TBD');
		$this->sellerUid = $sellerUid;
		$this->tbd       = true;
		$this->free      = false;
		$this->amount    = 0;
		$this->total     = 0;

		return $this;
	}

	/**
	 * Setup the instance as a free shipping
	 *
	 * @return  static  Allows chaining
	 *
	 * @since   1.5.2
	 */
	public function setupFree()
	{
		$this->reset();

		$this->ruleTitle    = \JText::_('COM_SELLACIOUS_CART_SHIPPING_FREE');
		$this->tbd          = false;
		$this->free         = true;
		$this->amount       = 0;
		$this->total        = 0;

		return $this;
	}

	/**
	 * Setup the instance as a flat shipping
	 *
	 * @param   float  $amount     Total shipping fee
	 * @param   float  $rate       Total shipping fee per unit item, if applicable
	 * @param   int    $sellerUid  Seller user id
	 *
	 * @return  static  Allows chaining
	 *
	 * @since   1.5.2
	 */
	public function setupFlat($amount, $rate, $sellerUid = null)
	{
		// If flat fee is ZERO, it is eventually free!
		if (abs(round($amount, 2)) < 0.01)
		{
			return $this->setupFree();
		}

		$this->reset();

		$helper  = SellaciousHelper::getInstance();
		$filters = array('list.select' => 'a.title', 'list.where' => array('a.user_id = ' . (int) $sellerUid));

		$this->ruleId     = 0;
		$this->ruleTitle  = \JText::_('COM_SELLACIOUS_CART_SHIPPING_FLAT_FEE');
		$this->sellerUid  = $sellerUid;
		$this->sellerName = $sellerUid ? $helper->seller->loadResult($filters) : null;
		$this->tbd        = false;
		$this->free       = false;
		$this->amount     = $rate;
		$this->total      = $amount;

		return $this;
	}

	/**
	 * Merge another quote instance to this instance, usually to add up the total value
	 *
	 * @param   ShippingQuote  $quote  The quote object to be merged
	 *
	 * @return  static  Allows chaining
	 *
	 * @throws  \Exception  If the quotes are not of the same rule/handler type
	 *
	 * @since   1.5.2
	 */
	public function merge(ShippingQuote $quote)
	{
		if ($quote->ruleId != $this->ruleId || $quote->ruleHandler != $this->ruleHandler)
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_QUOTE_MERGE_RULE_MISMATCH'));
		}

		if (!$quote->free)
		{
			if ($quote->tbd)
			{
				$this->tbd = true;
			}

			if ($quote->total)
			{
				$this->total += $quote->total;
				$this->amount = 0;
			}
		}

		if (abs(round($this->total, 2)) < 0.01)
		{
			$this->free = true;
		}

		// Todo: Also handle other parameters like delivery estimates etc.

		return $this;
	}
}
