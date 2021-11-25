<?php
/**
 * @version     1.0.1
 * @package     mod_sellacious_offeredproducts
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the mod_sellacious_offeredproducts MainProduct.
 *
 * @since   1.6
 */
class JFormFieldMainProduct extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'MainProduct';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// This may be called from outer context so load helpers explicitly.
		jimport('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			JFactory::getApplication()->enqueueMessage('Sellacious Library was not found.', 'error');

			return parent::getOptions();
		}

		$helper = SellaciousHelper::getInstance();

		$filters                  = array();
		$filters['list.select'][] = ' a.id, a.title';

		$filters['list.join'] = array(
			array('inner', '#__sellacious_product_prices AS p ON p.product_id = a.id'),
		);

        $filters['list.where'][] = 'a.state = 1';
        $filters['list.where'][] = 'p.edate > NOW()';
        $filters['list.where'][] = 'p.qty_min <= 1';

		$filters['list.group'][] = 'p.product_id';

        //$filters['list.where'][] = '(p.edate >= NOW() OR p.edate ="0000-00-00 00:00:00")';
		//$filters['list.group'][] = 'p.product_id HAVING COUNT(p.id) > 1';

		$items   = $helper->product->loadObjectList($filters);
		$options = array();

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, $item->title, 'value', 'text');
		}

		return array_merge(parent::getOptions(), $options);
	}
}
