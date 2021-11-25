<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

$templates = $this->getTemplates('images');
?>
<div id="import-images" class="import-tab">
	<?php
	foreach ($templates as $template)
	{
		echo $this->renderLayout('section_main', $template, 'default');
	}

	if ($this->helper->access->check('template.create', null, 'com_importer') && $this->helper->access->check('template.edit', null, 'com_importer'))
	{
		$template = new stdClass;

		$template->id          = 0;
		$template->import_type = 'images';
		$template->title       = JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_TITLE');
		$template->alias       = JApplicationHelper::stringURLSafe($template->title);
		$template->mapping     = array();
		$template->override    = 1;

		echo $this->renderLayout('section_main', $template, 'default');
	}
	elseif (count($templates) == 0)
	{
		?>
		<div class="alert alert-info"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_NO_IMPORT_TEMPLATE_MESSAGE') ?></div>
		<?php
	}
	?>
</div>