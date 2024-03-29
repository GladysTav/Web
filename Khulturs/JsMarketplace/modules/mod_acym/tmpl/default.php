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
if (!empty($visibleLists)) {
    echo '<table class="acym_lists">';
    foreach ($visibleLists as $myListId) {
        $check = '';
        if (!empty($userLists) && !empty($userLists[$myListId])) {
            $check = 'checked="checked"';
        }

        echo '
                <tr>
                    <td>
                        <label for="acylist_'.$myListId.'_'.$formName.'">
                        <input type="checkbox" class="acym_checkbox" name="subscription[]" id="acylist_'.$myListId.'_'.$formName.'" '.$check.' value="'.$myListId.'"/>'.$allLists[$myListId]->name.'
                        </label>
                    </td>
                </tr>';
    }
    echo '</table>';
}
?>

<table class="acym_form">
    <tr>
        <?php
        foreach ($fields as $field) {
            $fieldDB = empty($field->option->fieldDB) ? '' : json_decode($field->option->fieldDB);
            $field->value = empty($field->value) ? '' : json_decode($field->value);
            $field->option = json_decode($field->option);
            $valuesArray = array();
            if (!empty($field->value)) {
                foreach ($field->value as $value) {
                    if ($value->disabled == 'y') {
                        continue;
                    }
                    $valuesArray[$value->value] = $value->title;
                }
            }
            if (!empty($fieldDB) && !empty($fieldDB->value)) {
                $fromDB = $fieldClass->getValueFromDB($fieldDB);
                foreach ($fromDB as $value) {
                    $valuesArray[$value->value] = $value->title;
                }
            }
            $size = empty($field->option->size) ? '' : 'width:'.$field->option->size.'px';
            echo '<td class="acyfield_'.$field->id.'">';
            echo $fieldClass->displayField($field, $field->default_value, $size, $valuesArray, $displayOutside, true, $identifiedUser, $field->frontend_form);
            echo '</td>';
            if (!$displayInline) {
                echo '</tr><tr>';
            }
        }

        if (empty($identifiedUser->id) && $config->get('captcha', '') == 1) {
            echo '<td class="captchakeymodule" '.($displayOutside && !$displayInline ? 'colspan="2"' : '').'>';
            $captchaClass = acym_get('class.acycaptcha');
            echo $captchaClass->display($formName);
            echo '</td>';
            if (!$displayInline) {
                echo '</tr><tr>';
            }
        }
        ?>

        <td <?php if ($displayOutside && !$displayInline) {
            echo 'colspan="2"';
        } ?> class="acysubbuttons">
            <input type="button" class="btn btn-primary button subbutton" value="<?php echo acym_translation($params->get('subtext', 'ACYM_SUBSCRIBE')); ?>" name="Submit" onclick="try{ return submitAcymForm('subscribe','<?php echo $formName; ?>'); }catch(err){alert('The form could not be submitted '+err);return false;}"/>
            <?php if ($params->get('unsub', '0') == '1' && !empty($countUnsub)) { ?>
                <span style="display: none;"></span>
                <input type="button" class="btn button unsubbutton" value="<?php echo acym_translation($params->get('unsubtext', 'ACYM_UNSUBSCRIBE')); ?>" name="Submit" onclick="try{ return submitAcymForm('unsubscribe','<?php echo $formName; ?>'); }catch(err){alert('The form could not be submitted '+err);return false;}"/>
            <?php } ?>
        </td>
    </tr>
</table>
