<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
use Sellacious\Toolbar\Button\CustomButton;
use Sellacious\Toolbar\Button\PopupButton;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * View to edit template
 *
 * @since   1.7.0
 */
class SellacioustemplatesViewTemplate extends SellaciousViewForm
{
	/**
	 * @var    string
	 *
	 * @since   1.7.0
	 */
	protected $action_prefix = 'template';

	/**
	 * @var    string
	 *
	 * @since   1.7.0
	 */
	protected $view_item = 'template';

	/**
	 * @var    string
	 *
	 * @since   1.7.0
	 */
	protected $view_list = 'templates';

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	public function display($tpl = null)
	{
		if (!isset($this->state))
		{
			$this->state = $this->get('State');
		}

		if (!isset($this->item))
		{
			$this->item  = $this->get('Item');
		}

		if (!isset($this->form))
		{
			$this->form  = $this->get('Form');
		}

		if ($this->_layout == 'preview')
		{
			$previewContext = $this->app->getUserState('com_sellacioustemplates.preview_context');

			if ($previewContext == 'view_order.pdf')
			{
				$filename = $this->app->input->get('fname', 'invoice_preview');
				$options  = array(
					'filename' => $filename,
					'title'    => 'Invoice',
					'subject'  => 'Invoice',
				);

				ob_start();
				parent::display();
				$content = ob_get_clean();

				$this->helper->order->renderInvoicePdf($this->item, $content, $options, true);

				return true;
			}
			elseif ($previewContext == 'view_order.invoice')
			{
				JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
				JHtml::_('stylesheet', 'com_sellacious/fe.view.order.invoice.css', null, true);

				return parent::display($tpl);
			}
			elseif ($previewContext == 'view_order.print')
			{
				JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
				JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
				JHtml::_('stylesheet', 'com_sellacious/fe.view.order.print.css', null, true);

				return parent::display($tpl);
			}
			elseif ($previewContext == 'backoffice_order.invoice')
			{
				JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
				JHtml::_('stylesheet', 'com_sellacious/view.order.invoice.css', array('version' => S_VERSION_CORE, 'relative' => true));

				return parent::display($tpl);
			}
		}
		else
		{
			return parent::display($tpl);
		}
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function setPageTitle()
	{
		$title = JText::_(strtoupper($this->getOption() . '_TITLE_' . $this->getName()));

		if ($this->item->get('id'))
		{
			$title .= ' - ' . ucwords(str_replace(array('_', '.'), array(' ', ' - '), $this->item->get('context')));
		}

		JToolBarHelper::title($title, 'file');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		if ($isNew ? $this->helper->access->check($this->action_prefix . '.create') : $this->helper->access->check($this->action_prefix . '.edit'))
		{
			$bar = Toolbar::getInstance();
			$bar->appendButton(new CustomButton('<button type="button" class="btn btn-default hidden btn-small btn-restore hasTooltip"  title="Restore Default">
				<i class="icon-redo-2"></i>
				<span class="hidden-xs">Restore Default</span>
			</button>'));

			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');

			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
