<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Template;

defined('_JEXEC') or die;

/**
 * @package  Sellacious\Template
 *
 * @since     2.0.0
 */
class TemplateVariable
{
	/**
	 * The name of this template variable
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	public $name;

	/**
	 * The description of this template variable
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	public $description;

	/**
	 * The sample value for use when generating template preview
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	public $sample;

	/**
	 * The function to be used to process template variable
	 *
	 * @var   callable
	 *
	 * @since   2.0.0
	 */
	public $callback;

	/**
	 * TemplateVariable constructor
	 *
	 * @param   string    $name
	 * @param   string    $description
	 * @param   string    $sample
	 * @param   Callable  $callback
	 *
	 * @since   2.0.0
	 */
	public function __construct($name, $description, $sample, Callable $callback = null)
	{
		$this->name        = strtolower($name);
		$this->description = $description ?: ucfirst(implode(' ', explode('_', $name)));
		$this->sample      = $sample;
		$this->callback    = $callback;
	}

	/**
	 * Replace the relevant variable with respective value using given callback.
	 * Use the callback or extend this class and implement your own parse() method.
	 *
	 * @param   AbstractTemplate  $template
	 * @param   string            $text
	 * @param   array             $data
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function parse(AbstractTemplate $template, $text, $data)
	{
		if ($this->callback && is_callable($this->callback))
		{
			return call_user_func_array($this->callback, func_get_args());
		}

		$replacement = '';

		if (isset($data[$this->name]) && is_scalar($data[$this->name]))
		{
			$replacement = $data[$this->name];
		}

		$text = str_ireplace('%' . $this->name . '%', $replacement, $text);

		return $text;
	}
}
