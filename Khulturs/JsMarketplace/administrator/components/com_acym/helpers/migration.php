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

class acymmigrationHelper
{
    private $errors = array();


    private $result = array(
        "isOk" => true,
        "errorInsert" => false,
        "errorClean" => false,
    );

    public function doConfigMigration()
    {
        $this->doElementMigration("config");

        return $this->result;
    }

    public function doTemplatesMigration()
    {
        $migrateMails = acym_getVar("int", "migratemails");
        if (!$migrateMails) {
            if (!$this->doCleanTable("Mail")) {
                return $this->result;
            }
        }

        $this->doElementMigration("templates");

        return $this->result;
    }

    public function doMailsMigration()
    {
        $migrateMailStats = acym_getVar("int", "migratemailstats");
        $migrateMailHasLists = acym_getVar("int", "migratemailhaslists");
        $migrateLists = acym_getVar("int", "migratelists");

        if (!$this->doCleanTable("Mail")) {
            return $this->result;
        }

        $params = array(
            "migrateMailStats" => $migrateMailStats,
            "migrateMailHasLists" => $migrateMailHasLists,
            "migrateLists" => $migrateLists,
        );

        $this->doElementMigration("mails", $params);

        return $this->result;
    }

    public function doListsMigration()
    {
        if (!$this->doCleanTable("List")) {
            return $this->result;
        }

        $this->doElementMigration("lists");

        return $this->result;
    }

    public function doUsersMigration()
    {
        if (!$this->doCleanTable("User")) {
            return $this->result;
        }

        $this->doElementMigration("users");

        return $this->result;
    }

    public function doSubscriptionsMigration()
    {
        $this->doElementMigration("subscriptions");

        return $this->result;
    }

    public function migrateConfig()
    {
        $fieldsMatchMailSettings = [
            "add_names" => "add_names",
            "bounce_email" => "bounce_email",
            "charset" => "charset",
            "dkim" => "dkim",
            "dkim_domain" => "dkim_domain",
            "dkim_identity" => "dkim_identity",
            "dkim_passphrase" => "dkim_passphrase",
            "dkim_private" => "dkim_private",
            "dkim_public" => "dkim_public",
            "dkim_selector" => "dkim_selector",
            "elasticemail_password" => "elasticemail_password",
            "elasticemail_port" => "elasticemail_port",
            "elasticemail_username" => "elasticemail_username",
            "embed_files" => "embed_files",
            "embed_images" => "embed_images",
            "encoding_format" => "encoding_format",
            "from_email" => "from_email",
            "from_name" => "from_name",
            "mailer_method" => "mailer_method",
            "multiple_part" => "multiple_part",
            "reply_email" => "replyto_email",
            "reply_name" => "replyto_name",
            "sendmail_path" => "sendmail_path",
            "smtp_auth" => "smtp_auth",
            "smtp_host" => "smtp_host",
            "smtp_keepalive" => "smtp_keepalive",
            "smtp_password" => "smtp_password",
            "smtp_port" => "smtp_port",
            "smtp_secured" => "smtp_secured",
            "smtp_username" => "smtp_username",
            "special_chars" => "special_chars",
            "ssl_links" => "use_https",
        ];

        $fieldsMatchQueueProcess = [
            "cron_frequency" => "cron_frequency",
            "cron_fromip" => "cron_fromip",
            "cron_last" => "cron_last",
            "cron_report" => "cron_report",
            "cron_savereport" => "cron_savereport",
            "cron_sendreport" => "cron_sendreport",
            "cron_sendto" => "cron_sendto",
            "queue_nbmail" => "queue_nbmail",
            "queue_nbmail_auto" => "queue_nbmail_auto",
            "queue_pause" => "queue_pause",
            "queue_try" => "queue_try",
            "queue_type" => "queue_type",
            "sendorder" => "sendorder",
        ];

        $fieldsMatchSubscription = [
            "require_confirmation" => "require_confirmation",
        ];

        $fieldsMatchFeatures = [

        ];

        $fieldsMatchSecurity = [
            "allowedfiles" => "allowed_files",
            "email_checkdomain" => "email_checkdomain",
            "recaptcha_secretkey" => "recaptcha_secretkey",
            "recaptcha_sitekey" => "recaptcha_sitekey",
            "security_key" => "security_key",
        ];

        $fieldsMatchLanguages = [

        ];

        $fieldsMatchNotUsed = [
            "allow_visitor" => "allow_visitor",
            "confirm_redirect" => "confirm_redirect",
            "confirmation_message" => "confirmation_message",
            "cron_fullreport" => "cron_fullreport",
            "cron_next" => "cron_next",
            "css_backend" => "css_backend",
            "css_frontend" => "css_frontend",
            "forward" => "forward",
            "hostname" => "hostname",
            "notification_accept" => "notification_accept",
            "notification_confirm" => "notification_confirm",
            "notification_created" => "notification_created",
            "notification_refuse" => "notification_refuse",
            "notification_unsuball" => "notification_unsuball",
            "priority_followup" => "priority_followup",
            "priority_newsletter" => "priority_newsletter",
            "subscription_message" => "subscription_message",
            "unsub_message" => "unsub_message",
            "unsub_reasons" => "unsub_reasons",
            "unsub_redirect" => "unsub_redirect",
            "use_sef" => "use_sef",
            "welcome_message" => "welcome_message",
            "word_wrapping" => "word_wrapping",
        ];

        $fieldsMatch = array_merge($fieldsMatchMailSettings, $fieldsMatchQueueProcess, $fieldsMatchSubscription, $fieldsMatchFeatures, $fieldsMatchSecurity, $fieldsMatchLanguages, $fieldsMatchNotUsed);

        $queryGetValuesPreviousVersion = "SELECT `namekey`, `value` FROM #__acymailing_config WHERE `namekey` IN ('".implode("','", array_keys($fieldsMatch))."')";

        $dataPrevious = acym_loadObjectList($queryGetValuesPreviousVersion);

        $valuesToInsert = array();

        foreach ($dataPrevious as $value) {
            switch ($value->namekey) {
                case "queue_type":
                    switch ($value->value) {
                        case "onlyauto":
                            $value->value = "auto";
                            break;
                        case "auto":
                            $value->value = "automan";
                            break;
                    }
                    break;

                case "mailer_method":
                    $sending_platform = $value->value == "smtp" || $value->value == "elasticemail" ? "external" : "server";
                    $valuesToInsert[] = "('sending_platform','".$sending_platform."')";
                    break;
            }

            $value->namekey = $fieldsMatch[$value->namekey];
            $valuesToInsert[] = "(".acym_escapeDB($value->namekey).",".acym_escapeDB($value->value).")";
        }

        $query = "REPLACE INTO #__acym_configuration VALUES ".implode($valuesToInsert, ",").";";

        try {
            $result = acym_query($query);
        } catch (Exception $e) {
            $this->errors[] = acym_getDBError();

            return false;
        }

        if ($result === null) {
            $this->errors[] = acym_getDBError();

            return false;
        } else {
            return $result;
        }
    }

    public function migrateTemplates()
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetTemplates = "SELECT `tempid`, `name`, `body`, `styles`, `subject`, `stylesheet`, `fromname`, `fromemail`, `replyname`, `replyemail` FROM #__acymailing_template LIMIT ".$numberValue.", 500";

            $templates = acym_loadObjectList($queryGetTemplates);

            if (count($templates) == "0") {
                break;
            }

            $valuesToInsert = array();

            foreach ($templates as $oneTemplate) {
                $oneTemplateStyles = unserialize($oneTemplate->styles);

                foreach ($oneTemplateStyles as $key => $value) {
                    if (strpos($key, "tag_") !== false) {
                        $tag = str_replace("tag_", "", $key);
                        $styleDeclaration = $tag."{".$value."}";
                    } else if (strpos($key, "color_bg") !== false) {
                        $styleDeclaration = "";
                    } else {
                        $styleDeclaration = ".".$key."{".$value."}";
                    }

                    $oneTemplate->stylesheet .= $styleDeclaration;
                }

                $valuesToInsert[] = "(".acym_escapeDB(empty($oneTemplate->name) ? acym_translation('ACYM_MIGRATED_TEMPLATE').' '.time() : $oneTemplate->name).", ".acym_escapeDB(acym_date('now', 'Y-m-d H:i:s')).", 0, 0, 'standard', ".acym_escapeDB(empty($oneTemplate->body) ? "" : $oneTemplate->body).", ".acym_escapeDB($oneTemplate->subject).", 1, ".acym_escapeDB($oneTemplate->fromname).", ".acym_escapeDB($oneTemplate->fromemail).", ".acym_escapeDB($oneTemplate->replyname).", ".acym_escapeDB($oneTemplate->replyemail).",".acym_escapeDB($oneTemplate->stylesheet).",  '".acym_currentUserId()."')";
            }

            if (empty($valuesToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_mail (`name`, `creation_date`, `drag_editor`, `library`, `type`, `body`, `subject`, `template`, `from_name`, `from_email`, `reply_to_name`, `reply_to_email`, `stylesheet`, `creator_id`) VALUES ".implode($valuesToInsert, ',').";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }

            $numberValue += 500;
        } while (!empty($templates));

        return $result;
    }

    public function migrateMails($params = array())
    {
        $numberValue = 0;
        $result = 0;
        $idsMigratedMails = array();

        $migrateMailStats = empty($params["migrateMailStats"]) ? 0 : 1;
        $migrateMailHasLists = empty($params["migrateMailHasLists"]) ? 0 : 1;
        $migrateList = empty($params["migrateLists"]) ? 0 : 1;

        do {
            $queryGetMails = 'SELECT mail.`mailid`,
                                mail.`created`, 
                                mail.`type`, 
                                mail.`body`, 
                                mail.`subject`, 
                                mail.`fromname`, 
                                mail.`fromemail`, 
                                mail.`replyname`, 
                                mail.`replyemail`, 
                                mail.`bccaddresses`, 
                                mail.`tempid`, 
                                mail.`senddate`, 
                                mail.`published`, 
                                template.`stylesheet`, 
                                template.`styles` 
                        FROM #__acymailing_mail mail 
                        LEFT JOIN #__acymailing_template template 
                        ON mail.tempid = template.tempid
                        LIMIT '.$numberValue.', 500';

            $mails = acym_loadObjectList($queryGetMails);

            if (count($mails) == "0") {
                break;
            }

            $mailsToInsert = array();
            $campaignsToInsert = array();

            foreach ($mails as $oneMail) {
                if (empty($oneMail->mailid)) {
                    continue;
                }

                switch ($oneMail->type) {
                    case "welcome":
                        $mailType = "welcome";
                        break;
                    case "unsub":
                        $mailType = "unsubscribe";
                        break;
                    case "news":
                    case "followup":
                        $mailType = "standard";
                        break;
                    default:
                        $mailType = "invalid";
                        break;
                }

                if ($mailType == "invalid") {
                    continue;
                }

                $mailStylesheet = $oneMail->stylesheet;

                $templateStyles = unserialize($oneMail->styles);

                if ($templateStyles !== false) {
                    foreach ($templateStyles as $key => $value) {
                        if (strpos($key, "tag_") !== false) {
                            $tag = str_replace("tag_", "", $key);
                            $styleDeclaration = $tag."{".$value."}";
                        } else if (strpos($key, "color_bg") !== false) {
                            $styleDeclaration = "";
                        } else {
                            $styleDeclaration = ".".$key."{".$value."}";
                        }
                        $mailStylesheet .= $styleDeclaration;
                    }
                }

                $mail = [
                    "id" => intval($oneMail->mailid),
                    "name" => acym_escapeDB($oneMail->subject),
                    "creation_date" => acym_escapeDB(empty($oneMail->created) ? acym_date('now', 'Y-m-d H:i:s') : acym_date($oneMail->created, 'Y-m-d H:i:s')),
                    "drag_editor" => 0,
                    "library" => 0,
                    "type" => acym_escapeDB($mailType),
                    "body" => acym_escapeDB($oneMail->body),
                    "subject" => acym_escapeDB($oneMail->subject),
                    "template" => $mailType == "welcome" || $mailType == "unsubscribe" ? 1 : 0,
                    "from_name" => acym_escapeDB($oneMail->fromname),
                    "from_email" => acym_escapeDB($oneMail->fromemail),
                    "reply_to_name" => acym_escapeDB($oneMail->replyname),
                    "reply_to_email" => acym_escapeDB($oneMail->replyemail),
                    "bcc" => acym_escapeDB($oneMail->bccaddresses),
                    "stylesheet" => acym_escapeDB($mailStylesheet),
                    "creator_id" => empty($oneMail->userid) ? acym_currentUserId() : $oneMail->userid,
                ];

                if ($mailType == "standard") {
                    $isSent = !empty(acym_loadResult("SELECT COUNT(mailid) FROM #__acymailing_stats WHERE mailid = ".$oneMail->mailid));

                    $campaign = [
                        "sending_date" => !empty($oneMail->senddate) ? "'".acym_date($oneMail->senddate, 'Y-m-d H:i:s')."'" : "NULL",
                        "draft" => intval(!$isSent),
                        "active" => empty($oneMail->published) ? 0 : $oneMail->published,
                        "mail_id" => $oneMail->mailid,
                        "scheduled" => intval(!$isSent && ($oneMail->senddate > time())),
                        "sent" => intval($isSent),
                    ];
                    $campaignsToInsert[] = "(".implode($campaign, ", ").")";
                }

                $mailsToInsert[] = "(".implode($mail, ", ").")";


                if ($migrateMailStats) {
                    $idsMigratedMails[] = $oneMail->mailid;
                }
            }

            if (empty($mailsToInsert)) {
                return true;
            }

            $queryMailsInsert = "INSERT INTO #__acym_mail (`id`, `name`, `creation_date`, `drag_editor`, `library`, `type`, `body`, `subject`, `template`, `from_name`, `from_email`, `reply_to_name`, `reply_to_email`, `bcc`, `stylesheet`, `creator_id`) VALUES ".implode($mailsToInsert, ',').";";

            try {
                $resultMail = acym_query($queryMailsInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultMail === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultMail;
            }

            if (!empty($campaignsToInsert)) {
                $queryCampaignInsert = "INSERT INTO #__acym_campaign (`sending_date`, `draft`, `active`, `mail_id`, `scheduled`, `sent`) VALUES ".implode($campaignsToInsert, ",").";";

                try {
                    $resultCampaign = acym_query($queryCampaignInsert);
                } catch (Exception $e) {
                    $this->errors[] = acym_getDBError();

                    return false;
                }

                if ($resultCampaign === null) {
                    $this->errors[] = acym_getDBError();

                    return false;
                }
            }
            $numberValue += 500;
        } while (!empty($mails));

        if($migrateList){
            if($this->migrateWelcomeIdAndUnsubId($idsMigratedMails) === false){
                return false;
            }
        }

        if ($migrateMailStats) {
            if ($this->migrateMailStats($idsMigratedMails) === false) {
                return false;
            }
        }

        if($migrateMailHasLists){
            if ($this->migrateMailHasLists($idsMigratedMails) === false){
                return false;
            }
        }

        return $result;
    }

    public function migrateLists()
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetLists = "SELECT `listid`, `name`, `published`, `visible`, `color`, `userid` FROM #__acymailing_list LIMIT ".$numberValue.", 500";

            $lists = acym_loadObjectList($queryGetLists);

            if (count($lists) == "0") {
                break;
            }

            $listsToInsert = array();

            foreach ($lists as $oneList) {
                if (empty($oneList->listid)) {
                    continue;
                }

                $list = [
                    "id" => intval($oneList->listid),
                    "name" => acym_escapeDB($oneList->name),
                    "active" => empty($oneList->published) ? 0 : 1,
                    "visible" => acym_escapeDB($oneList->visible),
                    "clean" => 0,
                    "color" => acym_escapeDB($oneList->color),
                    "creation_date" => acym_escapeDB(acym_date('now', 'Y-m-d H:i:s')),
                    "cms_user_id" => empty($oneList->userid) ? acym_currentUserId() : intval($oneList->userid),
                ];

                $listsToInsert[] = "(".implode($list, ", ").")";
            }

            if (empty($listsToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_list (`id`, `name`, `active`, `visible`, `clean`, `color`, `creation_date`, `cms_user_id`) VALUES ".implode($listsToInsert, ",").";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }

            $numberValue += 500;
        } while (!empty($lists));

        return $result;
    }

    public function migrateUsers()
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetUsers = "SELECT `subid`, `name`, `email`, `created`, `enabled`, `userid`, `source`, `confirmed`, `key` FROM #__acymailing_subscriber LIMIT ".$numberValue.", 500";

            $users = acym_loadObjectList($queryGetUsers);

            if (count($users) == "0") {
                break;
            }

            $usersToInsert = array();

            foreach ($users as $oneUser) {
                if (empty($oneUser->subid)) {
                    continue;
                }

                $user = [
                    "id" => intval($oneUser->subid),
                    "name" => acym_escapeDB($oneUser->name),
                    "email" => acym_escapeDB($oneUser->email),
                    "creation_date" => acym_escapeDB(empty($oneUser->created) ? acym_date('now', 'Y-m-d H:i:s') : acym_date($oneUser->created, 'Y-m-d H:i:s')),
                    "active" => acym_escapeDB($oneUser->enabled),
                    "cms_id" => intval($oneUser->userid),
                    "source" => acym_escapeDB($oneUser->source),
                    "confirmed" => acym_escapeDB($oneUser->confirmed),
                    "key" => acym_escapeDB($oneUser->key),
                ];

                $usersToInsert[] = "(".implode($user, ", ").")";
            }

            if (empty($usersToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_user (`id`, `name`, `email`, `creation_date`, `active`, `cms_id`, `source`, `confirmed`, `key`) VALUES ".implode($usersToInsert, ", ").";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }
            $numberValue += 500;
        } while (!empty($users));

        return $result;
    }

    public function migrateSubscriptions()
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetSubscriptions = "SELECT `listid`, `subid`, `subdate`, `unsubdate`, `status` FROM #__acymailing_listsub LIMIT ".$numberValue.", 500";

            $subscriptions = acym_loadObjectList($queryGetSubscriptions);

            if (count($subscriptions) == 0) {
                break;
            }

            $subscriptionsToInsert = array();

            foreach ($subscriptions as $oneSubscription) {
                if (empty($oneSubscription->subid) || empty($oneSubscription->listid)) {
                    continue;
                }

                $subscription = [
                    "user_id" => acym_escapeDB($oneSubscription->subid),
                    "list_id" => acym_escapeDB($oneSubscription->listid),
                    "status" => acym_escapeDB($oneSubscription->status == -1 ? 0 : $oneSubscription->status),
                    "subscription_date" => empty($oneSubscription->subdate) ? "NULL" : acym_escapeDB(acym_date($oneSubscription->subdate, 'Y-m-d H:i:s')),
                    "unsubscribe_date" => empty($oneSubscription->unsubdate) ? "NULL" : acym_escapeDB(acym_date($oneSubscription->unsubdate, 'Y-m-d H:i:s')),
                ];

                $subscriptionsToInsert[] = "(".implode($subscription, ", ").")";
            }

            if (empty($subscriptionsToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_user_has_list (`user_id`, `list_id`, `status`, `subscription_date`, `unsubscribe_date`) VALUES ".implode($subscriptionsToInsert, ", ").";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }
            $numberValue += 500;
        } while (!empty($subscriptions));

        return $result;
    }

    public function migrateMailHasLists($idsMigratedMails = array())
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetMailHasLists = "SELECT `mailid`, `listid` FROM #__acymailing_listmail LIMIT ".$numberValue.", 500";

            $mailHasLists = acym_loadObjectList($queryGetMailHasLists);

            if (count($mailHasLists) == 0) {
                break;
            }
            $mailHasListsToInsert = array();

            foreach ($mailHasLists as $oneMailHasLists) {
                if (empty($oneMailHasLists->mailid) || empty($oneMailHasLists->listid) || !in_array($oneMailHasLists->mailid, $idsMigratedMails)) {
                    continue;
                }

                $mailHasListsToInsert[] = "(".acym_escapeDB($oneMailHasLists->mailid).",".acym_escapeDB($oneMailHasLists->listid).")";
            }

            if (empty($mailHasListsToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_mail_has_list (`mail_id`, `list_id`) VALUES ".implode($mailHasListsToInsert, ',').";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }
            $numberValue += 500;
        } while (!empty($mailHasLists));

        return $result;
    }

    public function migrateMailStats($idsMigratedMails = array())
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetStats = "SELECT `mailid`, `senthtml`, `senttext`, `senddate`, `fail`, `openunique`, `opentotal` FROM #__acymailing_stats LIMIT ".$numberValue.", 500";

            $stats = acym_loadObjectList($queryGetStats);

            if (count($stats) == '0') {
                break;
            }

            $statsToInsert = array();

            foreach ($stats as $oneStat) {
                if (empty($oneStat->mailid) || !in_array($oneStat->mailid, $idsMigratedMails)) {
                    continue;
                }

                $totalSent = intval($oneStat->senthtml) + intval($oneStat->senttext);
                $stat = [
                    "mail_id" => acym_escapeDB($oneStat->mailid),
                    "total_subscribers" => acym_escapeDB($totalSent + $oneStat->fail),
                    "sent" => acym_escapeDB($totalSent),
                    "send_date" => empty($oneStat->senddate) ? "NULL" : acym_escapeDB(acym_date($oneStat->senddate, 'Y-m-d H:i:s')),
                    "fail" => acym_escapeDB($oneStat->fail),
                    "open_unique" => acym_escapeDB(intval($oneStat->openunique)),
                    "open_total" => acym_escapeDB(intval($oneStat->opentotal)),
                ];
                $statsToInsert[] = "(".implode($stat, ", ").")";
            }

            if (empty($statsToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_mail_stat (`mail_id`, `total_subscribers`, `sent`, `send_date`, `fail`, `open_unique`, `open_total`) VALUES ".implode($statsToInsert, ",").";";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }
            $numberValue += 500;
        } while (!empty($stats));

        return $result;
    }

    public function migrateWelcomeIdAndUnsubId(array $migratedMailsId)
    {
        $numberValue = 0;
        $result = 0;

        do {
            $queryGetIds = "SELECT `listid`, `welmailid`, `unsubmailid` FROM #__acymailing_list LIMIT ".$numberValue.", 500";

            $ids = acym_loadObjectList($queryGetIds);

            if (count($ids) == 0) {
                break;
            }

            $idsToInsert = array();

            foreach ($ids as $oneId) {
                if (empty($oneId->listid)) {
                    continue;
                }

                $welId = empty($oneId->welmailid) ? "NULL" : (in_array($oneId->welmailid, $migratedMailsId) ? $oneId->welmailid : "NULL");
                $unsId = empty($oneId->unsubmailid) ? "NULL" : (in_array($oneId->unsubmailid, $migratedMailsId) ? $oneId->unsubmailid : "NULL");

                $id = [
                    "id" => $oneId->listid,
                    "welcome_id" => $welId,
                    "unsubscribe_id" => $unsId,
                ];

                $idsToInsert[] = "(".implode($id, ', ').")";
            }

            if (empty($idsToInsert)) {
                return true;
            }

            $queryInsert = "INSERT INTO #__acym_list(`id`, `welcome_id`, `unsubscribe_id`) VALUES ".implode($idsToInsert, ",")." ON DUPLICATE KEY UPDATE `welcome_id` = VALUES(`welcome_id`), `unsubscribe_id` = VALUES(`unsubscribe_id`)";

            try {
                $resultQuery = acym_query($queryInsert);
            } catch (Exception $e) {
                $this->errors[] = acym_getDBError();

                return false;
            }

            if ($resultQuery === null) {
                $this->errors[] = acym_getDBError();

                return false;
            } else {
                $result += $resultQuery;
            }
            $numberValue += 500;
        } while (!empty($ids));

        return $result;
    }




    private function cleanMailTable()
    {
        $hasError = false;

        $queryClean = [
            "UPDATE #__acym_list SET `unsubscribe_id` = NULL",
            "UPDATE #__acym_list SET `welcome_id` = NULL",
            "DELETE FROM #__acym_tag WHERE `type` = 'mail'",
            "DELETE FROM #__acym_campaign WHERE `mail_id` IS NOT NULL",
            "DELETE FROM #__acym_campaign",
            "DELETE FROM #__acym_queue",
            "DELETE FROM #__acym_mail_has_list",
            "DELETE FROM #__acym_user_stat",
            "DELETE FROM #__acym_url_click",
            "DELETE FROM #__acym_mail_stat",
            "DELETE FROM #__acym_mail",
        ];

        foreach ($queryClean as $oneQuery) {
            if (acym_query($oneQuery) === null) {
                $this->errors[] = acym_getDBError();
                $hasError = true;
                break;
            }
        }

        return !$hasError;
    }

    private function cleanListTable()
    {
        $hasError = false;

        $queryClean = [
            "DELETE FROM #__acym_tag WHERE `type` = 'list'",
            "DELETE FROM #__acym_mail_has_list",
            "DELETE FROM #__acym_user_has_list",
            "DELETE FROM #__acym_list",
        ];

        foreach ($queryClean as $oneQuery) {
            if (acym_query($oneQuery) === null) {
                $this->errors[] = acym_getDBError();
                $hasError = true;
                break;
            }
        }

        return !$hasError;
    }

    private function cleanUserTable()
    {
        $hasError = false;

        $queryClean = [
            "DELETE FROM `#__acym_user_has_list`",
            "DELETE FROM `#__acym_queue`",
            "DELETE FROM `#__acym_user`",
        ];

        foreach ($queryClean as $oneQuery) {
            if (acym_query($oneQuery) === null) {
                $this->errors[] = acym_getDBError();
                $hasError = true;
                break;
            }
        }

        return !$hasError;
    }

    private function doElementMigration($elementName, $params = array())
    {
        $functionName = 'migrate'.ucfirst($elementName);

        if (empty($params)) {
            $nbInsert = $this->$functionName();
        } else {
            $nbInsert = $this->$functionName($params);
        }

        if ($nbInsert !== false) {
            $this->result[$elementName] = $nbInsert;

            return true;
        } else {
            $this->result[$elementName] = false;
            $this->result["isOk"] = false;
            $this->result["errorInsert"] = true;
            $this->result["errors"] = $this->errors;

            return false;
        }
    }

    private function doCleanTable($tableName)
    {
        $functionName = "clean".ucfirst($tableName)."Table";

        if (!$this->$functionName()) {
            $this->result["isOk"] = false;
            $this->result["errorClean"] = true;
            $this->result["errors"] = $this->errors;

            return false;
        }

        return true;
    }
}
