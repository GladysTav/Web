<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * CKEditor4 Inline Editor Plugin
 *
 * @since   1.7.3
 */
class PlgEditorCKInlineEditor4 extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 *
	 * @since   1.7.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object instance
	 *
	 * @var    JApplicationCms
	 *
	 * @since   1.7.3
	 */
	protected $app;

	/**
	 * List of default hidden buttons for ckeditor4
	 *
	 * @var    array
	 *
	 * @since   1.7.3
	 */
	protected $defaultHiddenBtns = array();

	/**
	 * List of extra plugins for ckeditor4
	 *
	 * @var    array
	 *
	 * @since   1.7.3
	 */
	protected $extraPlugins = array();

	/**
	 * Initialises the Editor.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function onInit()
	{
		static $loaded = false;

		if (!$loaded)
		{
			JHtml::_('script', 'sellacious/plugins/ckeditor4/ckeditor.js', array('version' => S_VERSION_CORE, 'relative' => true));
			JHtml::_('script', 'sellacious/plugins/ckeditor4/plugin.js', array('version' => S_VERSION_CORE, 'relative' => true));

			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration('
				div[contenteditable] {
				    outline: 1px solid blue;
				}
			');

			$this->defaultHiddenBtns = explode(',', 'Save,NewPage,Preview,Print,Templates,Cut,Paste,PasteText,PasteFromWord,Undo,Redo,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CopyFormatting,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Copy,Anchor,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Format,Styles,Maximize,About,ShowBlocks');
			$this->extraPlugins      = array('image2','embed','autoembed','filebrowser','sourcedialog');

			$loaded = true;
		}
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Not used.
	 * @param   object   $author   Not used.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML
	 *
	 * @since   1.7.3
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		// Options for the CKEditor constructor.
		$options = new stdClass;

		$registry = new Registry($options);

		$options->height        = $height;
		$options->customConfig  = '';
		$options->removeButtons = (is_array($buttons) && !empty($buttons)) ? implode(',', $buttons) : implode(',', $this->defaultHiddenBtns);
		$options->extraPlugins  = implode(',', $this->extraPlugins);
		$options->inline        = true;

		$displayData = (object) array(
			'options' => $options,
			'config'  => $this->params,
			'params'  => $params,
			'name'    => $name,
			'id'      => empty($id) ? $name : $id,
			'cols'    => $col,
			'rows'    => $row,
			'content' => $content,
			'buttons' => $buttons,
			'width'   => $width,
			'height'  => $height,
			'asset'   => $asset,
			'author'  => $author,
		);

		$html = '';

		// At this point, displayData can be modified by a plugin before going to the layout renderer.
		$this->app->triggerEvent('onEditorBeforeDisplay', array($this->_name, &$displayData, &$html));

		ob_start();

		try
		{
			include JPluginHelper::getLayoutPath($this->_type, $this->_name, 'default');
		}
		catch (Exception $e)
		{
		}

		$html .= ob_get_clean();

		$this->app->triggerEvent('onEditorAfterDisplay', array($this->_name, &$displayData, &$html));

		return $html;
	}
}
