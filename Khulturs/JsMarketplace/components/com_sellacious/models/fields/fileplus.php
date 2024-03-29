<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('file');

/**
 * Form Field to provide an input field for files
 *
 * @link   http://www.w3.org/TR/html-markup/input.file.html#input.file
 */
class JFormFieldFilePlus extends JFormFieldFile
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'FilePlus';

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 */
	protected function getInput()
	{
		// todo: Add feature for readonly attribute support
		$helper    = SellaciousHelper::getInstance();
		$record_id = (int) $this->element['record_id'];
		$context   = (string) $this->element['context'];
		$fileType  = (string) $this->element['filetype'];
		$limit     = (int) $this->element['limit'];
		$rename    = (string) $this->element['rename'] == 'false' ? 0 : 1;

		// Context should be like 'table_name.image'
		list($tbl_name, $context) = explode('.', $context, 2);

		// Load value automatically, don't depend on model
		$filter = array(
			'list.select' => 'a.id, a.path, a.state, a.original_name, a.doc_type, a.doc_reference',
			'table_name'  => $tbl_name,
			'context'     => $context,
			'record_id'   => $record_id
		);

		$this->value = $helper->media->loadObjectList($filter);
		$this->class .= ' hidden hidden-lg hidden-md hidden-sm hidden-xs';

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'com_sellacious/field.fileplus.css', null, true);
		JHtml::_('script', 'com_sellacious/field.fileplus.js', false, true);

		$formToken = JSession::getFormToken();
		$jsTarget  = array(
			'table'     => $tbl_name,
			'context'   => $context,
			'record_id' => $record_id,
			'rename'    => $rename,
			'type'      => $fileType,
			'limit'     => $limit,
			'temp'      => '0',
		);

		$jsTarget = json_encode($jsTarget);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
			(function ($) {
				$(document).ready(function () {
					var o = new JFormFieldFilePlus;
					o.setup({
						wrapper : '{$this->id}_wrapper',
						siteRoot: '" . JUri::root(true) . "',
						target: {$jsTarget},
						token: '{$formToken}=1',
					});
				});
			})(jQuery);
		");

		$displayData = (object) get_object_vars($this);

		$options = array('debug' => 0);
		$html    = JLayoutHelper::render('com_sellacious.formfield.fileplus', $displayData, '', $options);

		return $html;
	}
}
