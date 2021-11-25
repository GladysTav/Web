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
$config = acym_config();
$encodingHelper = acym_get('helper.encoding');
$filename = strtolower(acym_getVar('cmd', 'filename'));
$encoding = acym_getVar('cmd', 'encoding');

$extension = '.'.acym_fileGetExt($filename);
$uploadPath = ACYM_MEDIA.'import'.DS.str_replace(array('.', ' '), '_', substr($filename, 0, strpos($filename, $extension))).$extension;

if (!file_exists($uploadPath)) {
    acym_display(acym_translation_sprintf('ACYM_FAIL_OPEN', '<b><i>'.htmlspecialchars($uploadPath, ENT_COMPAT, 'UTF-8').'</i></b>'), 'error');

    return;
}
$this->config = acym_config();
$this->content = file_get_contents($uploadPath);
if (empty($encoding)) {
    $encoding = $encodingHelper->detectEncoding($this->content);
}
$content = $encodingHelper->change($this->content, $encoding, 'UTF-8');

$content = str_replace(array("\r\n", "\r"), "\n", $content);
$this->lines = explode("\n", $content);

$this->separator = ',';
$listSeparators = array("\t", ';', ',');
foreach ($listSeparators as $sep) {
    if (strpos($this->lines[0], $sep) !== false) {
        $this->separator = $sep;
        break;
    }
}

$nbPreviewLines = 0;
$i = 0;

while (isset($this->lines[$i])) {
    if (empty($this->lines[$i])) {
        unset($this->lines[$i]);
        continue;
    } else {
        $nbPreviewLines++;
    }

    if (strpos($this->lines[$i], '"') !== false) {
        $j = $i + 1;
        $position = -1;

        while ($j < ($i + 30)) {
            $quoteOpened = substr($this->lines[$i], $position + 1, 1) == '"';

            if ($quoteOpened) {
                $nextQuotePosition = strpos($this->lines[$i], '"', $position + 2);
                if ($nextQuotePosition === false) {
                    if (!isset($this->lines[$j])) {
                        break;
                    }

                    $this->lines[$i] .= "\n".rtrim($this->lines[$j], $this->separator);
                    unset($this->lines[$j]);
                    $j++;
                    continue;
                } else {
                    $quoteOpened = false;

                    if (strlen($this->lines[$i]) - 1 == $nextQuotePosition) {
                        break;
                    }

                    $position = $nextQuotePosition + 1;
                }
            } else {
                $nextSeparatorPosition = strpos($this->lines[$i], $this->separator, $position + 1);
                if ($nextSeparatorPosition === false) {
                    break;
                } else { // If found the next separator, add the value in $data and change the position
                    $position = $nextSeparatorPosition;
                }
            }
        }

        $this->lines = array_merge($this->lines);
    }

    if ($nbPreviewLines == 10) {
        break;
    }

    if ($nbPreviewLines != 1) {
        $i++;
        continue;
    }

    if (strpos($this->lines[$i], '@')) {
        $noHeader = 1;
    } else {
        $noHeader = 0;
    }

    $columnNames = explode($this->separator, $this->lines[$i]);
    $nbColumns = count($columnNames);
    if (!empty($i)) {
        unset($this->lines[$i]);
    }
    ksort($this->lines);
}
$this->lines = array_values($this->lines);
$nbLines = count($this->lines);

?>
<div class="table-scroll">
    <table cellspacing="10" cellpadding="10" id="importdata" class="unstriped">
        <?php
        if ($noHeader || !isset($this->lines[1])) {
            $firstValueLine = $columnNames;
        } else {
            $firstValueLine = explode($this->separator, $this->lines[1]);
            foreach ($firstValueLine as &$oneValue) {
                $oneValue = trim($oneValue, '\'" ');
            }
        }

        $fieldAssignment = array();

        $fieldAssignment[] = acym_selectOption("0", acym_translation('ACYM_UNASSIGNED'), "value", "text");
        $fieldAssignment[] = acym_selectOption("1", acym_translation('ACYM_IGNORE'));
        $separator = acym_selectOption("3", '----------------------');
        $separator->disable = true;
        $fieldAssignment[] = $separator;


        $fields = acym_getColumns('user');
        $fields[] = 'listids';
        $fields[] = 'listname';

        foreach ($fields as $oneField) {
            $fieldAssignment[] = acym_selectOption($oneField, $oneField);
        }

        $fields[] = '1';

        echo '<tr>';

        $alreadyFound = array();
        foreach ($columnNames as $key => &$oneColumn) {
            $oneColumn = strtolower(trim($oneColumn, '\'" '));
            $customValue = '';
            $default = acym_getVar('cmd', 'fieldAssignment'.$key);
            if (empty($default) && $default !== 0) {
                $default = (in_array($oneColumn, $fields) ? $oneColumn : '0');

                if (!$default && !empty($firstValueLine)) {
                    if (isset($firstValueLine[$key]) && strpos($firstValueLine[$key], '@')) {
                        $default = 'email';
                    } elseif ($nbColumns == 2) {
                        $default = 'name';
                    }
                }
                if (in_array($default, $alreadyFound)) {
                    $default = '0';
                }
                $alreadyFound[] = $default;
            } elseif ($default == 2) {
                $customValue = acym_getVar('cmd', 'newcustom'.$key);
            }

            echo '<td valign="top">'.acym_select($fieldAssignment, 'fieldAssignment'.$key, $default, 'class="fieldAssignment"', 'value', 'text').'<br />';
        }
        echo '</tr>';

        if (!$noHeader) {
            foreach ($columnNames as &$oneColumn) {
                $oneColumn = htmlspecialchars($oneColumn, ENT_COMPAT | ENT_IGNORE, 'UTF-8');
            }
            echo '<tr class="acym__users__import__generic__column_name"><td><b>'.implode('</b></td><td><b>', $columnNames).'</b></td></tr>';
        }

        for ($i = 1 - $noHeader; $i < 11 - $noHeader && $i < $nbLines; $i++) {
            $values = explode($this->separator, $this->lines[$i]);

            echo '<tr>';
            foreach ($values as &$oneValue) {
                $oneValue = htmlspecialchars(trim($oneValue, '\'" '), ENT_COMPAT | ENT_IGNORE, 'UTF-8');
                echo '<td>'.$oneValue.'</td>';
            }
            echo '</tr>';
        }
        ?>
    </table>
</div>
