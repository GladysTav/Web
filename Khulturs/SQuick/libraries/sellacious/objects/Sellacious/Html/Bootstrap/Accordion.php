<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Html\Bootstrap;

use JLayoutHelper;

/**
 * @package  Accordion class for ctech-bootstrap
 *
 * @since    2.0.0
 */

class Accordion
{

	protected static $active = '';

	/**
	 * Starts a ctech-bootstrap accordion
	 *
	 * @param   int    $id       Id of the accordion
	 * @param   array  $options  Options for the accordion (Active)
	 *
	 * @return  string  HTML markup for start of a ctech-bootstrap accordion
	 *
	 * @since   2.0.0
	 */
	public static function startAccordion($id, $options)
	{
		self::$active = $options['active'];

		$params = array(
			'id' => $id,
		);

		return JLayoutHelper::render('sellacious.ctech-bootstrap.startAccordion', $params);
	}

	/**
	 * Adds a ctech-bootstrap accordion slide
	 *
	 * @param   int     $id      Id of the accordion slide
	 * @param   int     $parent  Id of the parent accordion
	 * @param   string  $label   Label of the accordion slide
	 *
	 * @return  string  HTML markup for adding a ctech-bootstrap accordion slide
	 *
	 * @since   2.0.0
	 */
	public static function addAccordionSlide($id, $parent, $label)
	{
		$params = array(
			'id'     => $id,
			'parent' => $parent,
			'label'  => $label,
			'active' => self::$active
		);

		return JLayoutHelper::render('sellacious.ctech-bootstrap.addAccordionSlide', $params);
	}

	/**
	 * Ends a ctech-bootstrap accordion
	 *
	 * @return  string  HTML markup for end of a ctech-bootstrap accordion
	 *
	 * @since   2.0.0
	 */
	public static function endAccordionSlide()
	{
		return JLayoutHelper::render('sellacious.ctech-bootstrap.endAccordionSlide');
	}

	/**
	 * Ends a ctech-bootstrap accordion
	 *
	 * @return  string  HTML markup for end of a ctech-bootstrap accordion
	 *
	 * @since   2.0.0
	 */
	public static function endAccordion()
	{
		return JLayoutHelper::render('sellacious.ctech-bootstrap.endAccordion');
	}
}
