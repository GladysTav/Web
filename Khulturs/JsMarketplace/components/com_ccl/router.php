<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JLoader::registerPrefix('Ccl', JPATH_SITE . '/components/com_ccl/');

/**
 * Class CclRouter
 *
 * @since  3.3
 */
class CclRouter extends JComponentRouterBase
{
	/**
	 * Build method for URLs
	 * This method is meant to transform the query parameters into a more human
	 * readable form. It is only executed when SEF mode is switched on.
	 *
	 * @param   array &$query An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();
		$view     = null;

		if (isset($query['task']))
		{
			$taskParts  = explode('.', $query['task']);
			$segments[] = implode('/', $taskParts);
			unset($query['task']);
		}

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		if (!empty($query['auth']))
		{
			$segments[] = $query['auth'];
			unset($query['auth']);
		}


		return $segments;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array &$segments The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// View is always the first element of the array
		$segmentElement = array_shift($segments);
		$segmentElement == 'login' ? $vars['task'] = $segmentElement : $vars['view'] = $segmentElement;

		if (!empty($segments))
		{
			$vars['auth'] = array_shift($segments);
		}

		return $vars;
	}
}
