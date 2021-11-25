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

use Joomla\Event\AbstractEvent;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\Media\Attachment;
use stdClass;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
abstract class AbstractNotificationTemplate extends AbstractTemplate
{
	/**
	 * Constructor
	 *
	 * Use the passed event object to extract relevant data for notification content
	 *
	 * @param   AbstractEvent  $event
	 *
	 * @since   2.0.0
	 */
	abstract public function __construct(AbstractEvent $event);

	/**
	 * Get a list of recipients of given type based on the event,
	 * For guest users we can only promise of having email and/or phone
	 *
	 * @param   string  $type  The recipient type
	 *
	 * @return  array  An array containing user id for registered users / an object [name, email, phone] for guests
	 *
	 * @since   2.0.0
	 */
	abstract public function getRecipients($type);

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	abstract protected function loadObject();

	/**
	 * Get the replacement values for the variables. This will be used to parse the template.
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public function getValues()
	{
		if (!$this->values)
		{
			$data         = $this->loadObject();
			$this->values = $data ? ArrayHelper::fromObject($data) : array();
		}

		return parent::getValues();
	}

	/**
	 * Get a list of attachments if applicable
	 *
	 * @param   string  $type  The recipient type so that we have the flexibility to
	 *                         support different attachment for different recipient type on same event
	 *
	 * @return  Attachment[]
	 *
	 * @since   2.0.0
	 */
	public function getAttachments($type = null)
	{
		return array();
	}

	/**
	 * Set default variables and sample
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		$this->addVariable(new SiteNameTemplateVariable('sitename', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_GLOBAL_SITENAME'), ''));
		$this->addVariable(new SiteUrlTemplateVariable('site_url', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_GLOBAL_SITE_URL'), ''));
		$this->addVariable(new EmailHeaderTemplateVariable('email_header', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_GLOBAL_EMAIL_HEADER'), ''));
		$this->addVariable(new EmailFooterTemplateVariable('email_footer', JText::_('COM_SELLACIOUS_EMAILTEMPLATE_SHORTCODE_GLOBAL_EMAIL_FOOTER'), ''));

		parent::loadVariables();
	}
}
