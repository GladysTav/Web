<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

$helper     = SellaciousHelper::getInstance();
$c_currency = $helper->currency->current('code_3');

/** @var array  $displayData */
if (count($displayData['shoprules']))
{
	$order = new Registry($displayData['order']);
	?>
		<?php
		$count = 0;

		foreach ($displayData['shoprules'] as $ri => $rule)
		{
			$style = '';

			if ($count >= 1)
			{
				$style = 'style = "border-top: 1px solid #dfdfdf;"';
			}

			settype($rule, 'object');

			if ($rule->change != 0)
			{
				?>
				<tr <?php echo $style ?>>
					<td style="padding: 3px; line-height: 1.4;">
						<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
						<?php echo $rule->title; ?>
					</td>
					<td style="padding: 3px; line-height: 1.4; white-space: nowrap; text-align: right;">
						<em style="font-style: italic;">
							<?php
							$rule_base = $helper->currency->display($rule->input, $order->get('currency'), $c_currency, true);

							if ($rule->percent)
							{
								$change_value = number_format($rule->amount, 2);
								echo sprintf('@%s%% on %s', $change_value, $rule_base);
							}
							else
							{
								$change_value = $helper->currency->display($rule->amount, $order->get('currency'), $c_currency, true);
								echo sprintf('%s over %s', $change_value, $rule_base);
							}
							?>
						</em>
					</td>
					<td style="padding: 3px; line-height: 1.4; white-space: nowrap;text-align: right;">
						<?php
						$value = $helper->currency->display(abs($rule->change), $order->get('currency'), $c_currency, true);
						echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
						?>
					</td>
					<td style="padding: 3px; line-height: 1.4; white-space: nowrap;text-align: right;">
						<?php echo $helper->currency->display($rule->output, $order->get('currency'), $c_currency, true) ?></td>
				</tr>
				<?php
			}
			$count++;
		}
		?>
	<?php
}
