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

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class CclViewPassword extends JViewLegacy
{
	/**
	 * @var string
	 * @since 1.0
	 */
	protected $username;

	/**
	 * @var string
	 * @since 1.0
	 */
	protected $auth;

	/**
	 * {@inheritdoc}
	 *
	 * @param null $tpl
	 *
	 *
	 * @since 1.0
	 */
	public function display($tpl = null)
	{
		$input          = JFactory::getApplication()->input;
		$data           = json_decode(base64_decode($input->getBase64('data')), true);
		$this->username = $data['username'];
		$this->auth     = $data['auth'];

		parent::display($tpl);
	}
}
