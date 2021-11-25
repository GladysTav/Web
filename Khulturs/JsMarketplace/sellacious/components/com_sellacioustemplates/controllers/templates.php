<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Templates list controller class.
 */
class SellaciousTemplatesControllerTemplates extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 *
	 * @since	1.7.0
	 */
	protected $text_prefix = 'COM_SELLACIOUSTEMPLATES_TEMPLATES';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 */
	public function getModel($name = 'Template', $prefix = 'SellaciousTemplatesModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to get template preview with short codes replaced
	 *
	 * @since   1.7.0
	 */
	public function getPreviewAjax()
	{
		$context = $this->app->input->get('context');

		/** @var \SellaciousTemplatesModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$table = SellaciousTable::getInstance('Template');
			$table->load(array('context' => $context));

			$html = $model->getPreview($context, $table->get('body'));

			$this->app->setUserState($this->option . '.preview', $html);
			$this->app->setUserState($this->option . '.preview_context', $context);

			echo new JResponseJson('');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}
}
