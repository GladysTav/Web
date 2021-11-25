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

class plgAcymSubscriber extends acymPlugin
{
    var $fields = array();

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_SUBSCRIBER');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-subscriber';

        return $onePlugin;
    }

    function textPopup()
    {
        $fieldClass = acym_get('class.field');
        $fieldsUser = acym_getColumns('user');
        $fieldsStats = acym_getColumns('user_stat');
        $fields = array_merge($fieldsUser, $fieldsStats);
        $customFields = $fieldClass->getAllFieldsForUser();
        $descriptions = array();

        foreach ($customFields as $one) {
            $descriptions[$one->id] = acym_translation('ACYM_CUSTOM_FIELD');
            $fields[] = $one->id;
        }


        $descriptions['id'] = acym_translation('ACYM_USER_ID');
        $descriptions['email'] = acym_translation('ACYM_USER_EMAIL');
        $descriptions['name'] = acym_translation('ACYM_USER_NAME');
        $descriptions['cms_id'] = acym_translation('ACYM_USER_CMSID');
        $descriptions['source'] = acym_translation('ACYM_USER_SOURCE');
        $descriptions['confirmed'] = acym_translation('ACYM_USER_CONFIRMED');
        $descriptions['active'] = acym_translation('ACYM_USER_ACTIVE');
        $descriptions['creation_date'] = acym_translation('ACYM_USER_CREATION_DATE');
        $descriptions['open_date'] = acym_translation('ACYM_USER_OPEN_DATE');
        $descriptions['date_click'] = acym_translation('ACYM_USER_CLICK_DATE');
        $descriptions['send_date'] = acym_translation('ACYM_USER_SEND_DATE');

        $text = '<div class="acym__popup__listing text-center grid-x">
					<h1 class="acym__popup__plugin__title cell">'.acym_translation('ACYM_RECEIVER_INFORMATION').'</h1>
					';

        $others = array();
        $others['{subtag:name|part:first|ucfirst}'] = array('name' => acym_translation('ACYM_USER_FIRSTPART'), 'desc' => acym_translation('ACYM_USER_FIRSTPART_DESC'));
        $others['{subtag:name|part:last|ucfirst}'] = array('name' => acym_translation('ACYM_USER_LASTPART'), 'desc' => acym_translation('ACYM_USER_LASTPART_DESC'));

        foreach ($others as $tagname => $tag) {
            $text .= '<div style="cursor:pointer" class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\''.$tagname.'\', $(this));" ><div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['name'].'</div><div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['desc'].'</div></div>';
        }

        foreach ($fields as $fieldname) {
            if (empty($descriptions[$fieldname])) {
                continue;
            }

            $type = '';
            if (in_array($fieldname, array('creation_date', 'open_date', 'date_click', 'send_date'))) {
                $type = '|type:time';
            }

            $text .= '<div style="cursor:pointer" class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\'{subtag:'.(empty($customFields[$fieldname]) ? $fieldname.$type : 'custom,'.$customFields[$fieldname]->id).'}\', $(this));" >
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.(empty($customFields[$fieldname]) ? $fieldname : $customFields[$fieldname]->name).'</div>
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$descriptions[$fieldname].'</div>
                     </div>';
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'subtag');
        if (empty($extractedTags)) {
            return;
        }

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }
            $tags[$i] = $this->replaceSubTag($oneTag, $user);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    private function replaceSubTag(&$mytag, $user)
    {
        $fieldClass = acym_get('class.field');
        $field = $mytag->id;
        if (strpos($mytag->id, 'custom') === false) {
            $replaceme = (isset($user->$field) && strlen($user->$field) > 0) ? $user->$field : $mytag->default;
        } else {
            $value = $fieldClass->getAllfieldBackEndListingByUserIds($user->id, explode(',', $mytag->id)[1]);

            $replaceme = empty($value) ? '' : $value[explode(',', $mytag->id)[1].$user->id];
        }
        $replaceme = nl2br($replaceme);

        $this->acympluginHelper->formatString($replaceme, $mytag);

        return $replaceme;
    }
}

