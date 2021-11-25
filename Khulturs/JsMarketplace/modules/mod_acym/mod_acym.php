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

if (!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
    echo 'This module cannot work without AcyMailing';

    return;
};

acym_initModule($params);

$identifiedUser = null;
$currentUserEmail = acym_currentUserEmail();
if ($params->get('userinfo', '1') == '1' && !empty($currentUserEmail)) {
    $userClass = acym_get('class.user');
    $identifiedUser = $userClass->getOneByEmail($currentUserEmail);
}

$visibleLists = $params->get('displists', array());
$hiddenLists = $params->get('hiddenlists', array());
$fields = $params->get('fields', array());
$allfields = is_array($fields) ? $fields : explode(',', $fields);
if (!in_array('2', $allfields)) {
    $allfields[] = 2;
}
acym_arrayToInteger($visibleLists);
acym_arrayToInteger($hiddenLists);

$listClass = acym_get('class.list');
$fieldClass = acym_get('class.field');
$allLists = $listClass->getAll();
$allfields = $fieldClass->getFieldsByID($allfields);
$fields = array();
foreach ($allfields as $field) {
    $fields[$field->id] = $field;
}

if (empty($visibleLists) && empty($hiddenLists)) {
    $hiddenLists = array_keys($allLists);
}

if (!empty($visibleLists) && !empty($hiddenLists)) {
    $visibleLists = array_diff($visibleLists, $hiddenLists);
}

if (!empty($identifiedUser->id)) {
    $userLists = $userClass->getUserSubscriptionById($identifiedUser->id);

    $countSub = 0;
    $countUnsub = 0;
    $formLists = array_merge($visibleLists, $hiddenLists);
    foreach ($formLists as $idOneList) {
        if (empty($userLists[$idOneList]) || $userLists[$idOneList]->status == 0) {
            $countSub++;
        } else {
            $countUnsub++;
        }
    }
}

$config = acym_config();
$displayOutside = $params->get('textmode') == '0';
$formName = acym_getModuleFormName();
$formAction = htmlspecialchars_decode(acym_completeLink('frontusers', true, true));

$js = "\n"."acymModule['excludeValues".$formName."'] = [];";
$fieldsToDisplay = array();
foreach ($fields as $field) {
    $fieldsToDisplay[$field->id] = $field->name;
    $js .= "\n"."acymModule['excludeValues".$formName."']['".$field->id."'] = '".$field->name."';";
}
echo "<script type=\"text/javascript\">
        <!--
        $js
        //-->
        </script>";
?>
    <div class="acym_module" id="acym_module_<?php echo $formName; ?>">
        <div class="acym_fulldiv" id="acym_fulldiv_<?php echo $formName; ?>">
            <form id="<?php echo $formName; ?>" name="<?php echo $formName ?>" method="POST" action="<?php echo $formAction; ?>" onsubmit="return submitAcymForm('subscribe','<?php echo $formName; ?>')">
                <div class="acym_module_form">
                    <?php
                    $introText = $params->get('introtext', '');
                    if (!empty($introText)) {
                        echo '<div class="acym_introtext">'.$introText.'</div>';
                    }

                    if ($params->get('mode', 'tableless') == 'tableless') {
                        $view = 'tableless.php';
                    } else {
                        $displayInline = $params->get('mode', 'tableless') != 'vertical';
                        $view = 'default.php';
                    }

                    $app = JFactory::getApplication('site');
                    $template = $app->getTemplate();
                    if (file_exists(str_replace(DS, '/', ACYM_ROOT).'templates/'.$template.'/html/mod_acym/'.$view)) {
                        include(ACYM_ROOT.'templates'.DS.$template.DS.'html'.DS.'mod_acym'.DS.$view);
                    } else {
                        include(__DIR__.DS.'tmpl'.DS.$view);
                    }

                    ?>
                </div>

                <input type="hidden" name="ctrl" value="frontusers"/>
                <input type="hidden" name="task" value="notask"/>
                <input type="hidden" name="option" value="<?php echo ACYM_COMPONENT ?>"/>

                <?php
                $currentEmail = acym_currentUserEmail();
                if (!empty($currentEmail)) {
                    echo '<span style="display:none">{emailcloak=off}</span>';
                }

                $redirectURL = $params->get('redirect', '');
                if (empty($redirectURL)) {
                    echo '<input type="hidden" name="ajax" value="1"/>';
                } else {
                    echo '<input type="hidden" name="ajax" value="0"/>';
                    echo '<input type="hidden" name="redirect" value="'.htmlspecialchars($redirectURL).'"/>';
                }
                ?>
                <input type="hidden" name="acy_source" value="<?php echo htmlspecialchars($params->get('source', '')); ?>"/>
                <input type="hidden" name="hiddenlists" value="<?php echo implode(',', $hiddenLists); ?>"/>
                <input type="hidden" name="fields" value="<?php echo 'name,email'; ?>"/>
                <input type="hidden" name="acyformname" value="<?php echo $formName; ?>"/>

                <?php
                $postText = $params->get('posttext', '');
                if (!empty($postText)) {
                    echo '<div class="acym_posttext">'.$postText.'</div>';
                }
                ?>
            </form>
        </div>
    </div>
<?php

