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

class plgAcymTime extends acymPlugin
{
    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_TIME');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-time';

        return $onePlugin;
    }

    function textPopup()
    {
        $text = '<div class="acym__popup__listing text-center grid-x">
                    <h1 class="acym__popup__plugin__title cell">'.acym_translation('ACYM_TIME_FORMAT').'</h1>';

        $others = array();
        $others['{date:1}'] = 'ACYM_DATE_FORMAT_LC1';
        $others['{date:2}'] = 'ACYM_DATE_FORMAT_LC2';
        $others['{date:3}'] = 'ACYM_DATE_FORMAT_LC3';
        $others['{date:4}'] = 'ACYM_DATE_FORMAT_LC4';
        $others['{date:%m/%d/%Y}'] = '%m/%d/%Y';
        $others['{date:%d/%m/%y}'] = '%d/%m/%y';
        $others['{date:%A}'] = '%A';
        $others['{date:%B}'] = '%B';


        $k = 0;
        foreach ($others as $tagname => $tag) {
            $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\''.$tagname.'\', $(this));" >
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag.'</div>
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.acym_getDate(time(), acym_translation($tag)).'</div>
                     </div>';
            $k = 1 - $k;
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceContent(&$email, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'date');
        if (empty($extractedTags)) {
            return;
        }

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }

            $time = time();
            if (!empty($oneTag->senddate) && !empty($email->sending_date)) {
                $time = $email->sending_date;
            }
            if (!empty($oneTag->add)) {
                $time += intval($oneTag->add);
            }
            if (!empty($oneTag->remove)) {
                $time -= intval($oneTag->remove);
            }

            if (empty($oneTag->id) || is_numeric($oneTag->id)) {
                $oneTag->id = acym_translation('ACYM_DATE_FORMAT_LC'.$oneTag->id);
            }

            $tags[$i] = acym_getDate($time, $oneTag->id);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }
}//endclass

