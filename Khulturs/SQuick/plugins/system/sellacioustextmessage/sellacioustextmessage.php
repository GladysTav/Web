<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Communication\Message\TextMessage;

jimport('sellacious.loader');

JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries/src');

/**
 * Text Message Plugin
 *
 * @since   2.0.0
 */
class plgSystemSellaciousTextmessage extends SellaciousPlugin
{
	/**
	 * Whether this class has a configuration to inject into sellacious configurations
	 *
	 * @var    bool
	 *
	 * @since  2.0.0
	 */
	protected $hasConfig = true;

	/**
	 * Method to register and load email handler
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onAfterInitialise()
	{
		CommunicationHelper::addHandler('text', TextMessage::class, JText::_('PLG_SYSTEM_SELLACIOUSTEXTMESSAGE_COMMUNICATION_HANDLER_TEXT_LABEL'), JText::_('PLG_SYSTEM_SELLACIOUSTEXTMESSAGE_COMMUNICATION_HANDLER_TEXT_DESC'));
	}

	/**
	 * Prepare form
	 *
	 * @param   JForm  $form  The form to be altered
	 * @param   array  $data  The associated data for the form
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm && $form->getName() == 'com_sellacious.emailtemplate')
		{
			$form->loadFile(__DIR__ . '/forms/textmessage.xml');
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string $context The context for the data
	 * @param   object $data    An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 *
	 * @throws  Exception
	 */
	public function onContentPrepareData($context, $data)
	{
		parent::onContentPrepareData($context, $data);

		if ($context == 'com_sellacious.emailtemplate' && is_object($data))
		{
			$table = SellaciousTable::getInstance('EmailTemplate');
			$table->load(array('context' => $data->context, 'message_type' => 'text'));

			if ($table->get('id'))
			{
				if (!isset($data->handlers))
				{
					$data->handlers = new stdClass;
				}

				$data->handlers->text = $table->getProperties(true);
			}
		}
	}

	/**
	 * Method is called right before form is validated
	 *
	 * @param   JForm   $form  The form
	 * @param   object  $data  The data to validate
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 *
	 * @throws  Exception
	 */
	public function onFormDataValidation($form, $data)
	{
		if ($form instanceof JForm && $form->getName() == 'com_sellacious.emailtemplate')
		{
			$registry = new Registry($data);
			$actual   = $registry->get('handlers.text.send_actual_recipient');

			if (!$actual && trim($registry->get('handlers.text.recipients')) === '')
			{
				throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSTEXTMESSAGE_ACTUAL_RECIPIENTS_OR_ALTERNATE_REQUIRED_WARNING'));
			}
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $table    A JTable object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 *
	 * @throws  Exception
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$data     = $this->app->input->get('jform', array(), 'array');
			$mCtx     = ArrayHelper::getValue($data, 'context');
			$handlers = ArrayHelper::getValue($data, 'handlers');
			$record   = ArrayHelper::getValue($handlers, 'text');

			if (is_array($record) && isset($record['body']))
			{
				$tbl = SellaciousTable::getInstance('EmailTemplate');
				$tbl->load(array('context' => $mCtx, 'message_type' => 'text'));

				// Must have subject, because the list view requires it
				$record['subject'] = JHtml::_('string.truncate', $record['body'], 100);
				$record['context'] = $mCtx;

				$tbl->bind($record);
				$tbl->check();
				$tbl->store();
			}
		}

		return true;
	}
}
