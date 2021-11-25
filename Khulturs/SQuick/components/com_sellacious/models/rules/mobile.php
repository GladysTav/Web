<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Sellacious\Config\ConfigHelper;

/**
 * Form Rule class for the Joomla Platform
 *
 * @since   2.0.0
 */
class JFormRuleMobile extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $regex = '';

	/**
	 * Method to test the email address and optionally check for uniqueness.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object
	 * @param   mixed             $value    The form field value to validate
	 * @param   string            $group    The field name group control value. This acts as an array container for the field
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]"
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form
	 * @param   Form              $form     The form object for which the field is being tested
	 *
	 * @return  mixed  Boolean true if field value is valid, Exception on failure
	 *
	 * @since   2.0.0
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		try
		{
			$config      = ConfigHelper::getInstance('com_sellacious');
			$this->regex = $config->get('address_mobile_regex');

			if ($this->regex && !parent::test($element, $value, $group, $input, $form))
			{
				return false;
			}
		}
		catch (Exception $e)
		{
		}

		if ((string) $element['unique'] == 'true')
		{
			$userId = $form instanceof Form ? $input->get('id') : '';
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);

			$query->select('COUNT(*)')->from('#__sellacious_profiles')
				->where('mobile = ' . $db->quote($value))->where('user_id <> ' . (int) $userId);

			$result = (bool) $db->setQuery($query)->loadResult();

			if ($result)
			{
				return false;
			}
		}

		return true;
	}
}
