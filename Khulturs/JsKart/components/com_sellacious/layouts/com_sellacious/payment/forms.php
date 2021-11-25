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

/** @var  stdClass   $displayData */
/** @var  stdClass[] $methods */
$methods = $displayData->methods;
$helper  = SellaciousHelper::getInstance();
?>
<div id="payment-methods">
	<?php
	if (count($methods) > 1)
	{
		$active = reset($methods);
		?>
        <div class="ctech-row">
            <div class="ctech-col-sm-4">
                <ul class="ctech-nav ctech-nav-tabs">
                    <?php
                    $count = 0;
                    foreach ($methods as $i => $method):
                        if (isset($method->form) && $method->form instanceof JForm): ?>
                            <li class="ctech-nav-item"><a class="ctech-nav-link <?php echo $count == 0 ? 'ctech-active' : ''; ?>" href="#tab_<?php echo $method->id; ?>" data-toggle="ctech-tab"><?php echo $method->title; ?></a></li>
                        <?php
                            $count++;
                        endif;
                    endforeach; ?>
                </ul>
            </div>
            <div class="ctech-col-sm-8">
                <div class="ctech-tab-content">
                    <?php
                    $count = 0;
                    foreach ($methods as $i => $method):
                        if (isset($method->form) && $method->form instanceof JForm): ?>
                            <div class="ctech-tab-pane ctech-fade <?php echo $count == 0 ? 'ctech-show ctech-active' : ''; ?>" id="tab_<?php echo $method->id; ?>">
                                <?php
                                $override = isset($method->layout) && is_file($method->layout);
                                $file     = $override ? basename($method->layout, '.php') : 'com_sellacious.payment.form';
                                $dir      = $override ? dirname($method->layout) : '';

                                echo JLayoutHelper::render($file, $method, $dir);
                                ?>
                            </div>
                        <?php
                            $count++;
                        endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    <?php
	}
	else if (count($methods) == 1)
	{
	?>
		<div class="ctech-wrapper">
			<div class="ctech-container-fluid">
				<div class="ctech-row">
					<?php
						$method = reset($methods);
						if (isset($method->form) && $method->form instanceof JForm):
						?>
							<div class="single-payment-method" id="tab_<?php echo $method->id; ?>">
								<?php if ($helper->config->get('show_single_payment_method_title', '1')): ?>
								<h4 class="ctech-border-bottom ctech-pb-1"><?php echo $method->title; ?></h4>
								<?php endif;

								$override = isset($method->layout) && is_file($method->layout);
								$file     = $override ? basename($method->layout, '.php') : 'com_sellacious.payment.form';
								$dir      = $override ? dirname($method->layout) : '';

								echo JLayoutHelper::render($file, $method, $dir);
								?>
							</div>
							<?php
						endif;
					?>
				</div>
			</div>
		</div>
	<?php
	}
	else
	{
		echo '<div class="center">';
		echo JText::_('COM_SELLACIOUS_CART_PAYMENT_METHOD_NOT_AVAILABLE');
		echo '</div>';
	}
	?>
</div>
