/**
* @version     1.7.4
* @package     sellacious
*
* @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved
* @license     GNU General Public License version 2 or later; see LICENSE.txt
* @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
*/

// Create object wrapper for CKEDITOR, new features will be added on the go
PlgEditorCKEditor = function (element, params) {
	this.init(element, params);
};

PlgEditorCKEditor.prototype = {
	init: function (element, params) {
		this.params = params || {};
		this.element = element;

		params.toolbarGroups = [
			{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
			{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
			{ name: 'forms', groups: [ 'forms' ] },
			{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
			{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
			{ name: 'links', groups: [ 'links' ] },
			{ name: 'insert', groups: [ 'insert' ] },
			'/',
			{ name: 'styles', groups: [ 'styles' ] },
			{ name: 'colors', groups: [ 'colors' ] },
			{ name: 'tools', groups: [ 'tools' ] },
			{ name: 'others', groups: [ 'others' ] },
			{ name: 'about', groups: [ 'about' ] }
		];

		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.root || '';

		params.allowedContent = true;
		params.imageUploadURL = 'upload.php';
		params.embed_provider = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}';
		params.filebrowserUploadUrl = base + '/media/sellacious/js/plugins/ckeditor4/upload.php?type=file';
		params.filebrowserImageUploadUrl = base + '/media/sellacious/js/plugins/ckeditor4/upload.php?type=image';
		params.filebrowserUploadMethod = 'form'; // Added for file browser

		if (params.inline !== undefined) {
			this.editor = CKEDITOR.inline(this.element.name, this.params);
		} else {
			this.editor = CKEDITOR.replace(this.element.name, this.params);
		}
	}
};

// Declare as a jQuery DOM Plugin
jQuery(function ($) {
	$.fn.extend({
		ckEditor4: function () {
			this.each(function () {
				var params = $(this).data('ckeditor');
				$(this).data('cke4-instance', new PlgEditorCKEditor(this, params));
			});
			return this;
		}
	});
});

// Transform all editor elements, not requiring per instance script
jQuery(document).ready(function ($) {
	$('[data-ckeditor]').ckEditor4();
});
