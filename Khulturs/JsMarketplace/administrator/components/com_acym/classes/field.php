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

class acymfieldClass extends acymClass
{
    var $table = 'field';
    var $pkey = 'id';

    public function getMatchingFields()
    {
        $query = 'SELECT * FROM #__acym_field ORDER BY '.acym_secureDBColumn('ordering').' '.strtoupper('ASC');

        return acym_loadObjectList($query, 'id');
    }

    public function getOneFieldByID($id)
    {
        $query = 'SELECT * FROM #__acym_field WHERE `id`='.intval($id);

        return acym_loadObject($query);
    }

    public function getFieldsByID($ids)
    {
        $query = 'SELECT * FROM #__acym_field WHERE `id` IN('.implode(',', $ids).') ORDER BY '.acym_secureDBColumn('ordering').' '.strtoupper('ASC');

        return acym_loadObjectList($query);
    }

    public function getOrdering()
    {
        $query = 'SELECT COUNT(id) as ordering_number FROM #__acym_field';

        return acym_loadObject($query);
    }

    public function getAllfieldsNameId()
    {
        $query = 'SELECT name, id FROM #__acym_field';

        return acym_loadObjectList($query);
    }

    public function getAllFieldsForUser()
    {
        $query = 'SELECT * FROM #__acym_field WHERE id NOT IN (1, 2) ORDER BY '.acym_secureDBColumn('ordering').' '.strtoupper('ASC');

        return acym_loadObjectList($query, 'id');
    }

    public function getAllFieldsForModuleFront()
    {
        $query = 'SELECT * FROM #__acym_field WHERE id != 2 ORDER BY '.acym_secureDBColumn('ordering').' '.strtoupper('ASC');

        return acym_loadObjectList($query, 'id');
    }

    public function getFieldsValueByUserId($userId)
    {
        $query = 'SELECT * FROM #__acym_user_has_field  WHERE user_id = '.intval($userId);

        return acym_loadObjectList($query, 'field_id');
    }

    public function getValueFromDB($fieldDB)
    {
        $query = 'SELECT '.$fieldDB->value.' as value, '.$fieldDB->title.' as title
                    FROM '.$fieldDB->database.'.'.$fieldDB->table;
        $query .= empty($fieldDB->where_value) ? '' : ' WHERE '.$fieldDB->where.' '.$fieldDB->where_sign.' '.$fieldDB->where_value;
        $query .= ' ORDER BY '.$fieldDB->order_by.' '.$fieldDB->sort_order;

        return acym_loadObjectList($query);
    }

    public function store($fields, $userID)
    {
        if (!empty($_FILES['customField'])) {
            $uploadFolder = trim(acym_cleanPath(html_entity_decode(acym_getFilesFolder())), DS.' ').DS;
            $uploadPath = acym_cleanPath(ACYM_ROOT.$uploadFolder.'userfiles'.DS);
            foreach ($_FILES['customField']['tmp_name'] as $key => $value) {
                if (empty($value[0])) {
                    continue;
                }
                $error = acym_uploadFile($value[0], $uploadPath.$_FILES['customField']['name'][$key][0]);
                if (!$error) {
                    acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);

                    return;
                }
                $fields[$key] = $_FILES['customField']['name'][$key][0];
            }
        }
        foreach ($fields as $id => $field) {
            $query = 'INSERT INTO #__acym_user_has_field (`user_id`, `field_id`, `value`) VALUES ';
            $field = is_array($field) ? json_encode($field) : $field;
            $query .= '('.$userID.', '.$id.', \''.$field.'\')';
            $query .= ' ON DUPLICATE KEY UPDATE `value`=\''.$field.'\'';
            acym_query($query);
        }
    }

    public function getAllfieldBackEndListingByUserIds($ids, $fields, $forBackEnd = false)
    {
        $query = 'SELECT field.type as type, field.name as field_name, user_field.user_id as user_id, user_field.field_id as field_id, user_field.value as field_value FROM #__acym_user_has_field AS user_field
                    LEFT JOIN #__acym_field AS field ON user_field.field_id = field.id
                    '.($forBackEnd ? 'WHERE field.backend_listing = 1' : '');
        $query .= is_array($ids) ? ' AND user_field.user_id IN ('.implode(',', $ids).')' : ' AND user_field.user_id = '.$ids;
        $query .= is_array($ids) ? ' AND user_field.field_id IN ('.implode(',', $fields).')' : ' AND user_field.field_id = '.$fields;

        $fieldValues = array();
        foreach (acym_loadObjectList($query) as $one) {
            if ($one->type == 'date') {
                $fieldValues[$one->field_id.$one->user_id] = implode('/', json_decode($one->field_value));
            } else if ($one->type == 'phone') {
                $value = json_decode($one->field_value);
                $fieldValues[$one->field_id.$one->user_id] = empty($value->code) ? '' : '+'.$value->code.' '.$value->phone;
            } else {
                $fieldValues[$one->field_id.$one->user_id] = is_array(json_decode($one->field_value)) ? implode(', ', json_decode($one->field_value)) : $one->field_value;
            }
        }

        return $fieldValues;
    }

    public function getAllFieldsBackendListing()
    {
        $query = 'SELECT id, name FROM #__acym_field WHERE backend_listing = 1 AND id NOT IN (1, 2)';

        $return = array(
            'names' => array(),
            'ids' => array(),
        );

        foreach (acym_loadObjectList($query) as $one) {
            $return['names'][] = $one->name;
            $return['ids'][] = $one->id;
        }

        return $return;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }
        acym_arrayToInteger($elements);

        if (empty($elements)) {
            return 0;
        }

        acym_query('DELETE FROM #__acym_user_has_field WHERE field_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function displayField($field, $defaultValue, $size, $valuesArray, $displayOutside = true, $displayFront = false, $user = null, $dontDisplay = 1, $displayIf = '')
    {
        if ($dontDisplay == 0) {
            return '';
        }
        $cmsUser = false;
        if ($displayFront && !empty($user->id)) {
            $cmsUser = !empty($user->cms_id) ? true : false;
            if ($field->id == 1) {
                $defaultValue = $user->name;
            } else if ($field->id == 2) {
                $defaultValue = $user->email;
            } else {
                $allValues = array();
                $defaultUserValue = $this->getFieldsValueByUserId($user->id);
                if (!empty($defaultUserValue)) {
                    foreach ($defaultUserValue as $one) {
                        $allValues[$one->field_id] = $one->value;
                    }
                }

                if(isset($allValues[$field->id])){
                    $defaultValue = is_null(json_decode($allValues[$field->id])) ? $allValues[$field->id] : json_decode($allValues[$field->id]);
                }
            }
        }
        $return = '';

        $messageRequired = empty($field->option->error_message) ? acym_translation_sprintf('ACYM_DEFAULT_REQUIRED_MESSAGE', $field->name) : acym_translation($field->option->error_message);
        $requiredJson = json_encode(array('type' => $field->type, 'message' => $messageRequired));
        $required = $field->required ? 'data-required="'.htmlspecialchars($requiredJson, ENT_QUOTES, 'UTF-8').'"' : '';

        if ($field->id == 1) {
            $return .= ($displayOutside ? '<label class="cell"><h6 class="acym__users__creation__fields__title">'.acym_translation('ACYM_NAME').'</h6>' : '').'<input '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' type="text" class="cell" name="user[name]" placeholder="'.acym_translation('ACYM_NAME').'" value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->id == 2) {
            $return .= ($displayOutside ? '<label class="cell"><h6 class="acym__users__creation__fields__title">'.acym_translation('ACYM_EMAIL').'</h6>' : '').'<input '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' id="acym__user__edit__email" required type="email" class="cell" name="user[email]" '.($displayFront && $cmsUser ? 'disabled' : '').' placeholder="'.acym_translation('ACYM_EMAIL').'" value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'text') {
            $field->option->authorized_content->message = $field->option->error_message_invalid;
            $authorizedContent = 'data-authorized-content=\''.json_encode($field->option->authorized_content).'\'';
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>' : '').'<input '.$required.' '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' '.$authorizedContent.' style="'.$size.'" type="text" name="customField['.$field->id.']" value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'textarea') {
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>' : '').'<textarea '.$required.' rows="'.$field->option->rows.'" cols="'.$field->option->columns.'" name="customField['.$field->id.']">'.(empty($defaultValue) ? $field->name : $defaultValue).'</textarea>'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'radio') {
            if ($displayFront) {
                $return .= '<div '.$displayIf.' class="cell acym__content"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>';
                $defaultValue = empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue);
                foreach ($valuesArray as $key => $value) {
                    $defaultValue = $defaultValue == $key ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$required.' type="radio" name="customField['.$field->id.']" value="'.$key.'" '.$defaultValue.'></label>';
                }
                $return .= '</div>';
            } else {
                $return .= '<div '.$displayIf.' class="cell acym__content"><label class="cell"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>'.acym_radio($valuesArray, 'customField['.$field->id.'][]', empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue), null, ($field->required ? array('data-required' => $requiredJson) : array())).'</label></div>';
            }
        } else if ($field->type == 'checkbox') {
            $return .= '<div '.$displayIf.' class="cell margin-top-1 acym__content"><h6 class="acym__users__creation__fields__title margin-bottom-1">'.$field->name.'</h6>';
            if ($displayFront) {
                $defaultValue = empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue);
                foreach ($valuesArray as $key => $value) {
                    $defaultValue = $defaultValue == $key ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$required.' type="checkbox" name="customField['.$field->id.']" value="'.$key.'" '.$defaultValue.'></label>';
                }
            } else {
                if (!empty($defaultValue) && !is_object($defaultValue)) {
                    $temporaryObject = new stdClass();
                    $temporaryObject->$defaultValue = 'on';
                    $defaultValue = $temporaryObject;
                }
                $defaultValue = is_object($defaultValue) ? $defaultValue : new stdClass();
                foreach ($valuesArray as $key => $value) {
                    $return .= !empty($defaultValue->$key) ? '<label>'.$value.'<input '.$required.' type="checkbox" name="customField['.$field->id.']['.$value.']" class="acym__users__creation__fields__checkbox" checked></label>' : '<label class="cell margin-top-1">'.$value.'<input type="checkbox" name="customField['.$field->id.']['.$value.']" class="acym__users__creation__fields__checkbox"></label>';
                }
            }
            $return .= '</div>';
        } else if ($field->type == 'single_dropdown') {
            if (!$displayOutside) {
                array_unshift($valuesArray, $field->name);
            }
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>' : '').acym_select($valuesArray, 'customField['.$field->id.']', empty($defaultValue) ? '' : $defaultValue, 'class="acym__custom__fields__select__form" style="'.$size.'"'.$required, 'value', 'text').($displayOutside ? '</label>' : '');
        } else if ($field->type == 'multiple_dropdown') {
            $defaultValue = is_array($defaultValue) ? $defaultValue : array($defaultValue);
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>' : '').acym_selectMultiple($valuesArray, 'customField['.$field->id.']', empty($defaultValue) ? array() : $defaultValue, $field->required ? array('data-required' => $requiredJson, 'class' => 'acym__custom__fields__select__multiple__form', 'style' => $size) : array('class' => 'acym__custom__fields__select__multiple__form', 'style' => $size)).($displayOutside ? '</label>' : '');
        } else if ($field->type == 'date') {
            $defaultValue = is_array($defaultValue) ? implode('/', $defaultValue) : $defaultValue;

            $return .= '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>'.acym_displayDateFormat($field->option->format, 'customField['.$field->id.'][]', $defaultValue).'</label>';
        } else if ($field->type == 'file') {
            $defaultValue = is_array($defaultValue) ? $defaultValue[0] : $defaultValue;
            if ($displayFront) {
                $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><h6 class="acym__users__creation__fields__title cell">'.$field->name.'</h6>' : '').'<input '.$required.' type="file" name="customField['.$field->id.']"></label>';
            } else {
                $return .= acym_inputFile('customField['.$field->id.'][]', $defaultValue, '', '', $required);
            }
        } else if ($field->type == 'phone') {
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><h6 class="acym__users__creation__fields__title cell">'.$field->name.'</h6>' : '').'<div class="medium-3">'.acym_generateCountryNumber('customField['.$field->id.'][code]', empty($defaultValue) ? '' : $defaultValue->code).'</div><input '.$required.' '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' style="'.$size.'" class="medium-9 cell" type="tel" name="customField['.$field->id.'][phone]" value="'.(empty($defaultValue) ? '' : $defaultValue->phone).'" data-format="'.$field->option->format.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'custom_text') {
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><h6 class="acym__users__creation__fields__title">'.$field->name.'</h6>' : '').'<textarea name="customField['.$field->id.']" rows="10" cols="10" readonly>'.$field->option->custom_text.'</textarea>'.($displayOutside ? '</label>' : '');
        }

        return $return;
    }
}

