<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/** @var  array  $displayData */
extract($displayData);

/**
 * These lines below are for disabling scrolling of parent window.
 * $('body').addClass('modal-open');
 * $('body').removeClass('modal-open')
 *
 * Scrolling inside bootstrap modals on small screens (adapt to window viewport and avoid modal off screen).
 *      - max-height    .modal-body     Max-height for the modal body
 *                                      When height of the modal is too high for the window viewport height.
 *      - max-height    .iframe         Max-height for the iframe (Deducting the padding of the modal-body)
 *                                      When URL option is set and height of the iframe is higher than max-height of the modal body.
 *
 * Fix iOS scrolling inside bootstrap modals
 *      - overflow-y    .modal-body     When max-height is set for modal-body
 *
 * Specific hack for Bootstrap 2.3.x
 */
$iframeHtml = JLayoutHelper::render('joomla.modal.iframe', $displayData);
$iframeHtml = trim($iframeHtml);

$script = <<<JS
	jQuery(document).ready(function($) {
	   $('#{$selector}').on('show.bs.modal', function() {
	       $('body').addClass('modal-open');
			// Script for destroying and reloading the iframe
	       let modalBody = $(this).find('.modal-body');
	       modalBody.find('iframe').remove();
	       modalBody.prepend('{$iframeHtml}');
	       console.log(modalBody);
			// Adapt modal body max-height to window viewport if needed, when the modal has been made visible to the user.
	   }).on('shown.bs.modal', function() {
			// Get height of the modal elements.
	       let div = $('div.modal-body:visible');
	       let modalHeight = $('div.modal:visible').outerHeight(true),
	           modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
	           modalBodyHeightOuter = div.outerHeight(true),
	           modalBodyHeight = div.height(),
	           modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
				// Get padding top (jQuery position().top not working on iOS devices and webkit browsers, so use of Javascript instead)
	           padding = document.getElementById('{$selector}').offsetTop,
				// Calculate max-height of the modal, adapted to window viewport height.
	           maxModalHeight = ($(window).height()-(padding*2)),
				// Calculate max-height for modal-body.
	           modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
	           maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
			// Set max-height for iframe if needed, to adapt to viewport height.
	       let iframeHeight = $('.iframe').height();
	       if (iframeHeight > maxModalBodyHeight){
	           $('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'hidden'});
	           $('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
	       }
	   }).on('hide.bs.modal', function () {
	       $('body').removeClass('modal-open');
	       $('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
	       $('.modalTooltip').tooltip('destroy');
	   });
	});
JS;

echo $script;
