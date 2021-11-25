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

class acymexportHelper
{
    public function exportCSV($query, $fieldsToExport, $separator = ';', $charset = 'UTF-8')
    {
        @ob_clean();

        $filename = "export_".date('Y-m-d');
        $this->setDownloadHeaders($filename);
        $nbExport = $this->getExportLimit();

        acym_displayErrors();
        $encodingClass = acym_get('helper.encoding');

        $eol = "\r\n";
        $before = '"';
        $separator = '"'.$separator.'"';
        $after = '"';
        echo $before.implode($separator, $fieldsToExport).$after.$eol;

        $start = 0;
        do {
            $users = acym_loadObjectList($query.' LIMIT '.$start.', '.$nbExport, 'id');
            $start += $nbExport;

            if ($users === false) {
                echo $eol.$eol.'Error : '.acym_getDBError();
            }

            if (empty($users)) {
                break;
            }

            foreach ($users as $id => &$oneUser) {
                unset($oneUser->id);

                $dataexport = implode($separator, get_object_vars($oneUser));
                echo $before.$encodingClass->change($dataexport, 'UTF-8', $charset).$after.$eol;
            }

            unset($users);
        } while (true);
    }

    public function setDownloadHeaders($filename = 'export', $extension = 'csv')
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        header("Content-Disposition: attachment; filename=".$filename.".".$extension);
        header("Content-Transfer-Encoding: binary");
    }

    private function getExportLimit()
    {
        if (acym_bytes(ini_get('memory_limit')) > 150000000) {
            return 50000;
        } elseif (acym_bytes(ini_get('memory_limit')) > 80000000) {
            return 15000;
        } else {
            return 5000;
        }
    }
}
