<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Filesystem\Folder;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('radio');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		2.0.0
 */
class JFormFieldViewDesign extends JFormFieldRadio
{
	/**
	 * The field type.
	 *
	 * @var	   string
	 *
	 * @since  2.0.0
	 */
	protected $type = 'viewdesign';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'sellacious.form.field.viewdesign';

	/**
	 * Name of the option.
	 *
	 * @var	   array
	 *
	 * @since  2.0.0
	 */
	protected $option = '';

	/**
	 * Name of the context (page/block).
	 *
	 * @var	   array
	 *
	 * @since  2.0.0
	 */
	protected $context = '';

	/**
	 * Name of the view.
	 *
	 * @var	   array
	 *
	 * @since  2.0.0
	 */
	protected $view = '';

	protected function getOptions()
	{
		$options = array();
		if ($this->context == 'blocks')
		{
			$layoutPath = JPATH_ROOT . "/layouts/sellacious/{$this->view}/designs";
			$layoutUrl  = JUri::root() . "layouts/sellacious/{$this->view}/designs";
		}
		else
		{
			$layoutPath = JPATH_ROOT . "/designs/{$this->option}/{$this->view}";
			$layoutUrl  = JUri::root() . "designs/{$this->option}/{$this->view}";
		}

		if (JFolder::exists($layoutPath))
		{
			$designs = Folder::folders($layoutPath);
			foreach ($designs as $design)
			{
				$files = Folder::files($layoutPath . '/' . $design);
				foreach ($files as $file)
				{
					if (strpos($file, 'preview') !== false)
					{
						$previewPath = $layoutUrl . '/' . $design . '/' . $file;
					}
					else
					{
						$previewPath = '';
					}
				}

				$options[] = array(
					'design'  => $design,
					'preview' => $previewPath,
				);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);

		if (isset($element['context']))
		{
			$context = (string) $element['context'];
			$context = explode('.', $context);

			$this->option  = $context[0];
			$this->context = $context[1];
			$this->view    = $context[2];
		}
		else
		{
			return false;
		}

		return true;
	}

}
