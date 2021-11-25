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

class acymmailClass extends acymClass
{
    var $table = 'mail';
    var $pkey = 'id';

    public function getMatchingMails($settings)
    {
        $query = 'SELECT mail.* FROM #__acym_mail AS mail';
        $queryCount = 'SELECT COUNT(mail.id) FROM #__acym_mail AS mail';
        $queryStatus = 'SELECT COUNT(mail.type) AS number, mail.type FROM #__acym_mail AS mail';

        $filters = array();

        if (!empty($settings['tag'])) {
            $tagJoin = ' JOIN #__acym_tag AS tag ON mail.id = tag.id_element';
            $query .= $tagJoin;
            $queryCount .= $tagJoin;
            $queryStatus .= $tagJoin;
            $filters[] = 'tag.name = '.acym_escapeDB($settings['tag']);
            $filters[] = 'tag.type = "mail"';
        }

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($settings['type'])) {
            if ($settings['type'] == 'custom') {
                $filters[] .= 'mail.library = 0';
            } else {
                $filters[] .= 'mail.library = 1';
            }
        }

        if (!empty($settings['editor'])) {
            if ($settings['editor'] == 'html') {
                $filters[] .= 'mail.drag_editor = 0';
            } else {
                $filters[] .= 'mail.drag_editor = 1';
            }
        }

        if (empty($settings['onlyStandard'])) {
            $filters[] = 'mail.type != \'notification\'';
        } else {
            $filters[] = 'mail.type = \'standard\'';
        }

        $filters[] = 'mail.template = 1';

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = array(
                'standard',
                'welcome',
                'unsubscribe',
            );

            if (!in_array($settings['status'], $allowedStatus)) {
                die('Injection denied');
            }
            $filters[] = 'mail.type = "'.$settings['status'].'"';
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY mail.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }

        $results['mails'] = acym_loadObjectList($query, '', $settings['offset'], $settings['mailsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        $mailsPerStatus = acym_loadObjectList($queryStatus.' GROUP BY type', 'type');
        $nbAllMail = 0;
        foreach ($mailsPerStatus as $oneMailType) {
            $nbAllMail += $oneMailType->number;
        }

        $results['status'] = array(
            'all' => $nbAllMail,
            'standard' => !empty($mailsPerStatus['standard']->number) ? $mailsPerStatus['standard']->number : 0,
            'welcome' => !empty($mailsPerStatus['welcome']->number) ? $mailsPerStatus['welcome']->number : 0,
            'unsubscribe' => !empty($mailsPerStatus['unsubscribe']->number) ? $mailsPerStatus['unsubscribe']->number : 0,
        );

        return $results;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM #__acym_mail';

        return acym_loadObjectList($query);
    }

    public function getOneById($id)
    {
        $mail = acym_loadObject('SELECT * FROM #__acym_mail WHERE id = '.intval($id).' LIMIT 1');

        if (!empty($mail)) {
            $tagsClass = acym_get('class.tag');
            $mail->tags = $tagsClass->getAllTagsByElementId('mail', $id);
        }

        return $mail;
    }

    public function getOneByName($name)
    {
        $mail = acym_loadObject('SELECT * FROM #__acym_mail WHERE name = '.acym_escapeDB($name));

        if (!empty($mail)) {
            $tagsClass = acym_get('class.tag');
            $mail->tags = $tagsClass->getAllTagsByElementId('mail', $mail->id);
        }

        return $mail;
    }

    public function getMailsByType($typeMail, $settings)
    {
        if (empty($settings['key'])) {
            $settings['key'] = '';
        }
        if (empty($settings['offset'])) {
            $settings['offset'] = 0;
        }
        if (empty($settings['mailsPerPage'])) {
            $settings['mailsPerPage'] = 12;
        }

        $query = 'SELECT * FROM #__acym_mail AS mail';
        $queryCount = 'SELECT count(*) FROM #__acym_mail AS mail';

        $filters = array();
        $filters[] = 'mail.type = '.acym_escapeDB($typeMail);

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        $query .= ' WHERE ('.implode(') AND (', $filters).')';
        $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

        $query .= ' ORDER BY id DESC';

        $results['mails'] = acym_loadObjectList($query, $settings['key'], $settings['offset'], $settings['mailsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getAllListsWithCountSubscribersByMailIds($ids)
    {
        acym_arrayToInteger($ids);
        if (empty($ids)) {
            return array();
        }

        $query = 'SELECT mailLists.list_id, mailLists.mail_id, list.*, COUNT(userLists.user_id) AS subscribers 
                    FROM #__acym_mail_has_list AS mailLists 
                    JOIN #__acym_list AS list ON mailLists.list_id = list.id
                    LEFT JOIN #__acym_user_has_list AS userLists 
                        JOIN #__acym_user AS acyuser ON userLists.user_id = acyuser.id
                        AND userLists.status = 1
                        AND acyuser.active = 1 ';

        $config = acym_config();
        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND acyuser.confirmed = 1 ';
        }

        $query .= 'ON list.id = userLists.list_id    
                    WHERE mailLists.mail_id IN ('.implode(",", $ids).')
                    GROUP BY mailLists.list_id, mailLists.mail_id';

        return acym_loadObjectList($query);
    }

    public function getAllListsByMailId($id)
    {
        $query = 'SELECT list.*
                    FROM #__acym_mail_has_list AS mailLists
                    JOIN #__acym_list AS list ON mailLists.list_id = list.id
                    WHERE mailLists.mail_id IN ('.intval($id).')
                    GROUP BY mailLists.list_id, mailLists.mail_id';

        return acym_loadObjectList($query);
    }

    public function save($mail)
    {
        if (isset($mail->tags)) {
            $tags = $mail->tags;
            unset($mail->tags);
        }

        if (empty($mail->id) && empty($mail->creator_id)) {
            $mail->creator_id = acym_currentUserId();
        }

        foreach ($mail as $oneAttribute => $value) {
            if (empty($value) || in_array($oneAttribute, array('thumbnail', 'settings'))) {
                continue;
            }

            if ($oneAttribute == 'body') {
                $mail->$oneAttribute = preg_replace('#<input[^>]*value="[^"]*"[^>]*>#Uis', '', $mail->$oneAttribute);

                $mail->$oneAttribute = str_replace(' contenteditable="true"', '', $mail->$oneAttribute);
            } else {
                $mail->$oneAttribute = strip_tags($mail->$oneAttribute);
            }
        }

        $mailID = parent::save($mail);

        if (!empty($mailID) && isset($tags)) {
            $tagClass = acym_get('class.tag');
            $tagClass->setTags('mail', $mailID, $tags);
        }

        return $mailID;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);

        acym_query('UPDATE #__acym_list SET welcome_id = null WHERE welcome_id IN ('.implode(',', $elements).')');
        acym_query('UPDATE #__acym_list SET unsubscribe_id = null WHERE unsubscribe_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_queue WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_mail_has_list WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query("DELETE FROM #__acym_tag WHERE type = 'mail' AND id_element IN (".implode(',', $elements).")");
        acym_query('DELETE FROM #__acym_user_stat WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_url_click WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_mail_stat WHERE mail_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function deleteOneAttachment($mailid, $idAttachment)
    {
        $mailid = intval($mailid);
        if (empty($mailid)) {
            return false;
        }
        $mail = $this->getOneById($mailid);

        $attachments = $mail->attachments;
        if (empty($attachments)) {
            return false;
        }
        $decodedAttach = json_decode($attachments, true);
        unset($decodedAttach[$idAttachment]);
        $attachdb = json_encode($decodedAttach);

        return acym_query('UPDATE #__acym_mail SET attachments = '.acym_escapeDB($attachdb).' WHERE id = '.$mailid.' LIMIT 1');
    }
}
