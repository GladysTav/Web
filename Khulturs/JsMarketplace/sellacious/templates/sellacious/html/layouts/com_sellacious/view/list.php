<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousView $that */
/** @var array $displayData */
$data = (object) $displayData;
$that = &$data->view;

// Load the behaviors.
JHtml::_('jquery.framework');

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'com_sellacious/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE, 'relative' => true));

$doc = JFactory::getDocument();

$listOrder      = $that->escape($data->state->get('list.ordering'));
$listDirn       = $that->escape($data->state->get('list.direction'));
$originalOrders = array();

if (!isset($data->script) || $data->script == true):

	$message = JText::_(strtoupper($that->getOption()) . '_' . strtoupper($data->name) . '_CONFIRM_DELETE', true);
	$count   = count($data->items);

	$doc->addScriptDeclaration(
			<<<JS
		jQuery(document).ready($ => { $('select').select2(); });

		Joomla.submitbutton = function (task) {
			if (task === '{$data->name}.delete') {
				let f = document['adminForm'];
				let cb;
				for (let i = 0 ; i < $count ; i++) {
					cb = f['cb' + i];
					if (cb && cb.checked) {
						if (confirm("{$message}")) {
							Joomla.submitform(task);
						}
						return;
					}
				}
			}
			Joomla.submitform(task);
		}
JS
	);

endif; ?>

<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')); ?>"
      method="post" name="adminForm" id="adminForm" class="form-horizontal">

	<div class="search-filter-offcanvas">
		<?php echo isset($data->html['toolbar']) ? $data->html['toolbar'] : '' ?>
	</div>

	<div class="clearfix"></div>

	<div id="component-inner-container">
	<div class="table-responsive">
		<table id="<?php echo $data->view_item ?>List" class="w100p table table-striped table-bordered table-hover">
			<thead>
			<?php echo $data->html['head']; ?>
			</thead>
			<tbody>
			<?php echo $data->html['body']; ?>
			</tbody>
			<?php if (isset($data->pagination) && $data->pagination instanceof JPagination): ?>
			<tfoot>
			<tr>
				<td colspan="100" class="center">
					<?php echo $data->pagination->getListFooter(); ?><br/>
					<?php echo $data->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<?php endif; ?>
		</table>
	</div>

	<?php echo isset($data->html['batch']) ? $data->html['batch'] : ''; ?>

	<?php echo isset($data->html['custom']) ? $data->html['custom'] : ''; ?>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
