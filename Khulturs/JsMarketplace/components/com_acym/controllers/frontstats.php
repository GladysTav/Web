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

class FrontStatsController extends acymController
{
    public function openStats()
    {
        $mailId = acym_getVar('int', 'id');
        $userId = acym_getVar('int', 'userid');

        if (!empty($mailId) && !empty($userId)) {
            $userStatClass = acym_get('class.userstat');
            $userStat = $userStatClass->getOneByMailAndUserId($mailId, $userId);
            if (!empty($userStat)) {
                $openUnique = 1;
                if ($userStat->open > 0) {
                    $openUnique = 0;
                }

                $mailStat = array();
                $mailStat['mail_id'] = $mailId;
                $mailStat['open_unique'] = $openUnique;
                $mailStat['open_total'] = 1;

                $mailStatClass = acym_get('class.mailstat');
                $mailStatClass->save($mailStat);

                $userStatToInsert = array();
                $userStatToInsert['user_id'] = $userId;
                $userStatToInsert['mail_id'] = $mailId;
                $userStatToInsert['open'] = 1;
                $userStatToInsert['open_date'] = acym_date('now', 'Y-m-d H:i:s');

                $userStatClass->save($userStatToInsert);
            }
        }

        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("Expires: Wed, 17 Sep 1975 21:32:10 GMT");

        ob_end_clean();

        $picture = ACYM_MEDIA_RELATIVE.'images/statpicture.png';

        $picture = ltrim(str_replace(array('\\', '/'), DS, $picture), DS);

        $imagename = ACYM_ROOT.$picture;
        $handle = fopen($imagename, 'r');
        if (!$handle) {
            exit;
        }

        header("Content-type: image/png");
        $contents = fread($handle, filesize($imagename));
        fclose($handle);
        echo $contents;
        exit;
    }
}
