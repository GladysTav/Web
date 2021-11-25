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

class plgAcymUser extends acymPlugin
{
    var $sendervalues = array();

    function __construct()
    {
        parent::__construct();

        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;
    }

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation_sprintf('ACYM_CMS_USER', 'Joomla');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-taguser';

        return $onePlugin;
    }

    function textPopup()
    {
        ?>

        <script language="javascript" type="text/javascript">
            <!--
            var selectedTag;
            function changeUserTag(tagname){
                if(!tagname) return;

                selectedTag = tagname;

                var string;
                var iscf = tagname.toLowerCase().indexOf('custom');

                if(iscf >= 0) string = '{usertag:' + tagname.substr(0, iscf) + '|type:custom';else string = '{usertag:' + tagname;

                if(tagname.toLowerCase().indexOf('date') >= 0) string += '|type:date';
                string += '|info:' + $('input[name="typeinfo"]:checked').val() + '}';

                setTag(string, $('#' + tagname + 'option'));
            }
            -->
        </script>

        <?php
        $text = '<div class="acym__popup__listing text-center grid-x">';

        $typeinfo = array();
        $typeinfo[] = acym_selectOption("receiver", acym_translation('ACYM_RECEIVER_INFORMATION'));
        $typeinfo[] = acym_selectOption("sender", acym_translation('ACYM_SENDER_INFORMATION'));

        $text .= acym_radio($typeinfo, 'typeinfo', 'receiver', null, array('onclick' => 'changeUserTag(selectedTag)'));

        $fields = array(
            $this->cmsUserVars->username => 'ACYM_LOGIN_NAME',
            $this->cmsUserVars->name => 'ACYM_USER_NAME',
            $this->cmsUserVars->registered => 'ACYM_REGISTRATION_DATE',
            'groups' => 'ACYM_USER_GROUPS',
        );

        foreach ($fields as $fieldname => $description) {
            $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" id="'.$fieldname.'option" onclick="changeUserTag(\''.$fieldname.'\');" >
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$fieldname.'</div>
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.acym_translation($description).'</div>
                     </div>';
        }

        if ('Joomla' == 'Joomla' && ACYM_J37) {
            $groups = acym_loadObjectList('SELECT id, title FROM #__fields_groups WHERE context = "com_users.user" AND state = 1 ORDER BY title ASC');
            $defaultGroup = new stdClass();
            $defaultGroup->id = 0;
            $defaultGroup->title = acym_translation('ACYM_NO_GROUP');
            array_unshift($groups, $defaultGroup);

            $customFields = acym_loadObjectList('SELECT id, title, group_id FROM #__fields WHERE context = "com_users.user" AND state = 1 ORDER BY title ASC');
            if (!empty($customFields)) {
                $text .= '<h1 class="acym__popup__plugin__title cell" style="margin-top: 20px;">'.acym_translation('ACYM_CUSTOM_FIELDS').'</h1>';

                foreach ($groups as $oneGroup) {
                    foreach ($customFields as $oneCF) {
                        if ($oneCF->group_id != $oneGroup->id) {
                            continue;
                        }
                        $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" id="'.$oneCF->id.'customoption" onclick="changeUserTag(\''.$oneCF->id.'custom\');" >
                                    <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$oneCF->title.'</div>
                                 </div>';
                    }
                }
                $text .= '</table></div>';
            }
        }

        $text .= '</div>';
        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'usertag');
        if (empty($extractedTags)) {
            return;
        }

        if (empty($this->customFields) && 'Joomla' == 'Joomla' && ACYM_J37) {
            $this->customFields = acym_loadObjectList('SELECT * FROM #__fields WHERE context = "com_users.user"', 'id');
            foreach ($this->customFields as &$oneCF) {
                if (!empty($oneCF->fieldparams)) {
                    $oneCF->fieldparams = json_decode($oneCF->fieldparams, true);
                }
            }
        }

        $tags = array();
        $receivervalues = array();
        foreach ($extractedTags as $i => $mytag) {
            if (isset($tags[$i])) {
                continue;
            }
            $mytag->default = '';

            $values = new stdClass();
            $idused = 0;
            $save = false;

            if (!empty($mytag->info) && $mytag->info == 'sender' && !empty($email->creator_id)) {
                $idused = $email->creator_id;
                $save = true;
            }

            if (!empty($mytag->info) && $mytag->info == 'current') {
                $currentUserid = acym_currentUserId();
                if (!empty($currentUserid)) {
                    $idused = $currentUserid;
                }
            }

            if ((empty($mytag->info) || $mytag->info == 'receiver') && !empty($user->cms_id)) {
                $idused = $user->cms_id;
            }

            if (!empty($idused) && empty($this->sendervalues[$idused]) && empty($receivervalues[$idused])) {
                $receivervalues[$idused] = acym_loadObject('SELECT * FROM '.$this->cmsUserVars->table.' WHERE '.$this->cmsUserVars->id.' = '.intval($idused).' LIMIT 1');

                if ($save) {
                    $this->sendervalues[$idused] = $receivervalues[$idused];
                }
            }

            if (!empty($this->sendervalues[$idused])) {
                $values = $this->sendervalues[$idused];
            } elseif (!empty($receivervalues[$idused])) {
                $values = $receivervalues[$idused];
            }

            if ($mytag->id == 'groups') {
                $groups = acym_getGroupsByUser($idused, true, true);
                $values->groups = implode(', ', $groups);
            }

            if (empty($mytag->type)) {
                $mytag->type = '';
            }

            if ($mytag->type == 'custom' && 'Joomla' == 'Joomla') {
                $mytag->id = intval($mytag->id);
                if (empty($mytag->id)) {
                    $replaceme = '';
                } else {
                    $userFieldVals = acym_loadResultArray('SELECT value FROM #__fields_values WHERE item_id = '.intval($idused).' AND field_id = '.intval($mytag->id));

                    $fieldValues = trim(implode(', ', $userFieldVals), ', ');
                    if (empty($fieldValues)) {
                        $defaultValue = acym_loadObject('SELECT default_value, type FROM #__fields WHERE id = '.intval($mytag->id));
                        if (($defaultValue->type == 'user' && !empty($defaultValue->default_value)) || ($defaultValue->type != 'user' && strlen($defaultValue->default_value) > 0)) {
                            $userFieldVals = array($defaultValue->default_value);
                        }
                    }

                    foreach ($userFieldVals as &$oneFieldVal) {
                        switch ($this->customFields[$mytag->id]->type) {
                            case 'radio':
                            case 'list':
                            case 'checkboxes':
                                foreach ($this->customFields[$mytag->id]->fieldparams['options'] as $oneOPT) {
                                    if ($oneOPT['value'] == $oneFieldVal) {
                                        $oneFieldVal = $oneOPT['name'];
                                        break;
                                    }
                                }
                                break;

                            case 'usergrouplist':
                                if (empty($this->usergroups)) {
                                    $this->usergroups = acym_loadObjectList('SELECT id, title FROM #__usergroups', 'id');
                                }

                                $oneFieldVal = $this->usergroups[$oneFieldVal]->title;
                                break;

                            case 'imagelist':
                                if (strlen($this->customFields[$mytag->id]->fieldparams['directory']) > 1) {
                                    $oneFieldVal = '/'.$oneFieldVal;
                                } else {
                                    $this->customFields[$mytag->id]->fieldparams['directory'] = '';
                                }
                                $oneFieldVal = '<img src="images/'.$this->customFields[$mytag->id]->fieldparams['directory'].$oneFieldVal.'" />';
                                break;

                            case 'url':
                                $oneFieldVal = '<a target="_blank" href="'.$oneFieldVal.'">'.$oneFieldVal.'</a>';
                                break;

                            case 'sql':
                                if (empty($this->customFields[$mytag->id]->options)) {
                                    $this->customFields[$mytag->id]->options = acym_loadObjectList($this->customFields[$mytag->id]->fieldparams['query'], 'value');
                                }

                                $oneFieldVal = $this->customFields[$mytag->id]->options[$oneFieldVal]->text;
                                break;

                            case 'user':
                                $oneFieldVal = acym_currentUserName($oneFieldVal);
                                break;

                            case 'media':
                                $oneFieldVal = '<img src="'.$oneFieldVal.'" />';
                                break;

                            case 'calendar':
                                $format = $this->customFields[$mytag->id]->fieldparams['showtime'] == '1' ? 'Y-m-d H:i' : 'Y-m-d';
                                $oneFieldVal = acym_date(strtotime($oneFieldVal), $format);
                                break;
                        }
                    }

                    $replaceme = implode(', ', $userFieldVals);
                }
            } else {
                $replaceme = isset($values->{$mytag->id}) ? $values->{$mytag->id} : $mytag->default;
            }

            $tags[$i] = $replaceme;
            $this->acympluginHelper->formatString($tags[$i], $mytag);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }
}

