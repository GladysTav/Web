<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
    <input type="hidden" name="filename" id="filename" value="<?php echo acym_getVar('cmd', 'filename'); ?>"/>
    <input type="hidden" name="import_columns" id="import_columns" value=""/>
    <input type="hidden" name="new_list" id="acym__import__new-list" value=""/>
    <div id="acym__users__import__generic" class="acym__content">
        <div class="grid-x grid-padding-y">
            <div class="cell medium-shrink">
                <h4 class="cell"><?php echo acym_translation("ACYM_FIELD_MATCHING") ?></h4>
                <p class="acym__users__import__generic__instructions"><?php echo acym_translation('ACYM_ASSIGN_COLUMNS'); ?></p>
            </div>
            <div class="cell medium-auto hide-for-small-only"></div>
            <div class="cell medium-shrink">
                <?php echo acym_modal_pagination_lists_import(
                    acym_translation('ACYM_IMPORT_USERS'),
                    '',
                    acym_translation('ACYM_IMPORT_IN_THESE_LISTS'),
                    'acym__user__import__add-subscription__modal',
                    'data-toggle="add_subscription"'
                ) ?>
                <!--<button type="button" class="button expanded" id="acym__users__import__generic__import__button">--><?php //echo acym_translation('ACYM_IMPORT_USERS') ?><!--</button>-->
            </div>
        </div>
        <div class="grid-x grid-padding-y">
            <div class="cell medium-shrink">
                <input type="checkbox" id="acym__users__import__from_file__ignore__checkbox" name="acym__users__import__from_file__ignore__checkbox">
                <label for="acym__users__import__from_file__ignore__checkbox"><?php echo acym_translation('ACYM_IGNORE_UNASSIGNED') ?></label>
            </div>
        </div>

        <div class="grid-x" id="acym__users__import__generic__matchdata">
            <?php include_once(ACYM_BACK.'views'.DS.'users'.DS.'tmpl'.DS.'ajaxencoding.php'); ?>
        </div>

        <div class="grid-x">
            <h4 class="cell"><?php echo acym_translation("ACYM_PARAMETERS") ?></h4>
            <div class="cell grid-x">
                <div class="cell large-6 grid-x">
                    <label for="acyencoding" class="cell medium-6">File charset</label>
                    <div class="cell medium-6">
                        <?php
                        $encodingHelper = acym_get('helper.encoding');
                        $default = $encodingHelper->detectEncoding($this->content);
                        $urlEncodedFilename = urlencode($filename);
                        $attribs = 'data-filename="'.$urlEncodedFilename.'"';
                        $encodingHelper->charsetField("acyencoding", $default, $attribs);
                        ?>
                    </div>
                </div>
            </div>
            <div class="cell grid-x">
                <?php if ($config->get('require_confirmation')) { ?>
                    <div class="cell large-6 grid-x">
                        <?php echo acym_switch('import_confirmed_generic', 1, acym_translation("ACYM_IMPORT_USERS_AS_CONFIRMED")); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php acym_formOptions(true, "finalizeImport") ?>
</form>

