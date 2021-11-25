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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious shop rule class helper
 */
class SellaciousHelperShopRuleClass extends SellaciousHelperBase
{
	/**
	 * Method to get both shop rules and classes applied on a product
	 *
	 * @param   int     $productId   The product Id
	 * @param   int     $sellerUid   The product seller user id
	 * @param   string  $type        Type of rule (Tax or discount)
	 * @param   int     $assignment  Type of assignment (for future purpose)
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.7.1
	 */
	public function getProductShopRuleClasses($productId, $sellerUid, $type, $assignment)
	{
		$code = $this->helper->product->getCode($productId, 0, $sellerUid);

		// Shop Rules Query
		$srQuery = $this->db->getQuery(true);
		$srQuery->select($this->db->qn(array('a.rule_id', 'a.rule_id', 'c.title'), array('idx', 'id', 'title')));
		$srQuery->from($this->db->qn('#__sellacious_rule_products', 'a'));
		$srQuery->join('left', $this->db->qn('#__sellacious_shoprule_class', 'b'). ' ON b.id = a.class_id');
		$srQuery->join('left', $this->db->qn('#__sellacious_shoprules', 'c') . ' ON c.id = a.rule_id');
		$srQuery->where('(a.product_id = ' . (int) $productId . ' OR a.product_id = ' . $this->db->q($code) . ')');
		$srQuery->where('a.class_id = 0 AND a.context = ' . $this->db->q('shoprule') . ' AND a.assignment = ' . $this->db->q($assignment));
		$srQuery->where('c.type = ' . $this->db->q($type));

		// Class Query
		$cQuery = $this->db->getQuery(true);
		$cQuery->select($this->db->qn(array('b.id', 'b.title', 'b.title'), array('idx', 'id', 'title')));
		$cQuery->from($this->db->qn('#__sellacious_rule_products', 'a'));
		$cQuery->join('left', $this->db->qn('#__sellacious_shoprule_class', 'b') . ' ON b.id = a.class_id');
		$cQuery->where('(a.product_id = ' . (int) $productId . ' OR a.product_id = ' . $this->db->q($code) . ')');
		$cQuery->where('a.class_id > 0 AND a.context = ' . $this->db->q('shoprule') . ' AND a.assignment = ' . $this->db->q($assignment));
		$cQuery->where('b.type = ' . $this->db->q($type));

		// Union of two queries
		$cQuery->union($srQuery);

		$this->db->setQuery($cQuery);

		$results = $this->db->loadObjectList();

		return $results;
	}

	/**
	 * Method to get all available classes
	 *
	 * @param   string  $type      Type of rule (tax, discount)
	 * @param   bool    $checkRef  Whether to check that the class has a reference for products
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.7.1
	 */
	public function getAllClasses($type, $checkRef = false)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		// Get Class Groups
		$query->select($db->qn(array('a.title', 'a.title'), array('title', 'id')))
			->from($db->qn('#__sellacious_shoprule_class', 'a'))
			->group($db->qn('a.alias'))
			->order($db->qn('a.title'));

		if ($checkRef)
		{
			$query->join('INNER', $db->qn('#__sellacious_class_shoprules_xref', 'b') . ' ON b.class_id = a.id');
			$query->join('LEFT', $db->qn('#__sellacious_shoprules', 'c') . ' ON c.id = b.shoprule_id');
			$query->where('c.state = 1');
		}

		$query->where('a.type = ' . $db->q($type));

		$db->setQuery($query);

		$groups = $db->loadObjectList();

		return $groups;
	}

	/**
	 * Method to get all available shop rules and shop rules classes/groups
	 *
	 * @param   string  $type  The type of rule (discount, tax)
	 *
	 * @return  array
	 *
	 * @since   1.7.1
	 */
	public function getAllShopRulesClasses($type)
	{
		$db     = $this->db;
		$groups = $this->getAllClasses($type, true);

		// Get Shop rules
		$filters = array(
			'list.select' => array('a.title', 'a.id'),
			'list.where'  => array(
				'a.type = ' . $db->q($type),
				'a.state = 1',
				'a.level > 0',
				'a.sum_method = 2',
				'a.apply_on_all_products = 0',
			),
		);
		$rules   = $this->helper->shopRule->loadObjectList($filters);
		$results = array('groups' => $groups, 'rules' => $rules);

		return $results;
	}

	/**
	 * Set selected rule to given related shop rule class groups, REMOVING from other class groups
	 *
	 * @param   int       $rule_id  Target shop rule Id
	 * @param   string[]  $classes  Array of class group titles
	 * @param   string    $type     Rule Type
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	public function setShopRule($rule_id, $classes, $type)
	{
		if (empty($classes))
		{
			return;
		}

		$classes  = array_filter($classes, 'trim');
		$classIds = array();

		foreach ($classes as $group)
		{
			$table = $this->getTable();
			$data = array('title' => $group, 'type' => $type);
			$table->load($data);

			if (empty($table->get('id')))
			{
				$table->bind($data);
				$table->check();
				$table->store();
			}

			$classIds[] = $table->get('id');
		}

		// Save Shoprule to class
		$this->setClasses($rule_id, $classIds, $type);
	}

	/**
	 * Method to get Class Name from Alias
	 *
	 * @param   string  $alias   The alias of class
	 * @param   string  $select  Name of the column to select
	 *
	 * @return  mixed
	 *
	 * @since   1.7.1
	 */
	public function getClassFromAlias($alias, $select = 'title')
	{
		return $this->loadResult(array('list.select' => array('a.' . $select), 'list.where' => array('a.alias = ' . $this->db->q($alias))));
	}

	/**
	 * Method to get Alias from Class Name
	 *
	 * @param   string  $class   The Class Name
	 * @param   string  $column  Name of the column to select
	 *
	 * @return  mixed
	 *
	 * @since   1.7.1
	 */
	public function getAliasFromClass($class, $column = 'title')
	{
		$alias = $this->loadResult(array('list.select' => array('a.alias'), 'list.where' => array('a.' . $column . ' = ' . $this->db->q($class))));

		if (empty($alias) && $column == 'title')
		{
			$alias = JFilterOutput::stringURLSafe($class);

			if (trim(str_replace('-', '', $alias)) == '')
			{
				$alias = base64_encode(strtolower($class));
			}
		}

		return $alias;
	}

	/**
	 * Retrieve a list of all class ids that a given shoprule belongs to
	 *
	 * @param   int     $shopRuleId  Shop Rule id
	 * @param   string  $type        Rule Type
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	public function getShopRuleClassIds($shopRuleId, $type)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('c.class_id')
			->from($db->qn('#__sellacious_class_shoprules_xref', 'c'))
			->join('left', $db->qn('#__sellacious_shoprule_class', 'b') . ' ON b.id = c.class_id')
			->where('c.shoprule_id = ' . $db->q($shopRuleId) . ' AND b.type = ' . $this->db->q($type));

		$db->setQuery($query);
		$classIds = $db->loadColumn();

		return (array) $classIds;
	}

	/**
	 * Method to get class records that a give shoprule belongs to
	 *
	 * @param   int     $shopRuleId  Shop Rule id
	 * @param   string  $type        Rule Type
	 *
	 * @return  stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	public function getShopRuleClasses($shopRuleId, $type)
	{
		$classIds = $this->getShopRuleClassIds($shopRuleId, $type);

		if (empty($classIds))
		{
			return array();
		}

		$query = $this->db->getQuery(true);
		$query->select('a.id, a.title, a.alias');
		$query->from($this->db->qn('#__sellacious_shoprule_class', 'a'));
		$query->where('a.id IN (' . implode(',', $classIds) . ')');
		$query->where('a.type = ' . $this->db->q($type));

		$this->db->setQuery($query);

		$classes = $this->db->loadObjectList();

		return $classes;
	}

	/**
	 * Assign selected class id to given shop rules un-assign from others
	 *
	 * @param   int        $shopRuleId  Shop Rule Id
	 * @param   int|int[]  $classIds    Target class ids, other associations will be removed
	 * @param   string     $type        Rule Type
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	public function setClasses($shopRuleId, $classIds, $type)
	{
		$current  = $this->getShopRuleClassIds($shopRuleId, $type);
		$classIds = (array) $classIds;

		$remove = array_diff($current, $classIds);
		$addNew = array_diff($classIds, $current);

		$this->removeClassIds($shopRuleId, $remove);
		$this->addClassIds($shopRuleId, $addNew);
	}

	/**
	 * Method to remove classes from a shop rule
	 *
	 * @param   int      $shopRuleId  Shop Rule id in concern
	 * @param   int[]    $classes     Classes to remove
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	protected function removeClassIds($shopRuleId, $classes)
	{
		$classes = ArrayHelper::toInteger((array) $classes);

		if (count($classes) == 0)
		{
			return;
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__sellacious_class_shoprules_xref')
			->where('shoprule_id = ' . $this->db->q($shopRuleId))
			->where($this->db->qn('class_id') . ' IN (' . implode(',', $this->db->q($classes)) . ')');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to add class ids to a shop rule, in addition to any existing classes
	 *
	 * @param   int       $shopRuleId Shop rule id in concern
	 * @param   int|int[] $classIds   Class id or array of it to be removed
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	protected function addClassIds($shopRuleId, $classIds)
	{
		$classIds = ArrayHelper::toInteger((array) $classIds);

		if (count($classIds) == 0)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->insert('#__sellacious_class_shoprules_xref')
			->columns(array('shoprule_id', 'class_id'));

		foreach ($classIds as $class_id)
		{
			$filters = array(
				'list.from'   => '#__sellacious_class_shoprules_xref',
				'shoprule_id'  => $shopRuleId,
				'class_id' => $class_id,
			);

			if (!$this->count($filters))
			{
				$query->values($db->q($shopRuleId) . ', ' . $db->q($class_id));
			}
		}

		$db->setQuery($query)->execute();
	}
}
