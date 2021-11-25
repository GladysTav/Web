/*====
*
*  Common Js file for all layouts
*
*/
($ => {
	$.fn.extend({
		rollover() {
			let interval = [];
			$(this).each(function (i) {
				let $roller = $(this);
				let $img = $roller.find('img[data-rollover]');
				let $bgrollover = $roller.find('.bgrollover');

				if ($img.length) {
					let images = $img.data('rollover');
					$img.removeAttr('data-rollover');
					interval[i] = 0;
					let count = images.length;
					if (count) {
						$roller.hover(() => {
							interval[i] = setInterval(() => {
								let ci = $img.data('rolloverIndex') || 0;
								$img.attr('src', images[ci]);
								$img.data('rolloverIndex', ci + 1 < count ? ci + 1 : 0);
							}, 1500);
						}, () => {
							interval[i] && clearInterval(interval[i]);
						});
					}
				} else if ($bgrollover.length) {
					let bgImages = $bgrollover.data('rollover');
					$bgrollover.removeAttr('data-rollover');
					interval[i] = 0;
					let bgCount = bgImages.length;
					if (bgCount) {
						$roller.hover(() => {
							interval[i] = setInterval(() => {
								let ci = $bgrollover.data('rolloverIndex') || 0;
								$bgrollover.css('background-image', 'url("' + bgImages[ci] + '")');
								$bgrollover.data('rolloverIndex', ci + 1 < bgCount ? ci + 1 : 0);

							}, 2000);
						}, () => {
							interval[i] && clearInterval(interval[i]);
						});
					}
				}
			});
		}
	});

	$(window).load(() => {
		// Set macro for autoload
		$('[data-rollover="container"]').rollover();

		const wrapper = '<div class="ctech-wrapper" id="ctech-modal-wrapper"></div>';
		$('body').append(wrapper);

		$('div.ctech-modal').each(function () {
			$(this).appendTo($('#ctech-modal-wrapper'));
		});

		$('.ctech-modal-header button.close').click(() => {
			$('body').removeClass('modal-open');
		});
	});

	$(document).on('shown.ctech-bs.modal', '#modal-cart', () => {
		const $cart = $('#modal-cart');
		const $cartC = $cart.find('.ctech-modal-content');
		const closeBtn = '<button type="button" class="ctech-close" data-dismiss="ctech-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

		if (!$cart.find('.ctech-close').length) $cartC.prepend(closeBtn);
	})
})(jQuery);
