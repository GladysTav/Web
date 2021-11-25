<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;
?>
<div class="row">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious') ?>" method="post" id="install-form">

		<div class="center" style="max-width: 600px; margin: auto">

			<h3 style="margin: 0 0 20px; font-size: 24px;">Just one more step ...</h3>

			<br>

			<div class="alert alert-success center">

				<p>Sellacious requires some additional libraries to work it's magic.
					The required files will be downloaded and installed automatically.</p>

			</div>

			<div class="timer" style="font-size: 16px; font-weight:  bold; padding: 6px">
				<?php echo JText::_('COM_SELLACIOUS_INSTALL_WAIT_N_SECONDS_PRE') ?> <span>10</span>
				<?php echo JText::_('COM_SELLACIOUS_INSTALL_WAIT_N_SECONDS_POST') ?>
			</div>

			<div id="wait-progress" class="progress progress-striped active" style="width: 80%; margin: 10px auto;">
				<div class="bar" style="width: 0;"></div>
			</div>

			<button type="button" id="btn-stop" class="btn btn-danger"><i class="icon-stop"></i> <?php
				echo JText::_('COM_SELLACIOUS_INSTALL_BUTTON_STOP_LABEL') ?></button>

			<button type="button" id="btn-install" class="btn btn-primary btn-large hidden"><?php
				echo JText::_('COM_SELLACIOUS_INSTALL_DOWNLOAD_INSTALL_BUTTON') ?></button>

			<br>
			<br>

			<div class="alert alert-success center">
				<p>If you choose to install Sellacious you hereby agree to the
					<a href="https://www.sellacious.com/terms-of-use">Terms &amp; Conditions</a>.</p>

				<p style="font-size: 12px;">Copyright &copy; 2012 - <?php echo date('Y'); ?>
					<a href="http://sellacious.com" title="Sellacious">www.sellacious.com</a>.</p>
			</div>

		</div>

		<?php if ($this->version): ?>
			<br>
			<p class="center" style="color: #000;"><?php
				echo JText::sprintf('COM_SELLACIOUS_INSTALL_DOWNLOAD_INSTALL_NOTE', $this->version) ?></p>
		<?php endif; ?>

		<input type="hidden" name="task" value="install">
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
<script>
	jQuery(function ($) {
		const submit = () => {
			$('.timer').addClass('hidden');
			clearInterval(i);
			document.getElementById('install-form').submit();
		};
		const tick = () => {
			let $timer = $('.timer');
			let t = $timer.data('t') || 0;
			t += 0.1;
			$('#wait-progress').find('.bar').css({width: 10 * t + '%'});
			$timer.data('t', t).find('span').text(10 - parseInt(t));
			if (t >= 10) submit();
		};
		const stop = () => {
			clearInterval(i);
			$('#wait-progress,#btn-stop,.timer').addClass('hidden');
			$('#btn-install').removeClass('hidden');
		};
		$('#btn-install').click(submit);
		$('#btn-stop').click(stop);
		let i = setInterval(tick, 100);
	});
</script>

