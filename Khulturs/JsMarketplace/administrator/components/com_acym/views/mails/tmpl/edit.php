<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__editor__content" class="grid-x acym__content">
	<div class="cell grid-x align-right">
		<input type="hidden" id="acym__mail__edit__editor" value="<?php echo $data['mail']->editor ?>">
        <?php
        if ($data['mail']->type == 'notification') {
            ?>
			<button type="submit" data-task="test" class="cell medium-shrink button-secondary auto button acy_button_submit acym__template__save acym__template__prevent__unload">
                <?php echo acym_translation('ACYM_SEND_TEST'); ?>
			</button>
            <?php
        } else {
            echo acym_modal_include('<button type="button" id="acym__template__start-from" class="cell medium-shrink button-secondary auto button">'.acym_translation('ACYM_START_FROM').'</button>', dirname(__FILE__).DS.'choose_template_ajax.php', 'acym__template__choose__modal', $data); ?>
        <?php } ?>
		<button id="apply" type="button" data-task="apply" class="cell medium-shrink button-secondary auto button acym__template__save acym__template__prevent__unload">
            <?php echo acym_translation('ACYM_SAVE'); ?>
		</button>
		<button style="display: none;" data-task="apply" class="acy_button_submit" id="data_apply"></button>
		<button id="save" type="button" data-task="save" class="cell medium-shrink auto button margin-left-1 acym__template__prevent__unload">
            <?php echo acym_translation('ACYM_SAVE_EXIT'); ?>
		</button>
		<button style="display: none;" data-task="save" class="acy_button_submit" id="data_save"></button>
	</div>
	<div class="cell grid-x grid-padding-x acym__editor__content__options">
        <?php
        if ($data['mail']->type == 'notification') {
            echo '<input type="hidden" name="return" value="'.$data['return'].'"/>';
            echo '<input type="hidden" name="notification" value="'.$data['mail']->name.'"/>';
            $sizes = '';
        } else {
            $sizes = 'xlarge-3 medium-6';
            ?>
			<div class="cell xlarge-3 medium-6">
				<label>
                    <?php echo acym_translation('ACYM_TEMPLATE_NAME') ?>
					<input name="mail[name]" type="text" class="acy_required_field" value="<?php echo htmlspecialchars($data['mail']->name) ?>" required>
				</label>
			</div>
            <?php
        }
        ?>
		<div class="cell <?php echo $sizes; ?>">
			<label>
                <?php echo acym_translation('ACYM_SUBJECT') ?>
				<input name="mail[subject]" type="text" value="<?php echo htmlspecialchars($data['mail']->subject) ?>" <?php echo in_array($data['mail']->type, array('welcome', 'unsubscribe')) ? 'required' : ''; ?>>
			</label>
		</div>
        <?php
        if ($data['mail']->type != 'notification') {
            ?>
			<div class="cell xlarge-3 medium-6">
                <?php if ($data['mail']->type == 'welcome' || $data['mail']->type == 'unsubscribe') { ?>
					<label><?php echo acym_translation('ACYM_TYPE'); ?>
						<input name="mail[type]" type="text" value="<?php echo htmlspecialchars($data['mail']->type) ?>" readonly>
					</label>
                <?php } else { ?>
					<label>
                        <?php echo acym_translation('ACYM_TYPE');
                        $templateTypes = array(
                            'standard' => acym_translation('ACYM_STANDARD'),
                            'welcome' => acym_translation('ACYM_WELCOME_MAIL'),
                            'unsubscribe' => acym_translation('ACYM_UNSUBSCRIBE'),
                        );
                        echo acym_select($templateTypes, "mail[type]", htmlspecialchars($data['mail']->type), 'required="required"', null, null, 'acym__template__type') ?>
					</label>
                <?php } ?>
			</div>
			<div class="cell xlarge-3 medium-6">
				<label>
                    <?php echo acym_translation('ACYM_TAGS') ?>
                    <?php echo acym_selectMultiple($data['allTags'], "template_tags", $data['mail']->tags, ['id' => 'acym__tags__field', 'placeholder' => acym_translation('ACYM_ADD_TAGS')], "name", "name") ?>
				</label>
			</div>
            <?php
        }
        ?>
	</div>
</div>
<input type="hidden" name="mail[id]" value="<?php echo htmlspecialchars($data['mail']->id); ?>"/>
<input type="hidden" name="id" value="<?php echo htmlspecialchars($data['mail']->id); ?>"/>
<input type="hidden" name="thumbnail" value="<?php echo empty($data['mail']->thumbnail) ? '' : htmlspecialchars($data['mail']->thumbnail); ?>"/>
<?php echo acym_formOptions();
$editor = acym_get('helper.editor');
$editor->content = $data['mail']->body;
if (empty($data['mail']->id)) {
    $editor->editor = $data['mail']->editor;
}
echo $editor->display();
