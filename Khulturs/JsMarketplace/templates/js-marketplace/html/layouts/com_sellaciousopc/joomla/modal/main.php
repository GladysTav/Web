<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

// Load bootstrap-tooltip-extended plugin for additional tooltip positions in modal
JHtml::_('bootstrap.tooltipExtended');

/**
 * Layout variables
 * ------------------
 * @var  array    $displayData
 *
 * @var   string  $selector  Unique DOM identifier for the modal. CSS id without #
 * @var   array   $params    Modal parameters. Default supported parameters:
 *                             - title        string   The modal title
 *                             - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true)
 *                                                     The string 'static' includes a backdrop which doesn't close the modal on click.
 *                             - keyboard     boolean  Closes the modal when escape key is pressed (default = true)
 *                             - closeButton  boolean  Display modal close button (default = true)
 *                             - animation    boolean  Fade in from the top of the page (default = true)
 *                             - url          string   URL of a resource to be inserted as an <iframe> inside the modal body
 *                             - height       string   height of the <iframe> containing the remote resource
 *                             - width        string   width of the <iframe> containing the remote resource
 *                             - bodyHeight   int      Optional height of the modal body in viewport units (vh)
 *                             - modalWidth   int      Optional width of the modal in viewport units (vh)
 *                             - footer       string   Optional markup for the modal footer
 * @var   string  $body      Markup for the modal body. Appended after the <iframe> if the URL option is set
 *
 */
extract($displayData);

$modalClasses = array('modal', 'hide');

if (!isset($params['animation']) || $params['animation'])
{
	$modalClasses[] = 'fade';
}

$modalAttributes = array(
	'tabindex' => '-1',
	'role'     => 'dialog',
	'class'    => implode(' ', $modalClasses)
);

if (isset($params['backdrop']))
{
	$modalAttributes['data-backdrop'] = (is_bool($params['backdrop']) ? ($params['backdrop'] ? 'true' : 'false') : $params['backdrop']);
}

if (isset($params['keyboard']))
{
	$modalAttributes['data-keyboard'] = (is_bool($params['keyboard']) ? ($params['keyboard'] ? 'true' : 'false') : 'true');
}

if (isset($params['url']) && ($params['url']))
{
	$script = JLayoutHelper::render('joomla.modal.iframescript', $displayData);
}
else
{
	$script = JLayoutHelper::render('joomla.modal.script', $displayData);
}

JFactory::getDocument()->addScriptDeclaration($script);
?>
<div id="<?php echo $selector; ?>" <?php echo ArrayHelper::toString($modalAttributes); ?>
	 aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<?php
			// Header
			if (!isset($params['closeButton']) || isset($params['title']) || $params['closeButton'])
			{
				echo JLayoutHelper::render('joomla.modal.header', $displayData);
			}

			// Body
			echo JLayoutHelper::render('joomla.modal.body', $displayData);

			// Footer
			if (isset($params['footer']))
			{
				echo JLayoutHelper::render('joomla.modal.footer', $displayData);
			}
			?>
		</div>
	</div>
</div>
