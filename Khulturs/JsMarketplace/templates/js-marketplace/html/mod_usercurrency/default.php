<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('stylesheet', 'mod_usercurrency/style.css', null, true);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css');
JHtml::_('script', 'mod_usercurrency/default.js', false, true);
JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE));

$helper  = SellaciousHelper::getInstance();
$filter  = array('state' => 1, 'list.order' => 'a.code_3', 'list.select' => 'a.code_3');
$options = $helper->currency->loadObjectList($filter);
$current = $helper->currency->current('code_3');
?>
<script>
	jQuery(document).ready(function ($) {
		$(window).load(function () {
			// Skip already converted select2
			$('select').not('.select2-offscreen').select2();
		})

	});
</script>
<div class="mod_usercurrency_module">
	<div class="mod_usercurrency_block" id="mod_usercurrency_block">
		<div class="mod_usercurrency">
			<select id="mod_usercurrency_list">
				<?php echo JHtml::_('select.options', $options, 'code_3', 'code_3', $current); ?>
			</select>
		</div>
	</div>
</div>
