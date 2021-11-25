<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Forex;

use Sellacious\Config\ConfigHelper;
use Sellacious\Forex;

defined('_JEXEC') or die;

/**
 * Sellacious API
 *
 * @package  Sellacious\Forex
 *
 * @since    2.0.0
 */
class Sellacious extends Forex
{
	/**
	 * Method to get the forex rate for a given pair of currencies using live API
	 *
	 * @param   string   $from  Currency code for the source currency
	 * @param   mixed    $to    Currency code for the target currency, can also be an array
	 *
	 * @return  float|float[]  This converted value
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   2.0.0
	 */
	public function getLiveRate($from = null, $to = null)
	{
		try
		{
			$from  = $from ?: $this->from->code_3;
			$to    = $to ?: $this->to->code_3;
			$uri   = new \JUri('https://www.sellacious.com/api/forex.php');
			$rates = array();

			$uri->setVar('method', 'fetchRates');
			$uri->setVar('source', $from);
			$uri->setVar('symbols', is_array($to) ? implode(',', $to) : $to);

			// Add license key to API Endpoint
			$helper  = \SellaciousHelper::getInstance();
			$license = $helper->core->getLicense();

			$uri->setVar('apiKey', $license->get('site_id'));

			$transport = new \JHttp;
			$response  = $transport->get($uri->toString(), null, 30);

			$result = json_decode($response->body, true);

			if (isset($result['error']))
			{
				throw new \Exception($result['error']);
			}

			foreach ($result['data']['results'] as $res)
			{
				$rates[$res['to']] = $res['val'];
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
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_SELLACIOUS_INVALID_RESPONSE'));
			}
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_SELLACIOUS_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
