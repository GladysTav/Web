<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Html\Bootstrap;

use JLayoutHelper;

/**
 * @package  Modal class for ctech-bootstrap
 *
 * @since    2.0.0
 */

class Modal
{
	/**
	 * Displays a ctech-bootstrap modal
	 *
	 * @param   int     $id  Id of the modal
	 * @param   string  $header   html markup of the modal header
	 * @param   string  $body     html markup of the modal body
	 * @param   string  $footer   html markup of the modal footer
	 * @param   array   $options  Options for the modal (Animate, Scrollable, V-centered, Header, url, width, height, size, backdrop)
	 *
	 * @return  string  HTML markup for a ctech-bootstrap modal
	 *
	 * @since   2.0.0
	 */
	public static function modal($id, $header = '', $body = '', $footer = '', $options = array())
	{
		$params        = array();

		$dialogClasses = '';
		$backdrop      = 'data-backdrop="true"';
		$animation     = !isset($options['animate']) || $options['animate'] ? 'ctech-fade' : '';

		$dialogClasses .= isset($options['scrollable']) && $options['scrollable'] ? ' ctech-modal-dialog-scrollable' : '';
		$dialogClasses .= isset($options['v-centered']) && $options['v-centered'] ? ' ctech-modal-dialog-centered' : '';

		$showHeader    = isset($options['header']) ? $options['header'] : true;
		$url           = isset($options['url']) ? $options['url'] : '';

		if ($url)
		{
			$width  = isset($options['width']) ? $options['width'] : '800';
			$height = isset($options['height']) ? $options['width'] : '600';
		}
		else
		{
			$width  = 0;
			$height = 0;
		}

		if (isset($options['size']))
		{
			switch ($options['size'])
			{
				case 'small':
					$dialogClasses .= ' ctech-modal-sm';
					break;
				case 'large':
					$dialogClasses .= ' ctech-modal-lg';
					break;
				case 'x-large':
					$dialogClasses .= ' ctech-modal-xl';
					break;
				default:
					break;
			}
		}

		if (isset($options['backdrop']))
		{
			switch ($options['backdrop'])
			{
				case false:
					$backdrop = '';
					break;
				case 'static':
					$backdrop = 'data-backdrop="static"';
					break;
				default:
					$backdrop = 'data-backdrop="true"';
			}
		}

		if ($url)
		{
			$params['url']    = $url;
			$params['width']  = $width;
			$params['height'] = $height;
		}
		else
		{
			$params['body'] = $body;
		}

		if ($id)
		{
			$params['id'] = $id;
		}

		if ($header)
		{
			$params['header'] = $header;
		}

		if ($footer)
		{
			$params['footer'] = $footer;
		}

		if ($backdrop)
		{
			$params['backdrop'] = $backdrop;
		}

		if ($animation)
		{
			$params['animation'] = $animation;
		}

		if ($dialogClasses)
		{
			$params['classes'] = $dialogClasses;
		}

		$params['showHeader']  = $showHeader;

		return JLayoutHelper::render('sellacious.ctech-bootstrap.modal', $params);

	}
}
