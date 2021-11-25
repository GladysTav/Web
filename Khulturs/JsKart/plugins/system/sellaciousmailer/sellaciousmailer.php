<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Sellacious\Communication\Queue\MessageQueue;

defined('_JEXEC') or die;

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * Sellacious mailer plugin
 *
 * @since  1.0
 */
class PlgSystemSellaciousMailer extends SellaciousPlugin
{
	/**
	 * @var    boolean
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = true;

	/**
	 * This method sends a reminder email for non-activated users.
	 *
	 * @return  void
	 *
	 * @since   1.3.3
	 */
	public function onAfterRoute()
	{
		try
		{
			$app = JFactory::getApplication();
		}
		catch (Exception $e)
		{
			$app = null;
		}

		$db      = JFactory::getDbo();
		$limit   = $this->params->get('limit', 10);
		$cron    = $this->params->get('cron', 1);
		$cronKey = $this->params->get('cron_key', '');
		$secret  = $app->input->getString('mailer_key');

		// Quit if cron use is enabled and the cronKey mismatched
		if ($cron && (strlen($cronKey) == 0 || $cronKey !== $secret))
		{
			return;
		}

		try
		{
			$queue = new MessageQueue;
			$token = $queue->lock($limit);

			if ($token)
			{
				$queue->setFooter('email', $this->getFooter('email'));
				$queue->setFooter('default', $this->getFooter('default'));

				$queue->send();
			}
		}
		catch (Exception $e)
		{
			JLog::add('Sellacious mailer batch error: ' . $e->getMessage(), JLog::INFO);
		}

		if ($cron)
		{
			$app->close();
		}
	}

	protected function getFooter($type)
	{
		$layoutPath = JPluginHelper::getLayoutPath($this->_type, $this->_name, $type);
		$helper     = SellaciousHelper::getInstance();

		if (is_file($layoutPath) && ($helper->config->get('show_brand_footer', 1) || !$helper->access->isSubscribed()))
		{
			ob_start();
			include $layoutPath;
			return ob_get_clean();
		}

		return '';
	}
}

endif;
