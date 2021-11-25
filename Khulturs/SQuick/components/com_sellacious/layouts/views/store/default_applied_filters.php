<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

if ($this->filters && count($this->filters))
{
	?>
	<div class="w100p filter-choosen">
		<?php
		foreach ($this->filters as $filter)
		{
			$selected = array();

			foreach ($filter->choices as $ck => $choice)
			{
				if ($choice->selected)
				{
					$selected[$ck] = $choice;
				}
			}

			if (count($selected))
			{
				?>
				<div class="btn-group">
				<label class="btn btn-small active btn-info cursor-normal"><?php echo $filter->title ?>: </label>
				<?php
				foreach ($selected as $ck => $choice)
				{
					?>
					<label for="filter_fields_f<?php echo $filter->id ?>_<?php echo (int) $ck ?>"
						   class="btn btn-small btn-default"><?php
					echo $this->helper->field->renderValue($choice->value, $filter->type); ?>
					<i class="fa fa-times cursor-pointer"></i></label><?php
				}
				?></div><?php
			}
		}
		?>
	</div>
	<div class="clearfix"></div>
	<?php
}
