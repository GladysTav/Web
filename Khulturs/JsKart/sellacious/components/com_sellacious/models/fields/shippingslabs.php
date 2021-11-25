<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.5.2
 */
class JFormFieldShippingSlabs extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $type = 'ShippingSlabs';

	/**
	 * The rule type.
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $ruleType = 'shippingrule';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @throws   Exception
	 *
	 * @since    1.5.2
	 */
	protected function getInput()
	{
		// May be we should also check for data structure of value. Skipping for now!
		$this->value = is_string($this->value) ? (json_decode($this->value, true) ?: array()) : (array) $this->value;

		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		$helper        = SellaciousHelper::getInstance();
		$ruleType      = $this->ruleType;
		$scope         = (string)$this->element['currency'];
		$precision     = (int)$this->element['precision'];
		$unitToggle    = (string)$this->element['unit_toggle'] == 'true';
		$percentage    = (string)$this->element['percentage_allowed'] == 'true';
		$roundToDigits = (string)$this->element['round_to_digits'] == 'false' || (string)$this->element['round_to_digits'] == '' ? 'false' : (int)$this->element['round_to_digits'] ;
		$useTable      = $this->element['useTable'] ? (int)$this->element['useTable'] : 0;
		$rowsLimit     = $this->element['rows_limit'] ? (int) $this->element['rows_limit'] : 0;

		if ($useTable)
		{
			$rowsLimit = 0;
		}

		if ($rowsLimit > 0)
		{
			$this->value = array_slice($this->value, 0, $rowsLimit);
		}

		if ($scope == 'global' || $scope == '')
		{
			$currency = $helper->currency->getGlobal('code_3');
		}
		elseif ($scope == 'current')
		{
			$currency = $helper->currency->current('code_3');
		}
		else
		{
			$userId   = $this->form->getValue($scope, null);
			$currency = $helper->currency->forUser($userId, 'code_3');
		}

		$ruleId  = $this->form->getValue('id', 0);
		$props   = get_object_vars($this);
		$options = array('client' => 2, 'debug' => 0);

		$props['useTable']  = $useTable;
		$props['rowsLimit'] = $rowsLimit;
		$rateColumn         = $this->element['rate_column'] ?: 'shipping';

		$data = (object) array_merge($props, array('currency' => $currency, 'precision' => $precision, 'unitToggle' => $unitToggle, 'percentage' => $percentage));
		$html = JLayoutHelper::render('com_sellacious.formfield.shippingslabs', $data, '', $options);

		$data->row_index = '##INDEX##';

		$tmpl = JLayoutHelper::render('com_sellacious.formfield.shippingslabs.rowtemplate', $data, '', $options);
		$tmpl = json_encode(preg_replace('/[\t\r\n]+/', '', $tmpl));
		$rows = count($this->value);

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.shippingslabs.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('stylesheet', 'com_sellacious/field.shippingslabs.css', array('version' => S_VERSION_CORE, 'relative' => true));

		$token = JSession::getFormToken();

		$doc   = JFactory::getDocument();
		$doc->addScriptDeclaration(<<<JS
			jQuery(document).ready(function () {
				var o = new JFormFieldShippingSlabs;
				o.setup({
					id : '{$this->id}',
					rowIndex : '{$rows}',
					rowsLimit : '{$rowsLimit}',
					token: '{$token}',
					rateColumn: '{$rateColumn}',
					ruleType: '{$ruleType}',
					ruleId: '{$ruleId}',
					roundToDigits: '{$roundToDigits}',
					rowTemplate : {
						html : {$tmpl},
						replacement : '##INDEX##'
					}
				});
			});
JS
		);

		return $html;
	}
}
