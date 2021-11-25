<?php
/**
 * @version     2.0.0
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni Thakur <info@bhartiy.com> - http://www.bhartiy.com
 */

class ModSellaciousCategoriesHelper
{
    /**
     * Get categories
     *
     * @param   array       $categories
     * @param   bool        $showSubCategories
     * @param   string      $orderBy
     *
     * @return  mixed
     * @throws  Exception
     *
     * @since   1.7.0
     */
    public static function getCategories($categories, $showSubCategories = 0, $orderBy = '')
    {
        $helper      = SellaciousHelper::getInstance();
        $categoryList = array();

        if (!empty($categories))
        {
            $categoryIds = array();

            foreach ($categories as $category)
            {
                if ($showSubCategories)
                {
                    $categoryIds[] = $helper->category->getChildren($category, true);
                }
            }

            $categoryIds = array_reduce($categoryIds, 'array_merge', array());

            if (empty($categoryIds))
            {
                $categoryIds = $categories;
            }

            if (!empty($categoryIds))
            {
                $filter = array(
                    'list.select' => 'a.id, a.title',
                    'state'       => 1,
                    'id'          => $categoryIds
                );

                if ($orderBy !== '')
                {
                	$filter = array_merge($filter, array('list.order' => $orderBy));
                }

                $categoryList = $helper->category->loadObjectList($filter);
            }
        }

        foreach ($categoryList as $item)
        {
            $item->subcat_count  = 0;
            $item->product_count = 0;

            $children = $helper->category->getChildren($item->id, false, array('a.state = 1'));

            $item->subcat_count  = count($children);
            $item->product_count = static::getCountItems($item->id, false);
        }

        return $categoryList;
    }

    /**
     * Get total number of items/references within a selected category, optionally including
     *
     * @param   int   $category_id  Category being queried
     * @param   bool  $this_only    Do not include the sub categories
     *
     * @return  int
     * @throws  Exception
     *
     * @since   1.7.0
     */
    public static function getCountItems($category_id, $this_only = false)
    {
        $helper      = SellaciousHelper::getInstance();
        $items = $this_only ? array($category_id) : $helper->category->getChildren($category_id, true);

        if (count($items) == 0)
        {
            return 0;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $helper      = SellaciousHelper::getInstance();

        $query->select('COUNT(DISTINCT pc.product_id)')
            ->from('#__sellacious_product_categories pc')
            ->where('pc.category_id IN (' . implode(', ', $db->quote($items)) . ')')
            ->join('inner', '#__sellacious_categories c ON c.id = pc.category_id')
            ->join('inner', '#__sellacious_products p ON p.id = pc.product_id AND p.state = 1');

        if ($helper->config->get('multi_variant') == 2)
        {
            $query->clear('select')
                ->select('COUNT(DISTINCT pc.product_id) + COUNT(DISTINCT v.id)')
                ->join('left', '#__sellacious_variants v ON v.product_id = pc.product_id AND v.state = 1');
        }

        return (int) $db->setQuery($query)->loadResult();
    }
}
