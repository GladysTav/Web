<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acyarchiveview">
    <h1 class="contentheading"><?php echo $data['mail']->subject; ?></h1>

    <div class="newsletter_body" style="min-width:80%" id="newsletter_preview_area"><?php echo $data['mail']->body; ?></div>

    <?php if (!empty($data['mail']->attachments)) { ?>
        <fieldset class="newsletter_attachments">
            <legend><?php echo acym_translation('ACYM_ATTACHMENTS'); ?></legend>
            <table>
                <?php
                foreach ($data['mail']->attachments as $attachment) {
                    echo '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
                }
                ?>
            </table>
        </fieldset>
    <?php } ?>
</div>
