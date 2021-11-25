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

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

/** @var SellaciousViewOrders $this */
$app  = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.orders.tile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.orders.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
$me         = JFactory::getUser();

$orders = $this->items;
?>
<script>
	Joomla.submitbutton = function (task, form1) {
		var form = form1 || document.getElementById('adminForm');

		if (document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			form1 && Joomla.removeMessages();
			alert('<?php echo JText::_('COM_SELLACIOUS_ORDER_FORM_VALIDATION') ?>');
		}
	};
</script>

<div class="row">

    <div class="col-sm-12">
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
        <div id="order-box" class="order_sec">

        <div class="order-heading">
            <h2><?php echo JText::_('COM_SELLACIOUS_PRODUCT_ORDERED') ?><span> (<?php echo $this->pagination->get('total');?>&nbsp;<?php echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST_ITEMS') ?>)</span></h2>
            <select id="selct-order-type" class="pull-right">
                <option value="all">All</option>
                <option  selected="selected" value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>

        <div class="toggle-frame" id="order-toglle-frame">
            <?php
            foreach ($orders as $order)
            {
                echo $this->loadTemplate('tile', $order);
                echo $this->loadTemplate('tile_modals', $order);
            }
            ?>
            <input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
        </div>
        </div>
    </div>



</div>






<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
	method="post" name="adminForm" id="adminForm">
	<table class="w100p">
		<tr>
			<td class="text-center">
				<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

	<?php
	if ($tmpl = $app->input->get('tmpl'))
	{
		?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
	}

	if ($layout = $app->input->get('layout'))
	{
		?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
	}

	echo JHtml::_('form.token');
	?>
</form>
<div class="clearfix"></div>


