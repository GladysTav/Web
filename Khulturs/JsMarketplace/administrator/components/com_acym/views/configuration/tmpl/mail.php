<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_DEFAULT_SENDER'); ?></div>
    <div class="grid-x grid-margin-x">
        <div class="cell medium-4">
            <label class="cell grid-x">
                <span class="cell"><?php echo acym_translation('ACYM_FROM_NAME'); ?></span>
                <input type="text" name="config[from_name]" placeholder="<?php echo acym_translation('ACYM_FROM_NAME_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('from_name'); ?>"/>
            </label>
        </div>
        <div class="cell medium-4">
            <label class="cell grid-x">
                <span class="cell"><?php echo acym_translation('ACYM_FROM_EMAIL'); ?></span>
                <input type="email" name="config[from_email]" placeholder="<?php echo acym_translation('ACYM_FROM_EMAIL_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('from_email'); ?>"/>
            </label>
        </div>

        <div class="cell margin-bottom-1">
            <input type="hidden" id="from_as_replyto_value" name="config[from_as_replyto]" value="<?php echo $data['config']->get('from_as_replyto', 1); ?>"/>
            <input id="from_as_replyto" data-toggle="acy_toggle_replyto" data-value="from_as_replyto_value" class="acym_toggle" type="checkbox" <?php
            if ($data['config']->get('from_as_replyto', 1) == 1) {
                echo 'checked="checked"';
            };
            ?>/>
            <label for="from_as_replyto">
                <?php echo acym_translation('ACYM_FROM_AS_REPLYTO'); ?>
            </label>
        </div>

        <div class="cell medium-4 acy_toggle_replyto">
            <label class="cell grid-x">
                <span class="cell"><?php echo acym_translation('ACYM_REPLYTO_NAME'); ?></span>
                <input type="text" name="config[replyto_name]" placeholder="<?php echo acym_translation('ACYM_REPLYTO_NAME_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('replyto_name'); ?>"/>
            </label>
        </div>
        <div class="cell medium-4 acy_toggle_replyto">
            <label class="cell grid-x">
                <span class="cell"><?php echo acym_translation('ACYM_REPLYTO_EMAIL'); ?></span>
                <input type="email" name="config[replyto_email]" placeholder="<?php echo acym_translation('ACYM_REPLYTO_EMAIL_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('replyto_email'); ?>"/>
            </label>
        </div>
        <div class="medium-4 acy_toggle_replyto"></div>
        <div class="cell medium-4">
            <label class="cell grid-x">
                <span class="cell"><?php echo acym_translation('ACYM_BOUNCE_EMAIL'); ?></span>
                <input type="email" name="config[bounce_email]" placeholder="<?php echo acym_translation('ACYM_BOUNCE_EMAIL_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('bounce_email'); ?>"/>
            </label>
        </div>
        <div class="medium-8"></div>
        <div class="cell large-4 medium-6 grid-x">
            <?php echo acym_switch('config[add_names]', $data['config']->get('add_names'), acym_translation('ACYM_ADD_NAMES')); ?>
        </div>
    </div>
</div>

<div class="acym__configuration__mail-settings acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_CONFIGURATION_MAIL'); ?></div>
    <div class="grid-x grid-margin-x">
        <fieldset class="cell">
            <legend><?php echo acym_translation('ACYM_CONFIGURATION_MAIL_DESCRIPTION'); ?></legend>
            <?php
            $sendingMethods = array(
                'server' => acym_translation('ACYM_USING_YOUR_SERVER'),
                'external' => acym_translation('ACYM_USING_AN_EXTERNAL_SERVER'),
            );
            echo acym_radio($sendingMethods, 'config[sending_platform]', $data['config']->get('sending_platform', 'server'));
            ?>
        </fieldset>
        <fieldset class="cell">
            <?php
            $sendingMethods = array(
                'phpmail' => acym_translation('ACYM_PHP_MAIL_FUNCTION'),
                'sendmail' => 'SendMail',
                'qmail' => 'QMail',
                'smtp' => acym_translation('ACYM_SMTP'),
                'elasticemail' => 'Elastic Email',
            );
            echo acym_radio($sendingMethods, 'config[mailer_method]', $data['config']->get('mailer_method', 'phpmail'));
            ?>
        </fieldset>
        <div class="cell" id="sending_method_options">
            <div id="sendmail_settings" class="send_settings grid-x">
                <label for="sendmail_path" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_SENDMAIL_PATH'); ?>
                </label>
                <input id="sendmail_path" class="cell medium-auto" type="text" name="config[sendmail_path]" value="<?php echo $data['config']->get('sendmail_path', '/usr/sbin/sendmail'); ?>">
            </div>
            <div id="smtp_settings" class="send_settings grid-x">
                <label for="smtp_host" class="cell grid-x">
                    <span class="large-2 medium-3"><?php echo acym_translation('ACYM_SMTP_SERVER'); ?></span>
                    <input id="smtp_host" class="cell medium-4" type="text" name="config[smtp_host]" value="<?php echo $data['config']->get('smtp_host'); ?>">
                </label>


                <label for="smtp_port" class="cell grid-x">
                    <span class="large-2 medium-3"><?php echo acym_translation('ACYM_SMTP_PORT'); ?></span>
                    <input id="smtp_port" class="cell medium-4" type="text" name="config[smtp_port]" value="<?php echo $data['config']->get('smtp_port'); ?>">
                </label>

                <label for="smtp_secured" class="cell grid-x">
                    <span class="large-2 medium-3"><?php echo acym_translation('ACYM_SMTP_SECURE'); ?></span>
                    <div class="cell medium-2">
                        <?php
                        $secureMethods = array(
                            '' => '- - -',
                            'ssl' => 'SSL',
                            'tls' => 'TLS',
                        );
                        echo acym_select($secureMethods, 'config[smtp_secured]', $data['config']->get('smtp_secured', ''), null, '', '', 'smtp_secured');
                        ?>
                    </div>
                </label>


                <div class="cell grid-x">
                    <?php echo acym_switch('config[smtp_keepalive]', $data['config']->get('smtp_keepalive'), acym_translation('ACYM_SMTP_ALIVE'), array(), 'large-2 medium-3 small-9'); ?>
                </div>

                <div class="cell grid-x">
                    <?php echo acym_switch('config[smtp_auth]', $data['config']->get('smtp_auth'), acym_translation('ACYM_SMTP_AUTHENTICATION'), array(), 'large-2 medium-3 small-9'); ?>
                </div>

                <label for="smtp_username" class="cell grid-x">
                    <span class="large-2 medium-3"><?php echo acym_translation('ACYM_SMTP_USERNAME'); ?></span>
                    <input id="smtp_username" class="cell medium-4" type="text" name="config[smtp_username]" value="<?php echo $data['config']->get('smtp_username'); ?>">
                </label>

                <label for="smtp_password" class="cell grid-x">
                    <span class="large-2 medium-3"><?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?></span>
                    <input id="smtp_password" class="cell medium-4" type="text" name="config[smtp_password]" value="<?php echo str_repeat('*', strlen($data['config']->get('smtp_password'))); ?>">
                </label>

                <div id="available_ports">
                    <a href="#" id="available_ports_check"><?php echo acym_translation('ACYM_SMTP_AVAILABLE_PORTS'); ?></a>
                </div>
            </div>
            <div id="elastic_settings" class="send_settings grid-x">
                <label for="elasticemail_username" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_SMTP_USERNAME'); ?>
                </label>
                <input id="elasticemail_username" class="cell large-10 medium-9" type="text" name="config[elasticemail_username]" value="<?php echo $data['config']->get('elasticemail_username'); ?>">

                <label for="elasticemail_password" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_API_KEY'); ?>
                </label>
                <input id="elasticemail_password" class="cell large-10 medium-9" type="text" name="config[elasticemail_password]" value="<?php echo str_repeat('*', strlen($data['config']->get('elasticemail_password'))); ?>">

                <label for="elasticemail_password" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_SMTP_PORT'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <?php
                    $sendingMethods = array(
                        '25' => '25',
                        '2525' => '2525',
                        'rest' => acym_translation('ACYM_REST_API'),
                    );
                    echo acym_radio($sendingMethods, 'config[elasticemail_port]', $data['config']->get('elasticemail_port', 'rest'));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="acym__configuration__advanced acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_CONFIGURATION_ADVANCED'); ?></div>
    <div class="grid-x grid-margin-x">
        <div class="cell medium-6 grid-x">
            <div class="cell large-6 medium-5">
                <label for="config_encoding"><?php echo acym_translation('ACYM_CONFIGURATION_ENCODING'); ?></label>
            </div>
            <div class="cell medium-auto">
                <?php
                $encodingHelper = acym_get('helper.encoding');
                $encodingHelper->encodingField('config[encoding_format]', $data['config']->get('encoding_format', '8bit'));
                ?>
            </div>
        </div>
        <div class="cell medium-6 grid-x">
            <div class="cell large-6 medium-5">
                <label for="config_charset"><?php echo acym_translation('ACYM_CONFIGURATION_CHARSET'); ?></label>
            </div>
            <div class="cell medium-auto">
                <?php
                $encodingHelper->charsetField('config[charset]', $data['config']->get('charset'));
                ?>
            </div>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[use_https]', $data['config']->get('use_https'), acym_translation('ACYM_CONFIGURATION_HTTPS')); ?>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[special_chars]', $data['config']->get('special_chars'), acym_translation('ACYM_SPECIAL_CHARS')); ?>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[embed_images]', $data['config']->get('embed_images'), acym_translation('ACYM_CONFIGURATION_EMBED_IMAGES')); ?>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[embed_files]', $data['config']->get('embed_files'), acym_translation('ACYM_CONFIGURATION_EMBED_ATTACHMENTS')); ?>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[multiple_part]', $data['config']->get('multiple_part'), acym_translation('ACYM_CONFIGURATION_MULTIPART')); ?>
        </div>
        <div class="cell medium-6 grid-x">
            <?php echo acym_switch('config[dkim]', $data['config']->get('dkim'), acym_translation('ACYM_CONFIGURATION_DKIM'), array(), 'medium-6 small-9', "auto", "tiny", 'dkim_config'); ?>
        </div>
    </div>
</div>

<div class="acym__configuration__dkim acym__content acym_area padding-vertical-1 padding-horizontal-2" id="dkim_config">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_DKIM_SETTINGS'); ?></div>
    <?php
    $domain = $data['config']->get('dkim_domain', '');
    if (empty($domain)) {
        $domain = preg_replace(array('#^https?://(www\.)*#i', '#^www\.#'), '', ACYM_LIVE);
        $domain = substr($domain, 0, strpos($domain, '/'));
    }

    $dkimSelector = $data['config']->get('dkim_selector', 'acy');
    if ((!empty($dkimSelector) && $dkimSelector != 'acy') || $data['config']->get('dkim_passphrase', '') != '' || acym_getVar('int', 'dkimletme')) { ?>

        <div class="grid-x grid-margin-x">
            <div class="cell large-6 grid-x grid-margin-x">
                <label for="dkim_domain_name" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_DOMAIN'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <input id="dkim_domain_name" type="text" name="config[dkim_domain]" value="<?php echo $this->escape($domain); ?>">
                </div>

                <label for="dkim_selector" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_SELECTOR'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <input id="dkim_selector" type="text" name="config[dkim_selector]" value="<?php echo $this->escape($data['config']->get('dkim_selector', 'acy')); ?>">
                </div>

                <label for="dkim_private" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_PRIVATE'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <textarea id="dkim_private" name="config[dkim_private]"><?php echo $data['config']->get('dkim_private', ''); ?></textarea>
                </div>
            </div>

            <div class="cell large-6 grid-x grid-margin-x">
                <label for="dkim_passphrase" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_PASSPHRASE'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <input id="dkim_passphrase" type="text" name="config[dkim_passphrase]" value="<?php echo $this->escape($data['config']->get('dkim_passphrase', '')); ?>">
                </div>

                <label for="dkim_identity" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_IDENTITY'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <input id="dkim_identity" type="text" name="config[dkim_identity]" value="<?php echo $this->escape($data['config']->get('dkim_identity', '')); ?>">
                </div>

                <label for="dkim_public" class="cell large-2 medium-3">
                    <?php echo acym_translation('ACYM_DKIM_PUBLIC'); ?>
                </label>
                <div class="cell large-10 medium-9">
                    <textarea id="dkim_public" name="config[dkim_public]"><?php echo $data['config']->get('dkim_public', ''); ?></textarea>
                </div>
            </div>
        </div>

    <?php } else {

        if ($data['config']->get('dkim_private', '') == '' || $data['config']->get('dkim_public', '') == '') {
            echo acym_translation('ACYM_DKIM_SAVE');
            acym_addScript(false, ACYM_UPDATEMEURL.'generatedkim');
            ?>
            <input type="hidden" id="dkim_private" name="config[dkim_private]"/>
            <input type="hidden" id="dkim_public" name="config[dkim_public]"/>

            <?php
        } else {
            $publicKey = 'v=DKIM1;s=email;t=s;p='.trim($data['config']->get('dkim_public', ''), '"');

            echo acym_translation_sprintf(
                'ACYM_DKIM_CONFIGURE',
                '<input class="margin-bottom-0" type="text" id="dkim_domain" name="config[dkim_domain]" value="'.$this->escape($domain).'" />'
            ); ?><br/>
            <?php echo acym_translation('ACYM_DKIM_KEY') ?>
            <input id="dkim_key" class="acym_autoselect margin-bottom-0" type="text" readonly="readonly" value="acy._domainkey"/>
            <br/><?php echo acym_translation('ACYM_DKIM_VALUE') ?>
            <input id="dkim_value" class="acym_autoselect margin-bottom-0" type="text" readonly="readonly" value="<?php echo $this->escape($publicKey); ?>"/>
            <br/><input type="checkbox" value="1" id="dkimletme" name="dkimletme"/> <label for="dkimletme"><?php echo acym_translation('ACYM_DKIM_LET_ME'); ?></label>
            <?php
        }
        echo '<br />';
    }
    ?>


    <a class="smaller-button button button-secondary margin-bottom-0 margin-top-1" target="_blank" href="<?php echo ACYM_HELPURL; ?>dkim">
        <?php echo acym_translation('ACYM_HELP'); ?>
    </a>

</div>
