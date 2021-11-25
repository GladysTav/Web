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

class acymView
{
    var $name = '';
    var $steps = array();
    var $step = '';
    var $edition = false;

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;

        $classname = get_class($this);
        $viewpos = strpos($classname, 'View');
        $this->name = strtolower(substr($classname, $viewpos + 4));
        $this->step = acym_getVar('string', 'nextstep', '');
        if (empty($this->step)) {
            $this->step = acym_getVar('string', 'step', '');
        }
        $this->edition = acym_getVar('string', 'edition', '0') === '1';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLayout()
    {
        return acym_getVar('string', 'layout', acym_getVar('string', 'task', 'listing'));
    }

    public function setLayout($value)
    {
        acym_setVar('layout', $value);
    }

    public function display($data = array())
    {
        $view = $this->getLayout();

        if (method_exists($this, $view)) {
            $this->$view();
        }

        $viewFolder = acym_isAdmin() ? ACYM_VIEW : ACYM_VIEW_FRONT;
        if (!file_exists($viewFolder.$this->getName().DS.'tmpl'.DS.$view.'.php')) {
            $view = 'listing';
        }

        if ('Joomla' == 'WordPress') {
            echo ob_get_clean();
        }

        if (!empty($_SESSION['acynotif'])) {
            echo implode('', $_SESSION['acynotif']);
            $_SESSION['acynotif'] = array();
        }

        $outsideForm = $this->getName() == 'mails' && $view == 'edit';
        if ($outsideForm) {
            echo '<form id="acym_form" action="'.acym_completeLink(acym_getVar('cmd', 'ctrl')).'" method="post" name="acyForm" data-abide novalidate>';
        }

        echo '<div id="acym_wrapper" class="'.$this->getName().'_'.$view.'">';

        if ('Joomla' == 'Joomla' && !ACYM_J40 && acym_isAdmin() && acym_getVar('string', 'tmpl') != 'component') {
            echo $this->joomlaLeftMenu().'<div id="acym_content">';
        }

        if (!empty($data['header'])) {
            echo $data['header'];
        }

        acym_displayMessages();

        echo '<div id="acym__callout__container"></div>';

        if ('Joomla' == 'Joomla') {
            $app = JFactory::getApplication();
            $overridePath = (acym_isAdmin() ? JPATH_ADMINISTRATOR : JPATH_SITE).DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_acym'.DS.$this->getName().DS.$view.'.php';
        }

        if (!empty($overridePath) && file_exists($overridePath)) {
            include($overridePath);
        } else {
            include($viewFolder.$this->getName().DS.'tmpl'.DS.$view.'.php');
        }

        if ('Joomla' == 'Joomla' && !ACYM_J40 && acym_isAdmin() && acym_getVar('string', 'tmpl') != 'component') {
            echo '</div>';
        }
        echo '</div>';

        if ($outsideForm) {
            echo '</form>';
        }
    }

    public function escape($value)
    {
        return htmlspecialchars($value, ENT_COMPAT, "UTF-8");
    }

    private function joomlaLeftMenu()
    {
        $isCollapsed = empty($_COOKIE['menuJoomla']) ? '' : $_COOKIE['menuJoomla'];

        $menus = array(
            'dashboard' => array('title' => 'ACYM_DASHBOARD', 'class-i' => 'material-icons', 'text-i' => 'dashboard', 'span-class' => ''),
            'users' => array('title' => 'ACYM_USERS', 'class-i' => 'material-icons', 'text-i' => 'group', 'span-class' => ''),
            'fields' => array('title' => 'ACYM_CUSTOM_FIELDS', 'class-i' => 'material-icons', 'text-i' => '	text_fields', 'span-class' => ''),
            'lists' => array('title' => 'ACYM_LISTS', 'class-i' => 'fa fa-address-book-o', 'text-i' => '', 'span-class' => 'acym__joomla__left-menu__fa'),
            'campaigns' => array('title' => 'ACYM_CAMPAIGNS', 'class-i' => 'material-icons', 'text-i' => 'email', 'span-class' => ''),
            'mails' => array('title' => 'ACYM_TEMPLATES', 'class-i' => 'fa fa-pencil-square-o', 'text-i' => '', 'span-class' => 'acym__joomla__left-menu__fa'),
            'automation' => array('title' => 'ACYM_AUTOMATION', 'class-i' => 'fa fa-gears', 'text-i' => '', 'span-class' => 'acym__joomla__left-menu__fa'),
            'queue' => array('title' => 'ACYM_QUEUE', 'class-i' => 'fa fa-hourglass-half', 'text-i' => '', 'span-class' => 'acym__joomla__left-menu__fa'),
            'stats' => array('title' => 'ACYM_STATISTICS', 'class-i' => 'fa fa-bar-chart', 'text-i' => '', 'span-class' => 'acym__joomla__left-menu__fa'),
            'configuration' => array('title' => 'ACYM_CONFIGURATION', 'class-i' => 'material-icons', 'text-i' => 'settings', 'span-class' => ''),
        );

        $leftMenu = '<div id="acym__joomla__left-menu--show"><i class="acym-logo"></i><i id="acym__joomla__left-menu--burger" class="material-icons">menu</i></div>
                    <div id="acym__joomla__left-menu" class="'.$isCollapsed.'">
                        <i class="material-icons" id="acym__joomla__left-menu--close">close</i>';
        foreach ($menus as $oneMenu => $menuOption) {
            $class = $this->getName() == $oneMenu ? "acym__joomla__left-menu--current" : "";
            $leftMenu .= '<a href="'.acym_completeLink($oneMenu).'" class="'.$class.'"><i class="'.$menuOption['class-i'].'">'.$menuOption['text-i'].'</i><span class="'.$menuOption['span-class'].'">'.acym_translation($menuOption['title']).'</span></a>';
        }

        $leftMenu .= '<a href="#" id="acym__joomla__left-menu--toggle"><i class="material-icons">keyboard_arrow_left</i><span>'.acym_translation('ACYM_COLLAPSE').'</span></a>';

        $leftMenu .= '</div>';

        return $leftMenu;
    }
}
