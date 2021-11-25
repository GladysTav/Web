<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Template;

// no access
defined('_JEXEC') or die;

/**
 * Template Variable for Products in Order
 *
 * @package  Sellacious\Template
 *
 * @since     2.0.0
 */
class OrderItemsGridVariable extends TemplateVariable
{
	/**
	 * Replace the relevant variable with respective value using given callback.
	 * Use the callback or extend this class and implement your own parse() method.
	 *
	 * @param   AbstractTemplate  $template
	 * @param   string            $text
	 * @param   mixed             $data
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function parse(AbstractTemplate $template, $text, $data)
	{
		$value = isset($data[$this->name]) ? $data[$this->name] : null;

		if (is_array($value) && count($value) > 0)
		{
			$pattern = '@(%GRID_BEGIN%).*?<tbody>(.*?)</tbody>.*?(%GRID_END%)@s';
			$found   = preg_match($pattern, $text, $match);

			while ($found)
			{
				reset($value);
				$rows = array();

				foreach ($value as $items)
				{
					$row = trim($match[2]);

					foreach ($items as $key => $item)
					{
						$row = str_replace('%' . strtoupper($key) . '%', $items[$key], $row);
					}

					$rows[] = $row;
				}

				$output = str_ireplace(array($match[1], $match[2], $match[3]), array('', implode("\n", $rows), ''), $match[0]);
				$text   = str_ireplace($match[0], $output, $text);

				// Find next match after processing previous one.
				$found = preg_match($pattern, $text, $match);
			}
		}

		return $text;
	}
}
