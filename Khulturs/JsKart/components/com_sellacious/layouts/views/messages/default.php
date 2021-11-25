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

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

/** @var SellaciousViewOrders $this */
$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$me  = JFactory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('behavior.formvalidator');
JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.messages.tile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JText::script('COM_SELLACIOUS_MESSAGE_REPLY_TITLE');
JText::script('COM_SELLACIOUS_MESSAGE_SENDING_REPLY');

JHtml::_('stylesheet', 'com_sellacious/fe.view.message.bubbles.css', null, true);
JHtml::_('script', 'com_sellacious/fe.view.message.bubbles.js', null, true);

$messages  = $this->items;
$model     = $this->getModel('Messages');
$recipient = $app->input->getInt('recipient', 0);
?>
<div class="ctech-wrapper">
	<div class="message-tabs">
		<?php
		if ($messages):
            if ($recipient > 0)
            {
                $messageSingle = array_values(array_filter($messages, function ($item) use ($recipient){
                   return ($item->sender == $recipient || $item->recipient == $recipient);
                }));

                $defaultThreadId = $messageSingle[0]->id;
            }
            else
            {
	            $messageSingle   = reset($messages);
	            $defaultThreadId = $messageSingle->id;
            }
			echo JHtml::_('ctechBootstrap.startTabs', 'messages_tabs', array('vertical' => true, 'active' => 'tab_' . $defaultThreadId));

			$model->readThread($defaultThreadId);

			foreach ($messages as $message)
			{
				$tab = JLayoutHelper::render('com_sellacious.messages.threadtab', $message);

				echo JHtml::_('ctechBootstrap.addTab', 'tab_' . $message->id, $tab, 'messages_tabs');

				echo JLayoutHelper::render('com_sellacious.messages.bubbles', $message);
				echo JHtml::_('ctechBootstrap.endTab');
			}

			echo JHtml::_('ctechBootstrap.endTabs');
		endif;
		?>
	</div>
	<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
	      method="post" name="adminForm" id="adminForm">
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
		<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
	</form>
	<div class="ctech-clearfix"></div>
</div>
