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

/** @var  \SellaciousViewProfile $this */
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');
JHtml::_('behavior.keepalive');

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);

$fieldsets = $this->get('form')->getFieldsets();
?>
<div class="ctech-wrapper">
	<div class="profile profile-tabs">
		<div class="profile-buttons">
			<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses'); ?>"
			   class="ctech-btn ctech-btn-primary ctech-float-right ctech-btn-sm ctech-rounded-0 ctech-ml-1"><?php echo JText::_('COM_SELLACIOUS_ADDRESSES_MANAGE_LABEL') ?></a>
			<button type="button" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_sellacious&task=profile.edit'); ?>'"
			   class="ctech-btn ctech-btn-primary ctech-float-right ctech-btn-sm ctech-rounded-0 ctech-ml-1"><?php echo JText::_('COM_SELLACIOUS_PROFILE_EDIT_LABEL') ?></button>
			<div class="clearfix"></div>
		</div>
		<?php
		$tabs      = array('active' => 'profile_tabs_basic', 'vertical' => true);

		echo JHtml::_('ctechBootstrap.startTabs', 'profile_tabs', $tabs);

		$sets = array();

		try
		{
			$sets['basic'] = $this->loadTemplate('basic');
		}
		catch (Exception $e)
		{
		}

		try
		{
			$sets['client'] = $this->loadTemplate('client');
		}
		catch (Exception $e)
		{
		}

		if (!empty($this->get('registry')->get('seller.category_id')))
		{
			try
			{
				$sets['seller'] = $this->loadTemplate('seller');
			}
			catch (Exception $e)
			{
			}
		}

		if ($this->get('registry')->get('address') && $this->getShowOption('address'))
		{
			try
			{
				$sets['address'] = $this->loadTemplate('address');
			}
			catch (Exception $e)
			{
			}
		}

		if ($this->get('registry')->get('custom_profile'))
		{
			try
			{
				$sets['custom'] = $this->loadTemplate('custom');
			}
			catch (Exception $e)
			{
			}
		}

		// Get a list of configured segments
		$segments = $this->helper->config->get('profile_fieldset_order');

		// Display configured segments
		if (is_array($segments))
		{
			foreach ($segments as $segment)
			{
				if (!empty($sets[$segment]))
				{
					echo $sets[$segment];

					$sets[$segment] = null;
				}
			}
		}

		// Display remaining segments
		foreach ($sets as $set)
		{
			if (!empty($set))
			{
				echo $set;
			}
		}

		echo JHtml::_('ctechBootstrap.endTabs');
		?>
	</div>
	<div class="clearfix"></div>
</div>
