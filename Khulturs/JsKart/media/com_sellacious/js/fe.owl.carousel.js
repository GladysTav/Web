jQuery(document).ready($ => {
	$('[data-owl-carousel]').each(function () {
		const $el = $(this);
		const opts = $el.data('owl-carousel') || {};
		const responsive = (o => {
			let n = {};
			$.map(o, (v, k) => n[k] = {items: v});
			return n;
		})(opts.responsive);
		$el.owlCarousel({
			nav: true,
			navText: [
				'<i class="fa fa-angle-left"></i>',
				'<i class="fa fa-angle-right"></i>'
			],
			dots: false,
			rewind: true,
			autoplay: opts.autoplay,
			autoplayTimeout: opts.speed,
			autoplayHoverPause: opts.pause,
			margin: opts.margin,
			responsive: responsive,
			rtl: opts.rtl
		})
	});
});
