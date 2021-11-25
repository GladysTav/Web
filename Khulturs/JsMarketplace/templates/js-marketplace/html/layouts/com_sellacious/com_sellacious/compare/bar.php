<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var   JLayoutFile $this */
/** @var   \Sellacious\Product[] $displayData */
JHtml::_('stylesheet', 'com_sellacious/fe.view.compare.css', null, true);
$items = $displayData;
$codes = array();

if (is_array($items) && array_filter($items))
{
    $count = 0;

    foreach ($items as $item)
    {
        if (is_object($item))
        {
           $count++;
        }
    }
	?>



        <div class="circle-toggle" id="toggle-compare">
            <div class="circle-toggle-box">
                <div class="icon-comp fa fa-balance-scale">
                    <div class="count-comp"><?php echo $count ?></div>
                </div>
            </div>
        </div>
    <div style="display: none;" class="backdrop-comp">

    <div  class="compare-tbl" >
		<div class="w100p">
            <h2 class="comapre-page-title-sidebar"><i class="fa fa-balance-scale "></i> <?php echo JText::_("SELLA_SELLACIOUS_COMPARE_LABEL");?>  <i class="pull-right close fa fa-close"></i> </h2>


                <div class="tbl-compar row">
                    <div class="col-sm-12">

                        <?php foreach ($items as $item) : ?>
                            <?php
                            if (is_object($item))
                            {
                                $layoutId = 'bar_item';
                                $codes[]  = $item->getCode();
                            }
                            else
                            {
                                $layoutId = 'bar_noitem';
                            }
                            ?>
                            <span style="width: <?php echo 900 / count($items) ?>px;"
                                  class="<?php echo $layoutId ?>"><?php echo $this->sublayout($layoutId, $item); ?></span>

                        <?php endforeach; ?>
                        <span class="compare-submit"><?php
                            if (count($items) >= 2):
                                ?><a class="btn btn-success compare-btn" href="<?php
                            echo JRoute::_('index.php?option=com_sellacious&view=compare&c=') . implode(',', $codes); ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
                            else:
                                ?><a href="#" class="btn btn-success disabled"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
                            endif;
                            ?></span>


                    </div>
                </div>


		</div>
	</div>
    </div>






	<?php
}
else
{
//	echo '<div class="hidden w100p"></div>';
    echo ' <div class="circle-toggle" id="toggle-compare"></div>';
}
?>

<script>

    (function ($) {
        $(document).ready(function(){
            $("#toggle-compare").click(function () {
                $(".backdrop-comp").animate({
                    width: "toggle"
                });
            });

            $(".close").click(function () {
                $(".backdrop-comp").animate({
                    width: "toggle"
                });
            })




        });



    }) (jQuery);

</script>
