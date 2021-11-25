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

if ($this->helper->config->get('favorite_store', 1)):
	$me = JFactory::getUser();
	$id = $this->state->get('store.id');
	?>
	<div class="user-favorite-container ctech-float-left ctech-text-danger ctech-border-danger ctech-rounded-circle">
		<?php
		if ($me->guest):
		$url   = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $id, false);
		$login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false); ?>
		<a class="btn-favorite btn-favorite-notLoggedIn hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_STORE_FAVORITE') ?>" data-guest="true" data-href="<?php echo $this->escape($login) ?>">
				<i class="fa fa-heart regular-icon ctech-text-dark"></i>
			</a><?php
		elseif ($this->helper->userfavorite->check($id)):
			$url = JRoute::_('index.php?option=com_sellacious&view=favoritestores', false); ?>
		<a class="btn-favorite btn-favorite-loggedIn hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_STORE_FAVORITE')?>" data-href="<?php echo $this->escape($url) ?>">
				<i class="fa fa-heart solid-icon ctech-text-danger"></i>
			</a><?php
		else: ?>
		<a class="btn-favorite hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_STORE_FAVORITE')?>" data-seller-id="<?php echo $id ?>">
				<i class="fa fa-heart regular-icon"></i>
			</a><?php
		endif;
		?>
	</div>
<?php endif; ?>
