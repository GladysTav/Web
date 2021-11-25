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

$icon = empty($displayData['icon']) ? 'generic' : preg_replace('#\.[^ .]*$#', '', $displayData['icon']);
?>
<h1 class="page-title">
	<span class="fa fa-lg fa-<?php echo $icon; ?>"></span>
	<?php echo $displayData['title']; ?>
</h1>

<?php if ($subTitle = $displayData['sub_title']): ?>
<span class="page-sub-title">
	<?php echo $subTitle; ?>
</span>
<?php endif; ?>
