<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array   $displayData */
/** @var  string  $search */
extract($displayData);

?>

<div class="filter-snap-in">
	<div class="filter-title"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SEARCH'); ?></div>
	<div class="filter-search-field">
		<input type="text" name="filter[search]" placeholder="<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SEARCH_PLACEHOLDER'); ?>" value="<?php echo $search; ?>"/>
		<?php if ($this->autoSubmit): ?>
			<button onclick="this.form.submit()" class="ctech-btn ctech-btn-dark ctech-btn-sm ctech-btn-block">Search</button>
		<?php endif; ?>
	</div>
</div>
