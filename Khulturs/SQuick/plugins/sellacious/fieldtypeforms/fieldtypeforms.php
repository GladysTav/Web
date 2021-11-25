<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Language\LanguageHelper;

/**
 * Class plgSellaciousFieldTypeForms
 *
 */
class plgSellaciousFieldTypeForms extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		JFormHelper::addFieldPath(__DIR__ . '/fields');
	}

	/**
	 * Fetch the available field types
	 *
	 * @param   string  $context
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function onFetchFieldTypes($context = null)
	{
		$assoc = array();

		if ($context == 'com_sellacious.field')
		{
			$assoc = array(
				'calendar'   => JText::_('COM_SELLACIOUS_FIELD_TYPE_CALENDAR'),
				'checkbox'   => JText::_('COM_SELLACIOUS_FIELD_TYPE_CHECKBOX'),
				'checkboxes' => JText::_('COM_SELLACIOUS_FIELD_TYPE_CHECKBOXES'),
				'color'      => JText::_('COM_SELLACIOUS_FIELD_TYPE_COLOR'),
				'hidden'     => JText::_('COM_SELLACIOUS_FIELD_TYPE_HIDDEN'),
				'number'     => JText::_('COM_SELLACIOUS_FIELD_TYPE_NUMBER'),
				'list'       => JText::_('COM_SELLACIOUS_FIELD_TYPE_LIST'),
				'radio'      => JText::_('COM_SELLACIOUS_FIELD_TYPE_RADIO'),
				'text'       => JText::_('COM_SELLACIOUS_FIELD_TYPE_TEXT'),
				'textarea'   => JText::_('COM_SELLACIOUS_FIELD_TYPE_TEXTAREA'),
				'timezone'   => JText::_('COM_SELLACIOUS_FIELD_TYPE_TIMEZONE'),
				'unitcombo'  => JText::_('COM_SELLACIOUS_FIELD_TYPE_UNITCOMBO'),
				'location'   => JText::_('COM_SELLACIOUS_FIELD_TYPE_LOCATION'),
			);
		}

		return $assoc;
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  bool
	 *
	 * @since   1.7.3
	 */
	public function onContentPrepareData($context, $data)
	{
		if ($context == 'com_sellacious.field')
		{
			if (is_object($data) && isset($data->params['listoptions']))
			{
				$listOptions = array();

				foreach ($data->params['listoptions'] as $listoption)
				{
					$option       = array('en-GB' => $listoption);
					$translations = isset($data->params['listoptions_translated']) ? $data->params['listoptions_translated'] : array();

					if (!empty($translations) && isset($translations[$listoption]))
					{
						$option = array_merge($option, $translations[$listoption]);
					}

					$listOptions[] = $option;
				}

				$data->params['listoptions'] = $listOptions;
			}
		}

		return true;
	}

	/**
	 * Adds additional fields to the sellacious field editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		$field = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		JLoader::import('sellacious.loader');
		$helper = SellaciousHelper::getInstance();

		if (!class_exists('SellaciousHelper'))
		{
			return true;
		}

		if ($name == 'com_sellacious.field')
		{
			// Check if a valid field type is selected.
			$types = $helper->field->getTypes(true);

			if (empty($field->type) || !array_key_exists($field->type, $types))
			{
				return true;
			}

			$form->loadFile(__DIR__ . '/forms/' . $field->type . '-field.xml', false);
		}
		elseif (strpos($name, 'subform.params.listoptions') !== false)
		{
			$languages = LanguageHelper::createLanguageList(null, null, JPATH_BASE);

			foreach ($languages as $language)
			{
				$code = $language['value'];

				if ($code == 'en-GB')
				{
					continue;
				}

				$xml = <<<XML
				<form>
					<field
						name="{$code}"
						type="text"
						label="{$code}"
					/>
				</form>
XML;

				$form->load($xml);
			}
		}
		elseif ($name == 'com_sellacious.config')
		{
			// Inject plugin configuration into config form
			$form->loadFile(__DIR__ . '/' . $this->_name . '.xml', false, '//config');
		}

		return true;
	}

	/**
	 * Method is called right before an item is saved
	 *
	 * @param   string   $context  The calling context
	 * @param   \JTable  $table    A JTable object
	 * @param   bool     $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 *
	 * @throws  \Exception
	 */
	public function onContentBeforeSave($context, $table, $isNew)
	{
		if ($context == 'com_sellacious.field')
		{
			$data        = $table->getProperties(1);
			$params      = new Registry($data['params']);
			$listOptions = (array) $params->get('listoptions', array());

			if (!empty($listOptions))
			{
				$enGB = ArrayHelper::getColumn($listOptions, 'en-GB');
				$params->set('listoptions', $enGB);
				$translatedOptions = array();

				foreach ($listOptions as $listOption)
				{
					$listOption = (array) $listOption;
					$key        = $listOption['en-GB'];

					array_shift($listOption);

					$translatedOptions[$key] = $listOption;
				}

				$params->set('listoptions_translated', $translatedOptions);
			}

			$table->set('params', $params->toString());
		}
	}

	/**
	 * Adds attributes to dynamic rendering custom field xml element
	 *
	 * @param   string            $context   context name from where event was triggered.
	 * @param   object            $field     The field object loaded from fields table in the database.
	 * @param   SimpleXMLElement  $fieldXml  The xml element to which attributes to be added.
	 * @param   string            $language  Language to which XML field need to be translated to
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onRenderCustomField($context, $field, SimpleXMLElement &$fieldXml, $language = null)
	{
		$language = ($language && $language != '*') ? $language : JFactory::getLanguage()->getTag();
		$types    = $this->onFetchFieldTypes($context);

		if (!array_key_exists($field->type, $types))
		{
			return true;
		}

		$params     = new Registry($field->params);
		$attributes = $this->getAttributes($field->type);

		foreach ($attributes as $attribute => $default)
		{
			$value = $params->get($attribute, $default);
			$fieldXml->addAttribute($attribute, isset($value) ? is_array($value) ? implode(',', $value) : $value : $default);
		}

		// for list type fields we must add option elements as children
		$options           = (array) $params->get('listoptions');
		$translatedOptions = (array) $params->get('listoptions_translated', array());

		// This unique has to be added as for some unknown reason duplicate values are saved.
		foreach (array_unique($options) as $option)
		{
			if ($language != 'en-GB' && isset($translatedOptions[$option]->$language) && !empty($translatedOptions[$option]->$language))
			{
				$translated = $translatedOptions[$option]->$language;
				$opt_xml    = $fieldXml->addChild('option', htmlspecialchars($translated, ENT_COMPAT, 'UTF-8'));
			}
			else
			{
				$opt_xml = $fieldXml->addChild('option', htmlspecialchars($option, ENT_COMPAT, 'UTF-8'));
			}

			$opt_xml->addAttribute('value', htmlspecialchars($option, ENT_COMPAT, 'UTF-8'));
		}

		return true;
	}

	/**
	 * Adds attributes to  dynamic rendering custom field xml element
	 *
	 * @param   string  $context  Context name from where event was triggered.
	 * @param   object  $field    The field object loaded from fields table in the database.
	 * @param   string  $text     The resulting text/html to be rendered
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onRenderCustomFieldValue($context, $field, &$text)
	{
		$types = $this->onFetchFieldTypes($context);

		if (!array_key_exists($field->type, $types))
		{
			return true;
		}

		/** @note  $helper is used inside the layout(s), do not remove from here */
		$helper     = SellaciousHelper::getInstance();
		$layoutPath = JPluginHelper::getLayoutPath($this->_type, $this->_name, $field->type);

		if (file_exists($layoutPath))
		{
			ob_start();
			include $layoutPath;
			$text = ob_get_clean();
		}

		return true;
	}

	/**
	 * Get a list of choices for the filter form for a given custom field object
	 *
	 * @param   string    $context  Context name from where event was triggered.
	 * @param   stdClass  $field    The field object loaded from fields table in the database.
	 * @param   array     $tables   An associative array for FieldValue tables 'table_name' as 'key' and 'record_id'
	 *                              as value(s) for which to load the filters. If not specified all will be loaded.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function onFieldFilterOptions($context, $field, $tables = null)
	{
		$types = $this->onFetchFieldTypes($context);

		if (!array_key_exists($field->type, $types))
		{
			return array();
		}

		$params  = new Registry($field->params);
		// $options = $params->get('listoptions');

		// We currently load entered values from db for now. We may come across other robust way later in future.
		if (empty($options))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.field_value')
				->from($db->qn('#__sellacious_field_values', 'a'))
				->where('a.field_id = ' . $db->q($field->id))
				->where('a.is_json = 0');

			if (is_array($tables) && count($tables))
			{
				if (ArrayHelper::isAssociative($tables))
				{
					$where = array();

					foreach ($tables as $table => $pks)
					{
						$pks = (array) $pks;

						// Empty list would mean skip this table's records
						if (count($pks))
						{
							$where[] = sprintf('(a.table_name = %s AND a.record_id IN (%s))', $db->q($table), implode(', ', $db->quote($pks)));
						}
					}

					$query->where('(' . implode(' OR ', $where) . ')');
				}
				else
				{
					$query->where('a.table_name IN (' . implode(', ', $db->quote($tables)) . ')');
				}
			}

			$options = $db->setQuery($query)->loadColumn();
			$options = $options ? array_filter($options, 'trim') : array();
		}

		$options  = $options ? array_unique($options) : array();
		$sortable = $params->get('sortable');

		if ($sortable)
		{
			sort($options);
		}

		return $options;
	}

	/**
	 * JSON decode the field value as stored values are JSON encoded
	 *
	 * @param   stdClass  $choice
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	private function decode($choice)
	{
		$choice->value = json_decode($choice->value);

		return $choice;
	}

	/**
	 * Get list of all XML fields in plugin manifest file for the given field type
	 *
	 * To skip the fields from being included as an attribute, set formskip="true" in the field element
	 *
	 * @param   string  $field_type
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getAttributes($field_type)
	{
		$attributes = array();

		$filename = __DIR__ . '/forms/' . $field_type . '-field.xml';

		if (is_file($filename))
		{
			$xml      = simplexml_load_file($filename);
			$elements = $xml->xpath('//field');

			foreach ($elements as $element)
			{
				$skip    = (string) $element['formskip'];
				$name    = (string) $element['name'];
				$default = (string) $element['default'];

				if ($skip != 'true' && $name != 'listoptions')
				{
					$attributes[$name] = $default;
				}
			}
		}
		else
		{
			JLog::add(JText::sprintf('COM_SELLACIOUS_FIELD_XML_NOT_FOUND', $field_type), JLog::WARNING, 'jerror');
		}

		return $attributes;
	}
}
