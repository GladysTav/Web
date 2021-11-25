<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Utilities\Otp;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

class JFormFieldPreVerify extends JFormField
{
	/**
	 * The field type
	 *
	 * @var	   string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'preverify';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since   2.0.0
	 */
	protected $layout = 'sellacious.form.field.preverify.input';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('ctech.bootstrap');
		JHtml::_('behavior.formvalidator');

		JHtml::_('script', 'sellacious/vue.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
		JHtml::_('script', 'sellacious/field.preverify.js', array('relative' => true, 'version' => S_VERSION_CORE));
		JHtml::_('stylesheet', 'sellacious/field.preverify.css', array('relative' => true, 'version' => S_VERSION_CORE));

		return parent::getInput();
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getLayoutData()
	{
		$data   = parent::getLayoutData();
		$config = ConfigHelper::getInstance('com_sellacious');

		$data['form']       = $this->form;
		$data['formName']   = $this->form->getName();
		$data['fieldType']  = (string) $this->element['fieldtype'] ?: 'email';
		$data['unique']     = (string) $this->element['unique'] === 'true';
		$data['otp_length'] = $config->get('otp_length', 6);

		$value = Otp::secureUnhash($this->value);

		if ($value)
		{
			// If value parsed, use it
			$data['value']     = $value;
			$data['validated'] = true;
			$data['pv_token']  = $this->value;
		}
		else
		{
			// If parse failed, set unverified and empty. If not hashed, its raw.
			$data['value']     = $value === null ? $this->value : '';
			$data['validated'] = false;
			$data['pv_token']  = null;
		}

		return $data;
	}
}
