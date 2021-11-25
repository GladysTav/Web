<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('stylesheet', 'mod_backlink/style.css', null, true);

?>
<button  class="backlink" onclick="goBack()"><i class="fa fa-long-arrow-left"></i> </button>



<script>
	function goBack() {
		window.history.back();
	}
</script>
