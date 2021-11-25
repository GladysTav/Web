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
 * View class for a list of Ccl.
 *
 * @since  1.6
 */
class CclViewDashboard extends JViewLegacy
{
	/**
	 * @var array
	 * @since 1.0
	 */
	protected $plugins;

	/**
	 * @var bool
	 * @since 1.0
	 */
	protected $isUserPluginEnabled;

	/**
	 * @var bool
	 * @since 1.0
	 */
	protected $isSystemPluginEnabled;

	/**
	 * @var int
	 * @since 1.1
	 */
	protected $moduleIdentifier;

	/**
	 * @var string
	 * @since 1.1
	 */
	protected $templateOverrideDirectory;

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
		$this->plugins                   = $this->get('Plugins');
		$this->isUserPluginEnabled       = $this->get('UserPluginEnabled');
		$this->isSystemPluginEnabled     = $this->get('SystemPluginEnabled');
		$this->moduleIdentifier          = $this->get('ModuleIdentifier');
		$this->templateOverrideDirectory = '&#x3C;website_path&#x3E;/templates/' . $this->get('Template') . '/html';


		JToolBarHelper::title(JText::_('COM_CCL_TITLE_DASHBOARD'));
		JToolbarHelper::preferences('com_ccl');
		JToolbarHelper::custom('importFromOfflajn', 'download', 'COM_CCL_DASHBOARD_IMPORT_OFFLAJN', 'COM_CCL_DASHBOARD_IMPORT_OFFLAJN', false);

		parent::display($tpl);
	}
}
