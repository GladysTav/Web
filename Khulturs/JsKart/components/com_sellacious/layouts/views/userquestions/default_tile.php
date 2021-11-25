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

/** @var  SellaciousViewUserQuestions  $this */
/** @var  stdClass  $tplData */
$question = $tplData;
$replied  = $question->state == 1;
$url      = JRoute::_(sprintf('index.php?option=com_sellacious&view=product&p=%s', $question->product_code));

$productTitle = $question->product_title;

if ($question->variant_title)
{
	$productTitle .= ' -' .$question->variant_title;
}

$productTitle = $productTitle . ' (' . $question->product_code . ')';

?>
<div class="question-basic-details">
	<div class="question-basic-head">
		<span><?php echo JHtml::_('date', $question->created, 'D, F d, Y h:i A') ?></span>
		<i class="fa question-<?php echo $replied ? 'replied fa-check ctech-text-success' : 'not-replied fa-warning ctech-text-warning' ?>"> </i>
		<span class="ctech-float-right">
			<?php if ($replied): ?>
				<b class="ctech-text-success"><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_STATUS_REPLIED'); ?></b>
			<?php else: ?>
				<b class="ctech-text-warning"><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_STATUS_PENDING_REPLY'); ?></b>
			<?php endif; ?>
		</span>
	</div>
	<div class="question-basic-body">
		<div class="question-item">
			<div class="question-item-info">
				<div class="ctech-row">
					<div class="ctech-col-md-3"><strong><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_PRODUCT'); ?></strong></div>
					<div class="ctech-col-md-9"><a href="<?php echo $url?>"><?php echo $productTitle; ?></a></div>
				</div>
				<div class="ctech-row">
					<div class="ctech-col-md-3"><strong><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_QUESTION'); ?></strong></div>
					<div class="ctech-col-md-9"><?php echo $question->question; ?></div>
				</div>
				<?php if ($replied): ?>
					<div class="ctech-row">
						<div class="ctech-col-md-3"><strong><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_ANSWER'); ?></strong></div>
						<div class="ctech-col-md-9"><?php echo $question->answer; ?></div>
					</div>
					<?php if(isset($question->seller)) :
						$repliedBy = $question->seller->store_name ?: $question->seller->title ?: $question->seller->name ?: $question->seller->username ?: '';?>
						<div class="ctech-row">
							<div class="ctech-col-md-3"><strong><?php echo JText::_('COM_SELLACIOUS_USER_QUESTIONS_ANSWERED_BY'); ?></strong></div>
							<div class="ctech-col-md-9"><?php echo $repliedBy . ' (' . JHtml::_('date', $question->replied, 'M d, Y') . ')'; ?></div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
