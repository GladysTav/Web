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

/** @var SellaciousViewUserQuestions $this */
$app = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.view.userquestions.tile.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));

$questions = $this->items;
?>
<div class="ctech-wrapper">
	<div class="question-tabs">
		<?php
		if ($questions):
			$questionSingle = reset($questions);

			echo JHtml::_('ctechBootstrap.startTabs', 'questions_tabs', array('vertical' => true, 'active' => 'tab_' . $questionSingle->id));

			foreach ($questions as $question)
			{
				$read_icon = $question->state == 1 ? 'replied fa-check ctech-text-success' : 'not-replied fa-warning ctech-text-warning';

				$tabTitle = '<div class="question-tab-details">
							<span class="question-title">' . $question->question . '</span>
							</span>&nbsp; <i class="fa question-' . $read_icon . '"> </i></span></div>';
				echo JHtml::_('ctechBootstrap.addTab', 'tab_' . $question->id, $tabTitle, 'questions_tabs');
				echo $this->loadTemplate('tile', $question);
				echo JHtml::_('ctechBootstrap.endTab');
			}

			echo JHtml::_('ctechBootstrap.endTabs');
		endif;
		?>
		<?php echo JHtml::_('form.token'); ?>
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
	<div class="ctech-clearfix"></div>
</div>
