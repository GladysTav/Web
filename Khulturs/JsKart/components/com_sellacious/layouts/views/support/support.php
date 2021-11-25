<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$this->app->input->set('tmpl', 'component');
?>
<form action="<?php echo 'index.php'; ?>" method="post" target="_blank" id="support-form">
	<div class="center">
		<img src="<?php echo JUri::root() ?>/media/sellacious/images/sellacious-large.png" alt="Sellacious" style="width: 240px;"/>
	</div>
	<fieldset class="text-center">
		<div class="control-group">
			<div class="control-label"><label for="support-passwd">Login to</label></div>
			<div class="controls">
				<div class="btn-group" data-toggle="buttons">
					<label class="btn">
						<input checked class="btn-dest hidden" type="radio" value="<?php echo 'index.php'; ?>">
						<span>Site Frontend</span>
					</label>
					<label class="btn">
						<input class="btn-dest hidden" type="radio" value="<?php echo JPATH_SELLACIOUS_DIR . '/index.php'; ?>">
						<span>Sellacious Backend</span>
					</label>
					<label class="btn">
						<input class="btn-dest hidden" type="radio" value="<?php echo 'administrator/index.php'; ?>">
						<span>Joomla Administrator</span>
					</label>
				</div>
			</div>
		</div>
		<br>
		<div class="control-group">
			<div class="control-label"><label for="support-passwd">User ID (blank to use default user)</label></div>
			<div class="controls"><input type="text" id="support-passwd" name="_u" class="form-control"></div>
		</div>
		<br>
		<div class="control-group">
			<div class="control-label"><label for="support-passwd">Enter Support Password</label></div>
			<div class="controls"><input type="text" id="support-passwd" name="_" class="form-control"></div>
		</div>
		<button type="submit" class="btn btn-primary"><i class="fa fa-sign-in-alt"></i> Login as Support Agent</button>
	</fieldset>

	<input type="hidden" name="_c" value="support">

	<?php echo JHtml::_('form.token'); ?>
</form>
<style type="text/css">
	#support-form {
		border-radius: 10px;
		border: 1px solid #cccccc;
		margin: 10px auto;
		width: 420px;
		padding: 20px;
	}
</style>
<script>
	jQuery($ => {
		$('.btn-dest').click(function () {
			$('#support-form').attr('action', $(this).val());
		});

		$(document).on('click', '.btn-group label:not(.active)', function () {
			let $label = $(this);
			let $input = $('#' + $label.attr('for'));
			if ($input.prop('checked')) return;
			$label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
			$label.addClass('active btn-primary');
			$input.prop('checked', true).trigger('change');
		})
	});
</script>
