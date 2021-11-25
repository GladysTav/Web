(function ($) {
	$(document).ready(function () {
		//Tooltips dispose
		// $('*').click(function () {
		//     $('*').tooltip('dispose');
		// });

		//Scrolling
		var scroll_pos = 0;
		$(document).scroll(function () {
			scroll_pos = $(this).scrollTop();
			$("#header").addClass("sticky");
			if ((scroll_pos < 10)) {
				$("#header").removeClass("sticky");
			}
			if ((scroll_pos > 400) && ($(window).width() > 1200)) {
				$("body").addClass("onscroll");
			} else if ((scroll_pos > 300) && ($(window).width() < 1200)) {
				$("body").addClass("onscroll-tab");
			}
			else {
				$("body").removeClass('onscroll');
				$("body").removeClass("onscroll-tab");
				// $("#header").removeClass("sticky");

			}

		});

		//   odd empty div
		$(window).on('load', function () {
			($("#address-items").find(".address-item").length);
			if ($("#address-items").find(".address-item").length % 2 != 0) {
				$("#address-items").append("<li class='address-item'>" + "<a href=\"#address-form-0\" role=\"button\" data-toggle=\"modal\" class=\"append-addbutton btn-add-address\"><i class=\"fa fa-plus\"></i></a>" +
					"</li>");
			}
		})

		$(".accordion-toggle").on('click', function (e) {
			e.preventDefault();
			$('.accordion-group .accordion-heading').removeClass('active-heading');
			$(this).parent().parent('.accordion-heading').toggleClass("active-heading");
			$('.accordion-body').removeClass('show');
			// $(this).parent().parent('.accordion-heading').siblings('.accordion-body').toggleClass('show');
		});

		//    View status

		$("#view-staus-btn").on('click', function (e) {
			// alert("HEl");
			e.preventDefault();
			if ($("#status-table").attr('hidden') !== undefined) {
				$("#status-table").removeAttr('hidden');
				// alert('true');
			} else {

				$("#status-table").attr('hidden', 'hidden');
			}
		});

		//Show multiple  images on click on products list page
		$('.product-images-list').on('mouseover', function () {
			let thisImage = '';
			let dataImage = $(this).data('image');
			if(typeof dataImage !== undefined && dataImage !== false)
			{
				thisImage = dataImage;
			}
			else
			{
				thisImage = $(this).css('background-image');
			}
			$(this).closest('.image-box').find('.product-img').css({'background-image': 'url(' + thisImage + ')'});
		});

		//check varient box -height
		$('.variant-picker').each(function (i, picker) {
			if ($(picker).find('.main-product-variant').length > 2) {
				$(this).next('.view-more-box').removeClass('hidden');
			} else {
				$(this).next('.view-more-box').addClass('hidden');
			}
		});


		// question ns ans box read more nad less
		$('.view-more-box').toggle(function () {
			$(this).prev('.table-questions, .variant-picker').animate({'height': '100px'}, 1000);
			$(this).css('opacity', '1');
			$(this).find('.view-more-bg-icon').removeClass('fa-angle-double-up');
			$(this).find('.view-more-bg-icon').addClass('fa-angle-double-down');
		}, function () {
			$(this).prev('.table-questions, .variant-picker').animate({'height': '100%'}, 1000);
			$(this).css('opacity', '0.4');
			$(this).find('.view-more-bg-icon').removeClass('fa-angle-double-down');
			$(this).find('.view-more-bg-icon').addClass('fa-angle-double-up');
		});
		//    products recently viewed container on top for large screens
		if ($('.recent-top-module').length) {
			$('.variants-box').addClass('col-md-9');

		} else {
			$('.variants-box').addClass('col-md-12');
		}

		//  Offcanvas
		$(".category-offcanvas-btn").on('click', function () {
			$('.astroid-offcanvas').css('visibility', 'visible');
			$('.astroid-content').addClass('astroid-content dropshadow');
		});
		$('.close-offcanvas').on('click', function () {
			$('.astroid-offcanvas').css('visibility', 'hidden');
		})

		//	if variant box div is prenet recently viewed products on top is then allowed to show
		if ($('.recent-top-module').siblings().hasClass('variants-box')) {
			$('.recent-top-module').removeClass('hidden');
		} else {
			$('.recent-top-module').addClass('hidden');
		}


	});


})(jQuery);


