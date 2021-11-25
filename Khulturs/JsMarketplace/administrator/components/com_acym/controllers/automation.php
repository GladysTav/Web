<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class AutomationController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_AUTOMATION')] = acym_completeLink('automation');
    }

    public function listing()
    {
        if (acym_level(2)) {
            acym_setVar('layout', 'listing');
            $pageIdentifier = 'automation';

            $searchFilter = acym_getVar('string', 'automation_search', '');
            $tagFilter = acym_getVar('string', 'automation_tag', '');
            $ordering = acym_getVar('string', 'automation_ordering', 'name');
            $orderingSortOrder = acym_getVar('string', 'automation_ordering_sort_order', 'asc');

            $automationsPerPage = acym_getCMSConfig('list_limit', 20);
            $page = acym_getVar('int', 'automation_pagination_page', 1);

            $automationClass = acym_get('class.automation');
            $matchingAutomations = $automationClass->getMatchingAutomations(
                array(
                    'ordering' => $ordering,
                    'search' => $searchFilter,
                    'automationsPerPage' => $automationsPerPage,
                    'offset' => ($page - 1) * $automationsPerPage,
                    'tag' => $tagFilter,
                    'ordering_sort_order' => $orderingSortOrder,
                )
            );

            $pagination = acym_get('helper.pagination');
            $pagination->setStatus($matchingAutomations['total'], $page, $automationsPerPage);

            $data = array(
                'allAutomations' => $matchingAutomations['automations'],
                'allTags' => acym_get('class.tag')->getAllTagsByType('automation'),
                'pagination' => $pagination,
                'search' => $searchFilter,
                'ordering' => $ordering,
                'tag' => $tagFilter,
                'orderingSortOrder' => $orderingSortOrder,
            );

            parent::display($data);
        }

        if (acym_level(0) && !acym_level(2)) {
            acym_redirect(acym_completeLink('dashboard&task=upgrade&version=enterprise', false, true));
        }
    }
}
