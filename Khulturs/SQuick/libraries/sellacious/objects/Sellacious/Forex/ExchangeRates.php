<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Forex;

// no direct access.
defined('_JEXEC') or die;

use Sellacious\Forex;

/**
 * Exchange Rates API
 *
 * @package  Sellacious\Forex
 *
 * @since    1.7.3
 */
class ExchangeRates extends Forex
{
	/**
	 * Method to get the forex rate for a given pair of currencies using live API
	 *
	 * @param   string  $from  Currency code for the source currency
	 * @param   mixed   $to    Currency code for the target currency, can also be an array
	 *
	 * @return  float|float[]  This converted value
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   1.7.3
	 */
	public function getLiveRate($from = null, $to = null)
	{
		try
		{
			$uri  = new \JUri('https://api.exchangeratesapi.io/latest');
			$from = $from ?: $this->from->code_3;
			$to   = $to ?: $this->to->code_3;
			$to   = is_array($to) ? $to : explode(',', $to);

			// Not using $to and fetching all rates, because limited conversions are available for this API
			$uri->setVar('base', $from);

			$transport = new \JHttp;
			$response  = $transport->get($uri->toString(), null, 30);

			$result = json_decode($response->body, true);

			if (isset($result['error']))
			{
				throw new \Exception($result['error']);
			}

			$rates = array();

			foreach ($result['rates'] as $code => $rate)
			{
				if (in_array($code, $to))
				{
					$rates[$code] = $rate;
				}
			}

			if (is_array($to))
			{
				return $rates;
			}
			elseif (isset($rates[$to]))
			{
				return $rates[$to];
			}
			else
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_EXCHANGE_RATES_INVALID_RESPONSE'));
			}
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_EXCHANGE_RATES_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
