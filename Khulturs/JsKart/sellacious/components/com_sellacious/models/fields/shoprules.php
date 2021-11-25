<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for Shop rules and Shop rule class Groups.
 *
 */
class JFormFieldShopRules extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ShopRules';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$options   = array();
		$classType = (string) $this->element['class_type'];
		$sellerUid = (int) $this->element['seller_uid'];
		$helper    = SellaciousHelper::getInstance();
		$items     = $helper->shopRuleClass->getAllShopRulesClasses($classType);

		if (!empty($items))
		{
			$groups = ArrayHelper::getValue($items, 'groups', array());
			$rules  = ArrayHelper::getValue($items, 'rules', array());

			if (!empty($groups))
			{
				$grpTitle  = ucfirst($classType) . ' Classes';
				$options[] = JHtml::_('select.optgroup', $grpTitle);

				foreach ($groups as $group)
				{
					$options[] = JHtml::_('select.option', $group->id, $group->title, 'value', 'text');
				}

				$options[] = JHtml::_('select.optgroup', $grpTitle);
			}

			if (!empty($rules))
			{
				$ruleTitle = ucfirst($classType) . ' Rules';
				$options[] = JHtml::_('select.optgroup', $ruleTitle);

				foreach ($rules as $rule)
				{
					if  ($rule->seller_uid == 0 || ($sellerUid > 0 && $rule->seller_uid == $sellerUid))
					{
						$options[] = JHtml::_('select.option', $rule->id, $rule->title, 'value', 'text');
					}
				}

				$options[] = JHtml::_('select.optgroup', $ruleTitle);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
