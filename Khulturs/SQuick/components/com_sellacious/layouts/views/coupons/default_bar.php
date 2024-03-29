<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousViewCoupons $this */
$filters = $this->filterForm->getGroup('filter');

if ($filters): ?>
	<div class="coupon-filters ctech-float-right"><?php
		foreach ($filters as $fieldName => $field):
			echo $field->input;
		endforeach; ?>
	</div><?php
endif;
