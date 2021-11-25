<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Html;

use Exception;
use JFactory;
use JHtml;

/**
 * @package  Ctech class for importing various media files
 *
 * @since    2.0.0
 */
class Html
{
	/**
	 * List of all vue template ids
	 *
	 * @since   2.0.0
	 */
	protected static $vueTemplates = array();

	/**
	 * Function to include Bootstrap media files
	 *
	 * @param   array  $options  Options
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function bootstrap($options = array('js' => true, 'css' => true))
	{
		$doc = JFactory::getDocument();

		if (!isset($options['css']) || $options['css'])
		{
			if ($doc->direction == 'ltr')
			{
				JHtml::_('stylesheet', 'sellacious/ctech-bootstrap.css', null, true);
			}
			elseif ($doc->direction == 'rtl')
			{
				JHtml::_('stylesheet', 'sellacious/ctech-bootstrap-rtl.css', null, true);
			}
			JHtml::_('stylesheet', 'sellacious/ctech-colors.css', null, true);
		}

		if (!isset($options['js']) || $options['js'])
		{
			JHtml::_('script', 'sellacious/popper.min.js', false, true);
			JHtml::_('script', 'sellacious/ctech-bootstrap.js', false, true);
		}
	}

	/**
	 * Function to include select2 media files
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function select2()
	{
		JHtml::_('script', 'com_sellacious/plugin/select2-3.5/select2.js', false, true);
		JHtml::_('stylesheet', 'com_sellacious/plugin/select2-3.5/select2.css', null, true);
	}

	/**
	 * Function to register vue templates files
	 *
	 * @param   string  $templateId  Id of the vue template
	 * @param   string  $path        Path to vue template file
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function vueTemplate($templateId, $path)
	{
		$index = strtolower($templateId);

		if (!array_key_exists($index, self::$vueTemplates))
		{
			self::$vueTemplates[$index] = $path;

			$tag = sprintf('<script type="text/x-template" id="%s">%s</script>', $templateId, file_get_contents($path));

			$doc = JFactory::getDocument();

			/** @var  \JDocumentHtml  $doc */
			$doc->addCustomTag($tag);
		}
	}
}
