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

define('ACYM_CMS', 'Joomla!®');
define('ACYM_COMPONENT', 'com_acym');
define('ACYM_DEFAULT_LANGUAGE', 'en-GB');

define('ACYM_BASE', rtrim(JPATH_BASE, DS).DS);
define('ACYM_ROOT', rtrim(JPATH_ROOT, DS).DS);
define('ACYM_FRONT', rtrim(JPATH_SITE, DS).DS.'components'.DS.ACYM_COMPONENT.DS);
define('ACYM_BACK', rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.ACYM_COMPONENT.DS);
define('ACYM_VIEW', ACYM_BACK.'views'.DS);
define('ACYM_VIEW_FRONT', ACYM_FRONT.'views'.DS);
define('ACYM_HELPER', ACYM_BACK.'helpers'.DS);
define('ACYM_CLASS', ACYM_BACK.'classes'.DS);
define('ACYM_LIBRARY', ACYM_BACK.'library'.DS);
define('ACYM_TYPE', ACYM_BACK.'types'.DS);
define('ACYM_CONTROLLER', ACYM_BACK.'controllers'.DS);
define('ACYM_CONTROLLER_FRONT', ACYM_FRONT.'controllers'.DS);
define('ACYM_MEDIA', ACYM_ROOT.'media'.DS.ACYM_COMPONENT.DS);
define('ACYM_LANGUAGE', ACYM_ROOT.'language'.DS);
define('ACYM_INC', ACYM_FRONT.'inc'.DS);

define('ACYM_MEDIA_RELATIVE', 'media/'.ACYM_COMPONENT.'/');
define('ACYM_MEDIA_URL', acym_rootURI().'media/'.ACYM_COMPONENT.'/');
define('ACYM_IMAGES', ACYM_MEDIA_URL.'images/');
define('ACYM_CSS', ACYM_MEDIA_URL.'css/');
define('ACYM_JS', ACYM_MEDIA_URL.'js/');
define('ACYM_DYNAMICS_URL', acym_rootURI().'administrator/components/'.ACYM_COMPONENT.'/dynamics/');

define('ACYM_MEDIA_FOLDER', 'media/com_acym');

$jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
define('ACYM_J30', version_compare($jversion, '3.0.0', '>='));
define('ACYM_J37', version_compare($jversion, '3.7.0', '>='));
define('ACYM_J40', version_compare($jversion, '4.0.0', '>='));

define('ACYM_ALLOWRAW', defined('JREQUEST_ALLOWRAW') ? JREQUEST_ALLOWRAW : 2);
define('ACYM_ALLOWHTML', defined('JREQUEST_ALLOWHTML') ? JREQUEST_ALLOWHTML : 4);

function acym_getTime($date)
{
    static $timeoffset = null;
    if ($timeoffset === null) {
        $timeoffset = acym_getCMSConfig('offset');

        $dateC = JFactory::getDate($date, $timeoffset);
        $timeoffset = $dateC->getOffsetFromGMT(true);
    }

    return strtotime($date) - $timeoffset * 60 * 60 + date('Z');
}

function acym_fileGetContent($url, $timeout = 10)
{
    ob_start();
    $data = '';
    if (class_exists('JHttpFactory') && method_exists('JHttpFactory', 'getHttp')) {
        $http = JHttpFactory::getHttp();
        try {
            $response = $http->get($url, array(), $timeout);
        } catch (RuntimeException $e) {
            $response = null;
        }

        if ($response !== null && $response->code === 200) {
            $data = $response->body;
        }
    }

    if (empty($data) && function_exists('curl_exec') && filter_var($url, FILTER_VALIDATE_URL)) {
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($timeout)) {
            curl_setopt($conn, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        $data = curl_exec($conn);
        if ($data === false) {
            echo curl_error($conn);
        }
        curl_close($conn);
    }

    if (empty($data) && function_exists('file_get_contents')) {
        if (!empty($timeout)) {
            ini_set('default_socket_timeout', $timeout);
        }
        $streamContext = stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));
        $data = file_get_contents($url, false, $streamContext);
    }

    if (empty($data) && function_exists('fopen') && function_exists('stream_get_contents')) {
        $handle = fopen($url, "r");
        if (!empty($timeout)) {
            stream_set_timeout($handle, $timeout);
        }
        $data = stream_get_contents($handle);
    }
    $warnings = ob_get_clean();

    if (acym_isDebug()) {
        echo $warnings;
    }

    return $data;
}

function acym_formToken()
{
    return JHTML::_('form.token');
}

function acym_checkToken()
{
    if (ACYM_J40) {
        \JSession::checkToken() || \JSession::checkToken('get') || die('Invalid Token');
    } else {
        if (!JRequest::checkToken() && !JRequest::checkToken('get')) {
            JSession::checkToken() || JSession::checkToken('get') || die('Invalid Token');
        }
    }
}

function acym_getFormToken()
{
    if (ACYM_J30) {
        return JSession::getFormToken().'=1';
    }

    return JUtility::getToken().'=1';
}

function acym_translation($key, $jsSafe = false, $interpretBackSlashes = true)
{
    return JText::_($key, $jsSafe, $interpretBackSlashes);
}

function acym_translation_sprintf()
{
    $args = func_get_args();

    return call_user_func_array(array('JText', 'sprintf'), $args);
}

function acym_route($url, $xhtml = true, $ssl = null)
{
    return JRoute::_($url, $xhtml, $ssl);
}

function acym_getVar($type, $name, $default = null, $hash = 'default', $mask = 0)
{
    if (ACYM_J40) {
        if ($mask & ACYM_ALLOWRAW) {
            $type = 'RAW';
        } elseif ($mask & ACYM_ALLOWHTML) {
            $type = 'HTML';
        }

        $result = JFactory::getApplication()->input->get($name, $default, $type);
    } else {
        $result = JRequest::getVar($name, $default, $hash, $type, $mask);
    }

    if ($mask == ACYM_ALLOWRAW) {
        $result = JComponentHelper::filterText($result);
    }

    return $result;
}

function acym_setVar($name, $value = null, $hash = 'method', $overwrite = true)
{
    if (ACYM_J40) {
        return JFactory::getApplication()->input->set($name, $value);
    }

    return JRequest::setVar($name, $value, $hash, $overwrite);
}

function acym_raiseError($level, $code, $msg, $info = null)
{
    return JError::raise($level, $code, $msg, $info);
}

function acym_getGroupsByUser($userid = null, $recursive = null, $names = false)
{
    if ($userid === null) {
        $userid = acym_currentUserId();
        $recursive = true;
    }

    jimport('joomla.access.access');

    $groups = JAccess::getGroupsByUser($userid, $recursive);

    if ($names) {
        $groups = acym_loadResultArray(
            'SELECT ugroup.title 
            FROM #__usergroups AS ugroup 
            JOIN #__user_usergroup_map AS map ON ugroup.id = map.group_id 
            WHERE map.user_id = '.intval($userid).' AND ugroup.id IN ('.implode(',', $groups).')'
        );
    }

    return $groups;
}

function acym_getGroups()
{
    $groups = acym_loadObjectList('SELECT a.*, a.title AS text, a.id AS value, COUNT(ugm.user_id) AS nbusers FROM #__usergroups AS a LEFT JOIN #__user_usergroup_map ugm ON a.id = ugm.group_id GROUP BY a.id', 'id');

    return $groups;
}

function acym_getLanguages($installed = false)
{
    $result = array();

    $path = acym_getLanguagePath(ACYM_ROOT);
    $dirs = acym_getFolders($path);

    $languages = acym_loadObjectList('SELECT * FROM #__languages', 'lang_code');

    foreach ($dirs as $dir) {
        if (strlen($dir) != 5 || $dir == "xx-XX") {
            continue;
        }
        if ($installed && (empty($languages[$dir]) || $languages[$dir]->published != 1)) {
            continue;
        }

        $xmlFiles = acym_getFiles($path.DS.$dir, '^([-_A-Za-z]*)\.xml$');
        $xmlFile = reset($xmlFiles);
        if (empty($xmlFile)) {
            $data = array();
        } else {
            if (ACYM_J40) {
                $data = \JInstaller::parseXMLInstallFile(ACYM_LANGUAGE.$dir.DS.$xmlFile);
            } else {
                $data = JApplicationHelper::parseXMLLangMetaFile(ACYM_LANGUAGE.$dir.DS.$xmlFile);
            }
        }

        $lang = new stdClass();
        $lang->sef = empty($languages[$dir]) ? null : $languages[$dir]->sef;
        $lang->language = strtolower($dir);
        $lang->name = empty($data['name']) ? (empty($languages[$dir]) ? $dir : $languages[$dir]->title_native) : $data['name'];
        $lang->exists = file_exists(ACYM_LANGUAGE.$dir.DS.$dir.'.com_acym.ini');
        $lang->content = empty($languages[$dir]) ? false : $languages[$dir]->published == 1;

        $result[$dir] = $lang;
    }

    return $result;
}

function acym_languageFolder($code)
{
    return ACYM_LANGUAGE.$code.DS;
}

function acym_cleanSlug($slug)
{
    $method = acym_getCMSConfig('unicodeslugs', 0) == 1 ? 'stringURLUnicodeSlug' : 'stringURLSafe';

    return JFilterOutput::$method(trim($slug));
}

function acym_punycode($email, $method = 'emailToPunycode')
{
    if (empty($email) || version_compare(JVERSION, '3.1.2', '<')) {
        return $email;
    }
    $email = JStringPunycode::$method($email);

    return $email;
}

function acym_extractArchive($archive, $destination)
{
    return JArchive::extract($archive, $destination);
}

function acym_addScript($raw, $script, $type = "text/javascript", $defer = false, $async = false)
{
    $acyDocument = acym_getGlobal('doc');

    if ($raw) {
        $acyDocument->addScriptDeclaration($script, $type);
    } else {
        $acyDocument->addScript($script, $type, $defer, $async);
    }
}

function acym_addStyle($raw, $style, $type = 'text/css', $media = null, $attribs = array())
{
    $acyDocument = acym_getGlobal('doc');

    if ($raw) {
        $acyDocument->addStyleDeclaration($style, $type);
    } else {
        $acyDocument->addStyleSheet($style, $type, $media, $attribs);
    }
}

function acym_addMetadata($meta, $data, $name = 'name')
{
    $acyDocument = acym_getGlobal('doc');

    $acyDocument->setMetaData($meta, $data, $name);
}

function acym_isAdmin()
{
    $acyapp = acym_getGlobal('app');

    return $acyapp->isAdmin();
}

function acym_getCMSConfig($varname, $default = null)
{
    if (ACYM_J30) {
        $acyapp = acym_getGlobal('app');

        return $acyapp->getCfg($varname, $default);
    }

    $conf = JFactory::getConfig();
    $val = $conf->getValue('config.'.$varname);

    return empty($val) ? $default : $val;
}

function acym_getCMSPosts($category, $keyword, $offset = 0)
{
    $query = 'SELECT post.title, post.introtext AS content, categories.title AS categoryTitle
                FROM #__content AS post 
                JOIN #__categories AS categories ON categories.id = post.catid
                WHERE post.state = 1';

    if (!empty($category)) {
        $query .= ' AND categories.id = '.(int)$category;
    }

    $query .= ' AND post.title LIKE '.acym_escapeDB('%'.$keyword.'%');

    $query .= ' LIMIT '.(0 + $offset).', '.(10 + $offset);

    $posts = acym_loadObjectList($query);

    foreach ($posts as $post) {
        echo "<div class='cell acym__wysid__cms__post margin-bottom-1 padding-1'>";
        echo "<div class='cell acym__wysid__cms__post__title'><h3>".$post->title."</h3></div>";
        echo "<div class='cell acym__wysid__cms__post__content'><p>".$post->content."</p></div>";
        echo "</div>";
    }
}

function acym_getCMSCategories()
{
    $query = 'SELECT id, title 
            FROM #__categories 
            WHERE published = 1 
                AND extension = "com_content"';

    $categories = acym_loadObjectList($query);

    echo '<option value="">'.acym_translation('ACYM_ALL_CATEGORIES').'</option>';
    foreach ($categories as $category) {
        echo "<option value='".$category->id."'>".$category->title."</option>";
    }
}

function acym_redirect($url, $msg = '', $msgType = 'message')
{
    $acyapp = acym_getGlobal('app');

    if (ACYM_J40) {
        if (!empty($msg)) {
            acym_enqueueMessage($msg, $msgType);
        }

        return $acyapp->redirect($url);
    } else {
        return $acyapp->redirect($url, $msg, $msgType);
    }
}

function acym_getLanguageTag()
{
    $acylanguage = JFactory::getLanguage();

    return $acylanguage->getTag();
}

function acym_getLanguageLocale()
{
    $acylanguage = JFactory::getLanguage();

    return $acylanguage->getLocale();
}

function acym_setLanguage($lang)
{
    $acylanguage = JFactory::getLanguage();

    $acylanguage->setLanguage($lang);
}

function acym_baseURI($pathonly = false)
{
    return JURI::base($pathonly);
}

function acym_rootURI($pathonly = false, $path = null)
{
    return JURI::root($pathonly, $path);
}

function acym_generatePassword($length = 8)
{
    return JUserHelper::genrandompassword($length);
}

function acym_currentUserId()
{
    $acymy = JFactory::getUser();

    return $acymy->id;
}

function acym_currentUserName($userid = null)
{
    if (!empty($userid)) {
        $special = JFactory::getUser($userid);

        return $special->name;
    }

    $acymy = JFactory::getUser();

    return $acymy->name;
}

function acym_currentUserEmail($userid = null)
{
    if (!empty($userid)) {
        $special = JFactory::getUser($userid);

        return $special->email;
    }

    $acymy = JFactory::getUser();

    return $acymy->email;
}

function acym_authorised($action, $assetname = null)
{
    $acymy = JFactory::getUser();

    return $acymy->authorise($action, $assetname);
}

function acym_loadLanguageFile($extension = 'joomla', $basePath = JPATH_SITE, $lang = null, $reload = false, $default = true)
{
    $acylanguage = JFactory::getLanguage();

    $acylanguage->load($extension, $basePath, $lang, $reload, $default);
}

function acym_getGlobal($type)
{
    $variables = array(
        'db' => array('acydb', 'getDBO'),
        'doc' => array('acyDocument', 'getDocument'),
        'app' => array('acyapp', 'getApplication'),
    );

    global ${$variables[$type][0]};
    if (${$variables[$type][0]} === null) {
        $method = $variables[$type][1];
        ${$variables[$type][0]} = JFactory::$method();
    }

    return ${$variables[$type][0]};
}

function acym_escapeDB($value)
{
    $acydb = acym_getGlobal('db');

    return $acydb->quote($value);
}

function acym_query($query)
{
    $acydb = acym_getGlobal('db');
    $acydb->setQuery($query);

    $method = ACYM_J40 ? 'execute' : 'query';

    $result = $acydb->$method();
    if (!$result) {
        return false;
    }

    return $acydb->getAffectedRows();
}

function acym_loadObjectList($query, $key = '', $offset = null, $limit = null)
{
    $acydb = acym_getGlobal('db');

    $acydb->setQuery($query, $offset, $limit);

    return $acydb->loadObjectList($key);
}

function acym_loadObject($query)
{
    $acydb = acym_getGlobal('db');

    $acydb->setQuery($query);

    return $acydb->loadObject();
}

function acym_loadResult($query)
{
    $acydb = acym_getGlobal('db');

    $acydb->setQuery($query);

    return $acydb->loadResult();
}

function acym_loadResultArray($query)
{
    if (is_string($query)) {
        $acydb = acym_getGlobal('db');
        $acydb->setQuery($query);
    } else {
        $acydb = $query;
    }

    if (ACYM_J30) {
        return $acydb->loadColumn();
    }

    return $acydb->loadResultArray();
}

function acym_getEscaped($value, $extra = false)
{
    $acydb = acym_getGlobal('db');

    if (ACYM_J30) {
        return $acydb->escape($value, $extra);
    }

    return $acydb->getEscaped($value, $extra);
}

function acym_getDBError()
{
    $acydb = acym_getGlobal('db');

    return $acydb->getErrorMsg();
}

function acym_insertObject($table, $element)
{
    $acydb = acym_getGlobal('db');
    $acydb->insertObject($table, $element);

    return $acydb->insertid();
}

function acym_insertID()
{
    $acydb = acym_getGlobal('db');

    return $acydb->insertid();
}

function acym_updateObject($table, $element, $pkey)
{
    $acydb = acym_getGlobal('db');

    return $acydb->updateObject($table, $element, $pkey, true);
}

function acym_getPrefix()
{
    $acydb = acym_getGlobal('db');

    return $acydb->getPrefix();
}

function acym_getTableList()
{
    $acydb = acym_getGlobal('db');

    return $acydb->getTableList();
}

function acym_completeLink($link, $popup = false, $redirect = false, $forceNoPopup = false)
{
    if (($popup || acym_isNoTemplate()) && $forceNoPopup == false) {
        $link .= '&'.acym_noTemplate();
    }

    return acym_route('index.php?option='.ACYM_COMPONENT.'&ctrl='.$link, !$redirect);
}

function acym_noTemplate()
{
    return 'tmpl=component';
}

function acym_isNoTemplate()
{
    return acym_getVar('cmd', 'tmpl') == 'component';
}

function acym_setNoTemplate($status = true)
{
    if ($status) {
        acym_setVar('tmpl', 'component');
    } else {
        acym_setVar('tmpl', '');
    }
}

function acym_cmsLoaded()
{
    defined('_JEXEC') or die('Restricted access');
}

function acym_formOptions($token = true, $task = '', $currentStep = null, $currentCtrl = '')
{
    if (!empty($currentStep)) {
        echo '<input type="hidden" name="step" value="'.$currentStep.'"/>';
        echo '<input type="hidden" name="nextstep" value=""/>';
        echo '<input type="hidden" name="edition" value="'.acym_getVar('cmd', 'edition', '0').'"/>';
    }
    echo '<input type="hidden" name="option" value="'.ACYM_COMPONENT.'"/>';
    echo '<input type="hidden" name="nextstep" value=""/>';
    echo '<input type="hidden" name="task" value="'.$task.'"/>';
    echo '<input type="hidden" name="ctrl" value="'.(empty($currentCtrl) ? acym_getVar('cmd', 'ctrl', '') : $currentCtrl).'"/>';
    if ($token) {
        echo acym_formToken();
    }
    echo '<button type="submit" class="is-hidden" id="formSubmit"></button>';
}

function acym_enqueueMessage($message, $type = 'success')
{
    $result = is_array($message) ? implode('<br/>', $message) : $message;

    if (acym_isAdmin()) {
        if (ACYM_J30) {
            $type = str_replace(array('notice', 'message'), array('info', 'success'), $type);
        } else {
            $type = str_replace(array('message', 'notice', 'warning'), array('info', 'warning', 'error'), $type);
        }
    } else {
        if (ACYM_J30) {
            $type = str_replace(array('success', 'info'), array('message', 'notice'), $type);
        } else {
            $type = str_replace(array('success', 'error', 'warning', 'info'), array('message', 'warning', 'notice', 'message'), $type);
        }
    }

    $acyapp = acym_getGlobal('app');

    $acyapp->enqueueMessage($result, $type);
}

function acym_displayMessages()
{
    $acyapp = acym_getGlobal('app');
    $messages = $acyapp->getMessageQueue(true);
    if (empty($messages)) {
        return;
    }

    $sorted = array();
    foreach ($messages as $oneMessage) {
        $sorted[$oneMessage['type']][] = $oneMessage['message'];
    }

    foreach ($sorted as $type => $message) {
        acym_display($message, $type);
    }
}

function acym_editCMSUser($userid)
{
    return acym_route('index.php?option=com_users&view=user&layout=edit&id='.$userid);
}

function acym_prepareAjaxURL($url)
{
    return htmlspecialchars_decode(acym_completeLink($url, true));
}

function acym_cmsACL()
{
    if (!acym_authorised('core.admin', 'com_acym')) {
        return '';
    }

    $return = urlencode(base64_encode((string)JUri::getInstance()));

    return '<div class="onelineblockoptions">
        <span class="acyblocktitle">'.acym_translation('ACYM_JOOMLA_PERMISSIONS').'</span>
        <a class="acym_button_grey" style="color:#666;" target="_blank" href="index.php?option=com_config&view=component&component=com_acym&path=&return='.$return.'">'.acym_translation('JTOOLBAR_OPTIONS').'</a><br/>
    </div>';
}

function acym_isDebug()
{
    return defined('JDEBUG') && JDEBUG;
}

function acym_getLanguagePath($basePath = ACYM_BASE, $language = null)
{
    return JLanguage::getLanguagePath(rtrim($basePath, DS), $language);
}

function acym_userEditLink()
{
    if (file_exists(ACYM_ROOT.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')) {
        $editLink = 'index.php?option=com_comprofiler&task=edit&cid[]=';
    } else {
        $editLink = 'index.php?option=com_users&task=user.edit&id=';
    }

    return $editLink;
}

function acym_askLog($current = true, $message = 'ACYM_NOTALLOWED', $type = 'error')
{
    $url = 'index.php?option=com_users&view=login';
    if ($current) {
        $url .= '&return='.base64_encode(acym_currentURL());
    }
    acym_redirect($url, acym_translation($message), $type);
}

function acym_frontendLink($link, $complete = true, $popup = false)
{
    if ($complete) {
        $link = 'index.php?option=com_acym&ctrl='.$link;
    }

    if ($popup) {
        $link .= '&'.acym_noTemplate();
    }
    $config = acym_config();

    if ($config->get('use_sef', 0) && strpos($link, '&ctrl=cron') === false) {
        $sefLink = acym_fileGetContent(acym_rootURI().'index.php?option=com_acym&ctrl=url&task=sef&urls[0]='.base64_encode($link));
        $json = json_decode($sefLink, true);
        if ($json == null) {
            if (!empty($sefLink) && acym_isDebug()) {
                acym_enqueueNotification('Error trying to get the sef link: '.$sefLink, 'error', 0);
            }
        } else {
            $link = array_shift($json);

            return $link;
        }
    }

    $mainurl = acym_mainURL($link);

    return $mainurl.$link;
}

function acym_prepareQuery($query)
{
    $query = str_replace('#__', acym_getPrefix(), $query);

    return $query;
}

function acym_date($input = 'now', $format = null, $useTz = true, $gregorian = false)
{
    if ($useTz === true) {
        $tz = false;
    } else {
        $tz = null;
    }

    return JHTML::_('date', $input, $format, $tz, $gregorian);
}

function acym_getMenu()
{
    global $Itemid;

    $jsite = JFactory::getApplication('site');
    $menus = $jsite->getMenu();
    $menu = $menus->getActive();

    if (empty($menu) && !empty($Itemid)) {
        $menus->setActive($Itemid);
        $menu = $menus->getItem($Itemid);
    }

    return $menu;
}

function acym_getTitle()
{
    $document = acym_getGlobal('doc');

    return $document->getTitle();
}

function acym_getDefaultConfigValues()
{
    $allPref = array();

    $allPref['from_name'] = acym_getCMSConfig('fromname');
    $allPref['from_email'] = acym_getCMSConfig('mailfrom');
    $allPref['bounce_email'] = acym_getCMSConfig('mailfrom');
    $allPref['sendmail_path'] = acym_getCMSConfig('sendmail');
    $allPref['smtp_port'] = acym_getCMSConfig('smtpport');
    $allPref['smtp_secured'] = acym_getCMSConfig('smtpsecure');
    $allPref['smtp_auth'] = acym_getCMSConfig('smtpauth');
    $allPref['smtp_username'] = acym_getCMSConfig('smtpuser');
    $allPref['smtp_password'] = acym_getCMSConfig('smtppass');
    $allPref['mailer_method'] = acym_getCMSConfig('mailer');
    $smtpinfos = explode(':', acym_getCMSConfig('smtphost'));
    $allPref['smtp_host'] = $smtpinfos[0];
    if (isset($smtpinfos[1])) {
        $allPref['smtp_port'] = $smtpinfos[1];
    }
    if (!in_array($allPref['smtp_secured'], array('tls', 'ssl'))) {
        $allPref['smtp_secured'] = '';
    }
    $allPref['uploadfolder'] = 'media/com_acym/upload';
    $allPref['cron_savepath'] = 'media/com_acym/logs/report{year}_{month}.log';

    return $allPref;
}

function acym_addBreadcrumb($title, $link = '')
{
    $acyapp = acym_getGlobal('app');
    $pathway = $acyapp->getPathway();
    $pathway->addItem($title, $link);
}

function acym_setPageTitle($title)
{
    if (empty($title)) {
        $title = acym_getCMSConfig('sitename');
    } elseif (acym_getCMSConfig('sitename_pagetitles', 0) == 1) {
        $title = acym_translation_sprintf('ACYM_JPAGETITLE', acym_getCMSConfig('sitename'), $title);
    } elseif (acym_getCMSConfig('sitename_pagetitles', 0) == 2) {
        $title = acym_translation_sprintf('ACYM_JPAGETITLE', $title, acym_getCMSConfig('sitename'));
    }
    $document = JFactory::getDocument();
    $document->setTitle($title);
}

function acym_enqueueNotification_front($message, $type = 'info', $time = 0)
{
    acym_enqueueMessage($message, $type);
}

global $acymCmsUserVars;
$acymCmsUserVars = new stdClass();
$acymCmsUserVars->table = '#__users';
$acymCmsUserVars->name = 'name';
$acymCmsUserVars->username = 'username';
$acymCmsUserVars->id = 'id';
$acymCmsUserVars->email = 'email';
$acymCmsUserVars->registered = 'registerDate';
$acymCmsUserVars->blocked = 'block';
