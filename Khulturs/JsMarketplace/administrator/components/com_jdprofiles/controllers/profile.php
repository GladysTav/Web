<?php
/**
 *  
 * @package    Com_Jdprofiles
 * @author      Joomdev
 * @copyright  Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Profile controller class.
 *
 * @since  1.6
 */
class JdprofilesControllerProfile extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'profiles';
		parent::__construct();
	}
}
