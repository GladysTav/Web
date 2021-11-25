<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

?>

<a href="<?php echo $displayData->url; ?>" class="btn btn-<?php echo $displayData->plugin; ?>" type="button">
	<?php echo JText::sprintf('COM_CCL_LOGIN_BUTTON_LOGIN_WITH', ucfirst($displayData->plugin)); ?>
</a>
