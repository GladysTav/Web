<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 *  Templates controller class.
 */
class SellaciousTemplatesControllerTemplate extends SellaciousControllerForm
{
	/**
	 * @var		string	The name of the list view related to this
	 *
	 * @since	1.7.0
	 */
	protected $view_list = 'templates';

	/**
	 * @var		string	The prefix to use with controller messages
	 *
	 * @since	1.7.0
	 */
	protected $text_prefix = 'COM_SELLACIOUSTEMPLATES_TEMPLATE';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	protected function allowSave($data, $key = 'id')
	{
		return $this->helper->access->check('template.edit');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.7.0
	 */
	public function getModel($name = 'Template', $prefix = 'SellaciousTemplatesModel', $config = null)
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get template preview with short codes replaced
	 *
	 * @since   1.7.0
	 */
	public function getPreviewAjax()
	{
		$html    = $this->app->input->get('html', '', 'RAW');
		$context = $this->app->input->get('context');

		/** @var \SellaciousTemplatesModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$html = $model->getPreview($context, $html);

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

	/**
	 * AJAX Method to get Template Default Preview
	 *
	 * @since   1.7.0
	 */
	public function getTemplateDefaultAjax()
	{
		$context = $this->app->input->get('context');

		/** @var \SellaciousTemplatesModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$def = $model->getTemplateDefaultPreview($context);

			$this->app->setUserState($this->option . '.preview', $def);
			$this->app->setUserState($this->option . '.preview_context', $context);

			echo new JResponseJson(array('default_template' => $def));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$this->app->close();
	}
}
