<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Supports Specs list
 */
class JFormFieldSpecs extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Specs';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		$value = (array) $this->value;

		// Initialize variables.
		$html = $this->getHtml($value);

		$this->loadJS();

		return $html;
	}

	/**
	 * Returns form field grid to edit a list of specifications
	 *
	 * @param  array $specs List of specifications
	 *
	 * @return string
	 */
	private function getHtml($specs)
	{
		ob_start();
		?>
		<div class="grid-table" style="margin-left: -172px;"><br>
			<div class="pull-right">
				<button type="button" class="btn btn-small btn-default hasTooltip" id="<?php echo $this->id ?>_addnew" title="" data-original-title="Add new Specification">
					<i class="icon-save-new"></i>
					<span class="hidden-xs">Add New</span>
				</button>
			</div>
			<div class="clearfix"></div>
			<br>
			<table class="border w100p table table-striped" id="<?php echo $this->id ?>_grid">
				<thead>
				<tr>
					<th style="border: 1px solid #c9c9c9; height: 35px; text-align: center">#</th>
					<th style="border: 1px solid #c9c9c9; height: 35px; text-align: center">LABEL</th>
					<th style="border: 1px solid #c9c9c9; height: 35px; text-align: center">VALUE</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i = 0;
				foreach ($specs as $spec) { ?>
					<tr>
						<td width="2%" align="center" style="border: 1px solid #c9c9c9;"><?php echo $i+1; ?></td>
						<td width="25%" style="border: 1px solid #c9c9c9;">
							<input type="text" class="inputbox form-control" name="<?php echo $this->name ?>[<?php echo $i ?>][label]" value="<?php echo isset($spec['label']) ? $spec['label'] : ''?>">
						</td>
						<td width="25%" style="border: 1px solid #c9c9c9;">
							<input type="text" class="inputbox form-control" name="<?php echo $this->name ?>[<?php echo $i ?>][value]" value="<?php echo isset($spec['value']) ? $spec['value'] : '' ?>">
						</td>
					</tr>
				<?php $i++; } ?>
				</tbody>
			</table>
		</div>

		<?php
		$html = ob_get_clean();

		return $html;
	}

	private function loadJS()
	{
		JHtml::_('jquery.framework');
		ob_start();
		?>
		jQuery(function($){
			$(document).ready(function(){
				var index = <?php echo count((array) $this->value) ?>;

				$('#<?php echo $this->id ?>_addnew').click(function(){
					var rowhtml = <?php echo json_encode($this->getRowHtml()); ?>;
					$('#<?php echo $this->id ?>_grid').find('tbody').prepend(rowhtml.replace(/##NUM##/g, index++));
				});
			});
		});
		<?php
		$js = ob_get_clean();

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	private function getRowHtml()
	{
		ob_start();
		?>
		<tr>
			<td width="2%" style="border: 1px solid #c9c9c9;"></td>
			<td width="25%" style="border: 1px solid #c9c9c9;">
				<input type="text" class="inputbox form-control" name="<?php echo $this->name ?>[##NUM##][label]">
			</td>
			<td width="25%" style="border: 1px solid #c9c9c9;">
				<input type="text" class="inputbox form-control" name="<?php echo $this->name ?>[##NUM##][value]">
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
}
