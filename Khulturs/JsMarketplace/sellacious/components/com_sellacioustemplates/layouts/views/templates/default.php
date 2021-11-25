<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));
$html     = array(
	'toolbar' => JLayoutHelper::render('joomla.searchtools.default', $tOptions),
	'head'    => $this->loadTemplate('head'),
	'body'    => $this->loadTemplate('body'),
);

$data = $this->getProperties();

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['view_item'] = 'template';

$options = array('client' => 2, 'debug' => 0);

echo JLayoutHelper::render('com_sellacious.view.list', $data, '', $options);

$url = 'index.php?option=' . $this->_option . '&view=template&layout=preview&tmpl=component';
?>
<div id="template-preview">
	<iframe
		id="template-preview-iframe"
		style="width: 100%; height: 100%;"
		src="<?php echo $url; ?>"
		frameborder="0"
		allowfullscreen
	>
	</iframe>
	<button class="btn-close-template">Close Preview</button>
</div>
<div class="preview-backdrop"></div>
