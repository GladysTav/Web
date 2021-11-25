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

class plgAcymSubscription extends acymPlugin
{
    var $listunsubscribe = false;
    var $lists = array();
    var $listsowner = array();
    var $listsinfo = array();
    var $campaigns = array();
    var $unsubscribeLink = false;

    function __construct()
    {
        parent::__construct();

        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;
    }

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_SUBSCRIPTION');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-subscription';

        return $onePlugin;
    }

    function textPopup()
    {
        $others = array();
        $others['unsubscribe'] = array('name' => acym_translation('ACYM_UNSUBSCRIBE_LINK'), 'default' => acym_translation('ACYM_UNSUBSCRIBE', true));
        $others['confirm'] = array('name' => acym_translation('ACYM_CONFIRM_SUBSCRIPTION_LINK'), 'default' => acym_translation('ACYM_CONFIRM_SUBSCRIPTION', true));
        $others['subscribe'] = array('name' => acym_translation('ACYM_SUBSCRIBE_LINK'), 'default' => acym_translation('ACYM_SUBSCRIBE', true));

        ?>
        <script language="javascript" type="text/javascript">
            <!--
            var openLists = true;
            var selectedTag = '';
            function changeSubscriptionTag(tagName){
                selectedTag = tagName;
                defaultText = [];
                <?php
                foreach ($others as $tagname => $tag) {
                    echo "defaultText['$tagname'] = '".$tag['default']."';";
                }
                ?>
                $('.acym__subscription__subscription').removeClass('selected_row');
                $('#tr_' + tagName).addClass('selected_row');
                document.getElementById('acym__popup__subscription__tagtext').value = defaultText[tagName];
                setSubscriptionTag();
            }

            function setSubscriptionTag(){
                var tag = '{' + selectedTag;
                var lists = document.getElementById('acym__popup__subscription__listids').innerHTML;

                if(selectedTag == 'subscribe' && lists != ''){
                    tag += "|lists:" + document.getElementById('acym__popup__subscription__listids').innerHTML;
                }else{
                    $('#acym__popup__plugin__subscription__lists__modal').hide();
                    $('#acym__popup__subscription__listids').html('');
                }

                tag += '}' + document.getElementById('acym__popup__subscription__tagtext').value + '{/' + selectedTag + '}';
                setTag(tag, $('#tr_' + selectedTag));
            }

            function displayLists(){
                $.Modal();
                $('#acym__popup__plugin__subscription__lists__modal').toggle();
                openLists = !openLists;
                $('#acym__popup__subscription__change').on('change', function(){
                    var lists = JSON.parse($('#acym__modal__lists-selected').val());
                    $('#acym__popup__subscription__listids').html(lists.join());
                    changeSubscriptionTag('subscribe');
                });
            }
            //-->
        </script>
        <?php

        $text = '<div class="acym__popup__listing text-center grid-x">
                    <h1 class="acym__popup__plugin__title cell">'.acym_translation('ACYM_SUBSCRIPTION').'</h1>
                    <div class="medium-1"></div>
                    <div class="medium-10 text-left">';
        $text .= acym_modal_pagination_lists('', '', '', '', '', false, 'acym__popup__subscription__change', '', false, 'style="display: none;" id="acym__popup__plugin__subscription__lists__modal"');
        $text .= '  </div>
                    <div class="medium-1"></div>
					<div class="grid-x medium-12 cell acym__listing__row text-left">
                        <div class="grid-x cell medium-5 small-12 acym__listing__title acym__listing__title__dynamics acym__subscription__subscription">
                            <label class="small-3" style="line-height: 40px;" for="acym__popup__subscription__tagtext">'.acym_translation('ACYM_TEXT').': </label>
                            <input class="small-9" type="text" name="tagtext" id="acym__popup__subscription__tagtext" onchange="setSubscriptionTag();">
                        </div>
                        <div class="medium-1"></div>
                        <div id="select_lists_zone" class="grid-x cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">
                            <p class="shrink" id="acym__popup__subscription__text__list">'.acym_translation('ACYM_LISTS_SELECTED').'</button>
                            <p class="shrink" id="acym__popup__subscription__listids"></p>
                        </div>
                    </div>';
        $text .= '
					<div class="cell grid-x">';

        foreach ($others as $tagname => $tag) {
            $onclick = $tagname == 'subscribe' ? 'changeSubscriptionTag(\''.$tagname.'\');displayLists();return false;' : 'changeSubscriptionTag(\''.$tagname.'\');';
            $text .= '<div class="grid-x small-12 cell acym__listing__row acym__listing__row__popup text-left"  onclick="'.$onclick.'" id="tr_'.$tagname.'" ><div class="cell small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['name'].'</div></div>';
        }
        $text .= '</div></div>';

        $others = array();
        $others['name'] = acym_translation('ACYM_LIST_NAME');
        $others['names'] = acym_translation('ACYM_LIST_NAMES');
        $others['id'] = acym_translation('ACYM_LIST_ID', true);

        $text .= '<div class="acym__popup__listing text-center grid-x">
					<h1 class="acym__popup__plugin__title cell">'.acym_translation('LIST').'</h1>
					<div class="cell grid-x">';

        foreach ($others as $tagname => $tag) {
            $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\'{list:'.$tagname.'}\', $(this));" id="tr_'.$tagname.'" >
                        <div class="cell medium-12 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag.'</div>
                      </div>';
        }

        $text .= '</div></div>';

        $text .= '<div class="acym__popup__listing text-center grid-x">
					<span class="acym__popup__plugin__title cell">'.acym_translation('CAMPAIGN').'</span>
					<div class="cell grid-x">';
        $othersMail = array('campaignid', 'subject');

        foreach ($othersMail as $tag) {
            $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\'{mail:'.$tag.'}\', $(this));" id="tr_'.$tag.'" >
                        <div class="cell medium-12 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag.'</div>
                      </div>';
        }
        $text .= '</div></div>';

        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $this->_replacelisttags($email, $user, $send);

        if (!$this->unsubscribeLink || $this->listunsubscribe || !method_exists($email, 'addCustomHeader')) {
            return;
        }

        $lang = empty($email->lang) ? '' : '&lang='.$email->lang;
        $myLink = acym_frontendLink('frontusers&subid='.intval($user->id).'&task=unsubscribe&id='.$email->id.'&key='.urlencode($user->key).$lang.'&'.acym_noTemplate());

        $this->listunsubscribe = true;
        if (!empty($email->replyemail)) {
            $mailto = $email->replyemail;
        }
        if (empty($mailto)) {
            $config = acym_config();
            $mailto = $config->get('replyto_email');
        }
        $email->addCustomHeader('List-Unsubscribe: <'.$myLink.'>, <mailto:'.$mailto.'?subject=unsubscribe_user_'.$user->id.'&body=Please%20unsubscribe%20user%20ID%20'.$user->id.'>');
    }

    function replaceContent(&$email, $send = true)
    {
        $this->_replacesubscriptiontags($email);
        $this->_replacemailtags($email);
    }

    private function _replacemailtags(&$email)
    {
        $result = $this->acympluginHelper->extractTags($email, 'mail');
        $tags = array();

        foreach ($result as $key => $oneTag) {
            if (isset($tags[$key])) {
                continue;
            }

            $field = $oneTag->id;
            if (!empty($email) && !empty($email->$field)) {
                $text = $email->$field;
                $this->acympluginHelper->formatString($text, $oneTag);
                $tags[$key] = $text;
            } else {
                $tags[$key] = $oneTag->default;
            }
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    private function _replacelisttags(&$email, &$user, $send)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'list');
        if (empty($tags)) {
            return;
        }

        $replaceTags = array();
        foreach ($tags as $oneTag => $parameter) {
            $method = '_list'.trim(strtolower($parameter->id));

            if (method_exists($this, $method)) {
                $replaceTags[$oneTag] = $this->$method($email, $user, $parameter);
            } else {
                $replaceTags[$oneTag] = 'Method not found: '.$method;
            }
        }

        $this->acympluginHelper->replaceTags($email, $replaceTags, true);
    }

    private function _getattachedlistid($email, $subid)
    {
        $mailid = $email->id;
        $type = strtolower($email->type);

        if (isset($this->lists[$mailid][$subid])) {
            return $this->lists[$mailid][$subid];
        }

        $mailClass = acym_get('class.mail');
        $mailLists = $mailClass->getAllListsByMailId($mailid);
        $userLists = array();

        if (!empty($subid)) {
            $userClass = acym_get('class.user');
            $userLists = $userClass->getUserSubscriptionById($subid);

            $listid = null;
            foreach ($userLists as $id => $oneList) {
                if ($oneList->status == 1 && in_array($id, $mailLists)) {
                    $this->lists[$mailid][$subid] = $id;

                    return $this->lists[$mailid][$subid];
                }
            }

            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if (!empty($mailLists)) {
            $listIds = array_keys($mailLists);
            $this->lists[$mailid][$subid] = array_shift($listIds);

            return $this->lists[$mailid][$subid];
        }

        if ($type == 'welcome' && !empty($subid)) {
            $listid = acym_loadResult('SELECT list.id FROM #__acym_list AS list JOIN #__acym_user_has_list AS userlist ON list.id = userlist.list_id WHERE list.welcome_id = '.intval($mailid).' AND userlist.user_id = '.intval($subid).' ORDER BY b.subscription_date DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if ($type == 'unsubscribe' && !empty($subid)) {
            $listid = acym_loadResult('SELECT list.id FROM #__acym_list AS list JOIN #__acym_user_has_list AS userlist ON list.id = userlist.list_id WHERE list.unsubscribe_id = '.intval($mailid).' AND userlist.user_id = '.intval($subid).' ORDER BY b.unsubscribe_date DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if (!empty($userLists)) {
            $listIds = array_keys($userLists);
            $this->lists[$mailid][$subid] = array_shift($listIds);

            return $this->lists[$mailid][$subid];
        }
    }

    private function _listnames(&$email, &$user, &$parameter)
    {
        if (empty($user->id)) {
            return "";
        }
        $userClass = acym_get('class.user');
        $usersubscription = $userClass->getUserSubscriptionById($user->id);
        $lists = array();
        if (!empty($usersubscription)) {
            foreach ($usersubscription as $onesub) {
                if ($onesub->status < 1 || empty($onesub->active)) {
                    continue;
                }
                $lists[] = $onesub->name;
            }
        }

        return implode(isset($parameter->separator) ? $parameter->separator : ', ', $lists);
    }

    private function _listowner(&$email, &$user, &$parameter)
    {
        if (empty($user->id)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return "";
        }

        if (!isset($this->listsowner[$listid])) {
            $this->listsowner[$listid] = acym_loadObject('SELECT user.* FROM #__acym_list AS list JOIN '.$this->cmsUserVars->table.' AS user ON user.'.$this->cmsUserVars->id.' = list.cms_user_id WHERE list.id = '.intval($listid));
        }

        if (!in_array($parameter->field, array($this->cmsUserVars->username, $this->cmsUserVars->name, $this->cmsUserVars->email))) {
            return 'Field not found : '.$parameter->field;
        }

        return @$this->listsowner[$listid]->{$this->cmsUserVars->{$parameter->field}};
    }

    private function _loadlist($listid)
    {
        if (isset($this->listsinfo[$listid])) {
            return;
        }

        $listClass = acym_get('class.list');
        $this->listsinfo[$listid] = $listClass->getOneById(intval($listid));
    }

    private function _listname(&$email, &$user, &$parameter)
    {
        if (empty($user->id)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->id);
        if (empty($listid)) {
            return "";
        }

        $this->_loadlist($listid);

        return @$this->listsinfo[$listid]->name;
    }

    private function _listid(&$email, &$user, &$parameter)
    {
        if (empty($user->id)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->id);
        if (empty($listid)) {
            return "";
        }

        return $listid;
    }

    private function _replacesubscriptiontags(&$email)
    {
        $match = '#(?:{|%7B)(confirm[^}]*|unsubscribe(?:\|[^}]*)?|subscribe[^}]*)(?:}|%7D)(.*)(?:{|%7B)/(confirm|unsubscribe|subscribe)(?:}|%7D)#Uis';
        $variables = array('subject', 'body');
        $found = false;
        $results = array();
        foreach ($variables as $var) {
            if (empty($email->$var)) {
                continue;
            }
            $found = preg_match_all($match, $email->$var, $results[$var]) || $found;
            if (empty($results[$var][0])) {
                unset($results[$var]);
            }
        }

        if (!$found) {
            return;
        }

        $tags = array();
        $this->listunsubscribe = false;
        foreach ($results as $var => $allresults) {
            foreach ($allresults[0] as $i => $oneTag) {
                if (isset($tags[$oneTag])) {
                    continue;
                }
                $tags[$oneTag] = $this->replaceSubscriptionTag($allresults, $i, $email);
            }
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    function replaceSubscriptionTag(&$allresults, $i, &$email)
    {
        $config = acym_config();
        $lang = empty($email->lang) ? '' : '&lang='.$email->lang;

        $parameters = $this->acympluginHelper->extractTag($allresults[1][$i]);

        if ($parameters->id == 'confirm') {
            $myLink = acym_frontendLink('frontusers&task=confirm&id={subtag:id}&key={subtag:key|urlencode}'.$lang);
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a target="_blank" href="'.$myLink.'">'.$allresults[2][$i].'</a>';
        } elseif ($parameters->id == 'subscribe') {
            if (empty($parameters->lists)) {
                return 'You must select at least one list';
            }
            $lists = explode(',', $parameters->lists);
            acym_arrayToInteger($lists);
            $captchaKey = $config->get('captcha_enabled') ? '&seckey='.$config->get('security_key', '') : '';
            $myLink = acym_frontendLink('frontusers&task=subscribe&hiddenlists='.implode(',', $lists).'&user[email]={subtag:email|urlencode}'.$lang.$captchaKey);
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acym_subscribe">'.$allresults[2][$i].'</span></a>';
        } else {
            $this->unsubscribeLink = true;

            $myLink = acym_frontendLink('frontusers&task=unsubscribe&id={subtag:id}&key={subtag:key|urlencode}&'.acym_noTemplate().$lang.'&mail_id='.$email->id);
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acym_unsubscribe">'.$allresults[2][$i].'</span></a>';
        }
    }
}//endclass

