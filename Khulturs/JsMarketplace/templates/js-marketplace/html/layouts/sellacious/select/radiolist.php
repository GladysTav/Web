<?php


/** @var  array  $displayData */
extract($displayData);

/**
 * @var string $optKey
 * @var  bool $translate
 * @var string $optText
 * @var  string  $id_text
 */

$html = '<div class="controls">';

foreach ($data as $obj)
{
    $k = $obj->$optKey;
    $t = $translate ? JText::_($obj->$optText) : $obj->$optText;
    $id = (isset($obj->id) ? $obj->id : null);

    $extra = '';
    $id = $id ? $obj->id : $id_text . $k;

    if (is_array($selected))
    {
        foreach ($selected as $val)
        {
            $k2 = is_object($val) ? $val->$optKey : $val;

            if ($k == $k2)
            {
                $extra .= ' selected="selected" ';
                break;
            }
        }
    }
    else
    {
        $extra .= ((string) $k === (string) $selected ? ' checked="checked" ' : '');
    }

    $html .= "\n\t" . '<label for="' . $id . '" id="' . $id . '-lbl" class="radio-ui-box radio">';
    $html .= "\n\t\n\t" . '<input type="radio" name="' . $name . '" id="' . $id . '" value="' . $k . '" ' . $extra
        . $attribs . ' />' . $t;
    $html .= "\n\t" . ' <span class="checkmark"></span></label>';

}

$html .= "\n";
$html .= '</div>';
$html .= "\n";

echo $html;