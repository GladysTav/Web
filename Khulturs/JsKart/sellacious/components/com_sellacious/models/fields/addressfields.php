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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework. Provides configuration for showing/hiding address fields

 * @since  2.0.0
 */
class JFormFieldAddressFields extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var	 string
	 */
	protected $type = 'addressfields';

	protected $layout = 'sellacious.form.field.addressfields.addressfields';

	protected $fields = array();

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
	 * @since   2.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);
		$values = json_decode($value);

		$options = $element->option;

		if (isset($values))
		{
			foreach ($values as $i => $value)
			{
				$value  = new Registry($value);
				$option = array();
				$context = array();
				foreach ($options as $opt)
				{
					if ($opt['name'] == $value->get('name'))
					{
						$option = $opt;
						foreach ($opt->option as $o)
						{
							$coValue = true;
							foreach ($value->get('options') as $co)
							{
								if ($co->name === (string) $o['name'])
								{
									$coValue = $co->show;
								}
							}

							$context[] = (object) array(
								'name'  => (string) $o['name'],
								'label' => (string) $o['label'],
								'show'  => $coValue
							);
						}
						break;
					}
				}

				$this->fields[] = array(
					'name'       => (string) $option['name'],
					'type'       => (string) $option['type'],
					'context'    => (string) $option['context'],
					'label'      => (string) $option['label'],
					'labelValue' => JText::_((string) $value->get('labelValue')),
					'class'      => (string) $option['class'],
					'show'       => (int) $value->get('show', 1),
					'lines'      => $option['type'] == 'address' ? $value->get('lines', 1) : 1,
					'textOnly'   => $option['type'] == 'location' ? $value->get('textOnly', false) : '',
					'options'    => $context
				);
			}
		}
		else
		{
			foreach ($options as $option)
			{
			    $context = [];

			    if (isset($option->option))
			    {
                    foreach ($option->option as $o)
                    {
                        $context[] = (object) array(
                            'name' => (string) $o['name'],
                            'label' => (string) $o['label'],
                            'show' => true
                        );
                    }
                }

				$this->fields[] = array(
					'name'       => (string) $option['name'],
					'type'       => (string) $option['type'],
					'context'    => (string) $option['context'],
					'label'      => (string) $option['label'],
					'labelValue' => JText::_((string) $option['label']),
					'class'      => (string) $option['class'],
					'show'       => 1,
					'lines'      => 1,
					'textOnly'   => $option['type'] == 'location' ? false : null,
                    'options'    => $context
				);
			}
		}

		return true;
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
		$data = parent::getLayoutData();

		$data['fields'] = $this->fields;

		return $data;
	}
}
