jQuery(function ($) {
	$(window).load(function () {

		//For responsiveness
		if($(window).width() < 768) {
			$(this).find('.sellacious-navigator').addClass('fixed-top');
			if($(this).find('.sellacious-navigator .navigator-wrap').length === 0) {
				$('.sellacious-navigator').css('padding', '7px 0');
			}
		}
		$(window).on('resize', function () {
			if($(this).width() < 768) {
				$(this).find('.sellacious-navigator').addClass('fixed-top');
			} else {
				$(this).find('.sellacious-navigator').removeClass('fixed-top');
			}
		});

		//For fixed top
		var target = $('.sellacious-navigator.fixed-top .scroll-to-element:first-of-type a').attr('scroll-target');
		if($(target).offset() !== undefined) {
			var topDistance = $(target).offset().top;
			$(window).on('scroll', function () {
				var scrollTop = $(this).scrollTop();
				if (topDistance < (scrollTop + 75)) {
					$('.sellacious-navigator.fixed-top').slideDown('fast');
				} else {
					$('.sellacious-navigator.fixed-top').slideUp('fast');
				}
			});
		}

		//To animate scrolling to scroll-target
		$(".scroll-to-element").click(function() {
			var target = $(this).find("a").attr("scroll-target");
			if($(target).length > 0) $('html,body').animate({scrollTop: $(target).offset().top - 60}, 'slow');
		});

		//For making nav items active when page scrolls to their scroll-target
		let targets = $('.sellacious-navigator').find('.scroll-to-element a');
		let heights = [];
		$.each(targets, function (i, target) {
			if (i === 0) {
				let first = $(targets).first().attr('scroll-target');
				if ($(first).length > 0) {
					let height = $(first).outerHeight() + $(first).offset().top;
					heights.push(height);
				}
			} else {
				let elem = $(target).attr('scroll-target');
				if ($(elem).length > 0) {
					let height = $(elem).outerHeight() + $(elem).offset().top;
					heights.push(height);
				}
			}
		});

		$.each(heights, function (i, height) {
			$(window).on('scroll', function () {
				let scrollTop = $(this).scrollTop();
				let elem = $(targets).eq(i).attr('scroll-target');
				if ($(elem).length > 0) {
					let topOffset = $(elem).offset().top;
					if (scrollTop < height && scrollTop >= topOffset) {
						$('.scroll-to-element').eq(i).addClass('active');
					} else {
						$('.scroll-to-element').eq(i).removeClass('active');
					}
				}
			});
		});

		// function to animate active line
		function animateLine(left, top, width) {
			let offset = 25/*padding of scroll-to-element*/;
			if($(window).width() < 992 && $(window).width() > 650) {
				offset = 15;
			} else if ($(window).width() < 649) {
				offset = 5;
			}
			$('.active-line').animate({
				left: left + offset,
				top : top,
				width: width
			}, 100);
		}

		//For positioning active-line on page scroll according to the scroll-target on 'centered' layout
		if ($('.active-line').length > 0) {
			$(window).on('scroll', function () {
				let $scroll = $('.scroll-to-element.active');
				if ($scroll.length > 0) {
					let scrollTop = $(window).scrollTop();
					let position = $scroll.offset();
					let top = (position.top - scrollTop) + ($scroll.outerHeight() - 2/*height of active-line*/);
					let width = $scroll.find('span').innerWidth();

					animateLine(position.left, top, width);
				}
			});

			let hoverable = $('.sellacious-navigator').find('.scroll-to-element');
			$.each(hoverable, function (i, v) {
				$(this)
					.on('mouseenter', function () {
						let scrollTop = $(window).scrollTop();
						let position = $(this).offset();
						let top = (position.top - scrollTop) + ($(this).outerHeight() - 2/*height of active-line*/);
						let width = $(this).find('span').innerWidth();

						animateLine(position.left, top, width);
					})
					.on('mouseleave', function () {
						let $scroll = $('.scroll-to-element.active');
						if ($scroll.length > 0) {
							let scrollTop = $(window).scrollTop();
							let position = $scroll.offset();
							let top = (position.top - scrollTop) + ($(this).outerHeight() - 2/*height of active-line*/);
							let width = $scroll.find('span').innerWidth();

							animateLine(position.left, top, width);
						}
					})
			})
		}
	});

});
