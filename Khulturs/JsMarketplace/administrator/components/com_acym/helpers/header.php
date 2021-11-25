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

class acymheaderHelper
{
    function display($breadcrumb)
    {
        $links = array();
        foreach ($breadcrumb as $oneLevel => $link) {
            if (!empty($link)) {
                $oneLevel = '<a href="'.$link.'">'.$oneLevel.'</a>';
            }
            $links[] = '<li>'.$oneLevel.'</li>';
        }

        if (count($links) > 1) {
            $links[count($links) - 1] = str_replace('<li>', '<li class="last_link"><span class="show-for-sr">Current: </span>', $links[count($links) - 1]);
        }

        $header = '<div id="acym_header" class="grid-x hide-for-small-only margin-bottom-1">';

        $header .= '<i class="cell medium-shrink acym-logo"></i>';

        $header .= '<div id="acym_global_navigation" class="cell medium-auto"><nav aria-label="You are here:" role="navigation"><ul class="breadcrumbs">';
        $header .= implode('', $links);
        $header .= '</ul></nav></div>';
        $header .= '<a type="button" class="button medium-shrink acym_feedback" target="_blank" href="'.ACYM_UPDATEMEURL.'doc&task=doc&product=acymailing&for='.(empty($_REQUEST['ctrl']) ? 'dashboard' : $_REQUEST['ctrl']).'-'.$_REQUEST['layout'].'">'.acym_translation('ACYM_DOCUMENTATION').'</a>';
        $header .= '</div>';

        return $header;
    }
}
