<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$options = array('client' => 2, 'debug' => 0);

$code = $this->state->get('productmedia.code');
$prev = null;

foreach ($this->items as $media)
{
	$args = new stdClass;

	$args->media = (object) $media;
	$args->field = (object) array('code' => $code);

	if (strtolower($media->files_group) != strtolower($prev))
	{
		echo JLayoutHelper::render('com_sellacious.formfield.productmedia.media-group', $args, '', $options);

		$prev = $media->files_group;
	}

	echo JLayoutHelper::render('com_sellacious.formfield.productmedia.media-row', $args, '', $options);
}

