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

class acymautomationClass extends acymClass
{
    public function getMatchingAutomations($settings)
    {
        $query = 'SELECT automation.* FROM #__acym_automation AS automation';
        $queryCount = 'SELECT COUNT(automation.id) FROM #__acym_automation AS automation';
        $filters = array();

        if (!empty($settings['search'])) {
            $filters[] = 'automation.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY automation.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }
        $results['automations'] = acym_loadObjectList($query, '', $settings['offset'], $settings['automationsPerPage']);


        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }
}
