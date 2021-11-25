<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Forex;

// no direct access.
defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;
use Sellacious\Forex;

/**
 * Currency Converter API
 *
 * @package  Sellacious\Forex
 *
 * @since    1.7.3
 */
class CurrencyConverter extends Forex
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
			$from = $from ?: $this->from->code_3;
			$to   = $to ?: $this->to->code_3;
			$to   = is_array($to) ? $to : explode(',', $to);

			$config = ConfigHelper::getInstance('com_sellacious');
			$mode   = $config->get('currency_converter_api_mode', 'free');
			$value  = $config->get('currency_converter_access_key');
			$base   = $mode == 'live' ? 'https://api.currconv.com' : 'https://free.currconv.com';
			$uri    = new \JUri('' . $base . '/api/v7/convert');

			if (!$value)
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_CURRENCY_CONVERTER_INVALID_ACCESS_KEY'));
			}

			$query = array();

			foreach ($to as $code)
			{
				$query[] = $from . '_' . $code;
			}

			$uri->setVar('apiKey', $value);

			$rates = array();

			if ($mode == 'free' && count($query) > 10)
			{
				// Free mode only allows 10 conversions at a time
				$chunks = array_chunk($query, 10);

				foreach ($chunks as $chunk)
				{
					$uri->setVar('q', implode(',', $chunk));

					$transport = new \JHttp;
					$response  = $transport->get($uri->toString(), null, 30);

					$result = json_decode($response->body, true);

					if (isset($result['error']))
					{
						throw new \Exception($result['error']);
					}

					foreach ($result['results'] as $result)
					{
						$rates[$result['to']] = $result['val'];
					}
				}
			}
			else
			{
				$uri->setVar('q', implode(',', $query));

				$transport = new \JHttp;
				$response  = $transport->get($uri->toString(), null, 30);

				$result = json_decode($response->body, true);

				if (isset($result['error']))
				{
					throw new \Exception($result['error']);
				}

				foreach ($result['results'] as $res)
				{
					$rates[$res->to] = $res->val;
				}
			}

			return $rates;
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_CURRENCY_CONVERTER_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
