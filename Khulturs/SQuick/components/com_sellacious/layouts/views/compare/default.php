<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewCompare $this */
JHtml::_('script', 'sellacious/popper.js', false, true);
JHtml::_('script', 'sellacious/ctech-bootstrap.js', false, true);
JHtml::_('stylesheet', 'sellacious/ctech-bootstrap.css', false, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.css', null, true);

$items = $this->items;
?>
<div class="ctech-wrapper">
	<?php
	if (count($items) == 0)
	{
		echo $this->loadTemplate('nothing');
	}
	else
	{
		echo $this->loadTemplate('specs');
	}
	?>
</div>
