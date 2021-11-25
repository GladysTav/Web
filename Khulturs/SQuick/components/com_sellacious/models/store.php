<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JLoader::register('SellaciousModelProducts', __DIR__ . '/products.php');

/**
 * Methods supporting a list of products from one seller store
 *
 * @since   1.0.0
 */
class SellaciousModelStore extends SellaciousModelProducts
{
}
