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

/** @var  \SellaciousViewProfile $this */
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.loadCss');

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);



$fieldsets = $this->get('form')->getFieldsets();
$me         = JFactory::getUser();

?>


<div class="row">

    <div class="col-sm-12 ">
        <div class="page-brdcrmb">
            <?php
            jimport('joomla.application.module.helper');
            $modules = JModuleHelper::getModules('breadcrumbs');
            foreach ($modules as $module):
                $renMod = JModuleHelper::renderModule($module);

                if (!empty($renMod) && ($module->module == "mod_breadcrumbs")):?>
                    <div class="relatedproducts <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                        <div class="moreinfo-box">
                            <?php
                            if ($module->showtitle == 1) { ?>
                                <h3><?php echo $module->title ?></h3>
                            <?php } ?>
                            <div class="innermoreinfo">
                                <div class="relatedinner">
                                    <?php echo trim($renMod); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                        <?php echo trim($renMod); ?>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>
    <?php if (!$me->guest): ?>
        <div  class="col-sm-3 left-bar-wish hidden-xs hidden-sm">
            <?php
            jimport('joomla.application.module.helper');
            $modules = JModuleHelper::getModules('component-left');
            foreach ($modules as $module):
                $renMod = JModuleHelper::renderModule($module);

                if (!empty($renMod) && ($module->module == "mod_sellacious_inner_sidemenu")):?>
                    <div class="relatedproducts <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                        <div class="moreinfo-box">
                            <?php
                            if ($module->showtitle == 1) { ?>
                                <h3><?php echo $module->title ?></h3>
                            <?php } ?>
                            <div class="innermoreinfo">
                                <div class="relatedinner">
                                    <?php echo trim($renMod); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                        <?php echo trim($renMod); ?>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-sm-9' ?> order_pro_box">
        <div class="all_info_user">

        <div class="all_info_user_heading">
            <h2><?php echo JText::_('COM_SELLACIOUS_COMPLETE_PROFILE') ?>
                <ul class="btn-toolbar pull-right manage-group">
                    <li class="btn-group ">
                        <a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses'); ?>"
                           class="manage pull-right"><i class="fa fa-gear"></i> <?php echo JText::_('COM_SELLACIOUS_ADDRESSES_MANAGE_LABEL') ?></a>
                    </li>
                    <li class="btn-group">
                        <a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=profile&layout=edit'); ?>"
                           class="manage pull-right"><i class="fa fa-pencil-square"></i> <?php echo JText::_('COM_SELLACIOUS_PROFILE_EDIT_LABEL') ?></a>
                    </li>
                </ul></h2>

        </div>
        <div class="profile">

            <?php
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
                $sets['bank_tax_info'] = $this->loadTemplate('banking');
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
            ?>
        </div>

    </div>
    </div>


    </div>



<div class="clearfix"></div>
