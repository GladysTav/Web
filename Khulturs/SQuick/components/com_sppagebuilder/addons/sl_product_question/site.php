<?php
/**
 * @version     2.1.4
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

use Sellacious\Product;
use Sellacious\Seller;

class SppagebuilderAddonSL_Product_Question extends SppagebuilderAddons
{

	public function render()
	{

		$class             = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title             = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector  = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_questions_list = (isset($this->addon->settings->show_questions_list) && $this->addon->settings->show_questions_list) ? $this->addon->settings->show_questions_list : '0';
		$box_title  = (isset($this->addon->settings->ask_title) && $this->addon->settings->ask_title) ? $this->addon->settings->ask_title : '';

		$nullDt = JFactory::getDbo()->getNullDate();
		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($product)
		{
			$seller  = $helper->product->getSellers($product, false);

			if ($seller[0]->seller_uid)
			{
				$questions= $helper->product->getQuestions($product,$variant,$seller[0]->seller_uid);
				$form      = $helper->product->getQuestionForm($product,$variant,$seller[0]->seller_uid);

				if (!($form instanceof JForm) || count($fieldset = $form->getFieldset()) == 0)
				{
					$html = '';
				}
				else
				{
					ob_start();
					?>
					<div class="moreinfo-box" >
						<?php echo ($box_title) ?'<h3>' . $box_title . '</h3>' : ''; ?>
						<div class="innermoreinfo questionbox">
							<div id="questionBox">
								<form action="<?php echo JUri::base(); ?>/index.php" method="post" name="questionForm"
									  id="questionForm" class="innermoreinfo form-validate form-vertical" enctype="multipart/form-data">

									<fieldset>
										<?php
										echo $form->getInput('p_id');
										echo $form->getInput('v_id');
										echo $form->getInput('s_uid');

										$questioner_name  = $form->getField('questioner_name');
										$questioner_email = $form->getField('questioner_email');

										?>

										<div class="questionformarea">
											<?php if ($questioner_name || $questioner_email): ?>
												<div class="sell-row">
													<?php if ($field = $questioner_name): ?>
														<div class="sell-col-xs-12 <?php echo $questioner_name ? 'sell-col-sm-6' : ''; ?>">
															<div class="formfield">
																<?php echo $field->input; ?>
															</div>
														</div>
													<?php endif; ?>

													<?php if ($field = $questioner_email): ?>
														<div class="sell-col-xs-12 <?php echo $questioner_email ? 'sell-col-sm-6' : ''; ?>">
															<div class="formfield">
																<?php echo $field->input; ?>
															</div>
														</div>
													<?php endif; ?>
												</div>
											<?php endif; ?>
											<?php if ($field = $form->getField('question')): ?>
												<div class="formfield">
													<?php echo $field->input; ?>
												</div>
											<?php endif; ?>
											<?php if ($field = $form->getField('captcha')): ?>
												<div class="formfieldcaptcha">
													<?php echo $field->input; ?>
												</div>
											<?php endif; ?>

											<button type="button" class="btn btn-primary questionbtn" onclick="Joomla.submitform('product.saveQuestion', this.form);">
												<i class="fa fa-location-arrow"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_SUBMIT'); ?>
											</button>
										</div>
									</fieldset>

									<input type="hidden" name="task" value=""/>
									<input type="hidden" name="option" value="com_sellacious"/>
									<?php echo JHtml::_('form.token'); ?>
								</form>
							</div>
						</div>
						<?php
					if($show_questions_list && $questions)
					{ ?>
						<div class="questionanswerbox" class="qnabox">
							<h4><?php echo JText::_('COM_SELLACIOUS_TITLE_QA') ?></h4>
							<?php echo '<hr class="isolate">'; ?>
							<div class="table-questions">
								<?php
								foreach ($questions as $question)
								{ ?>
									<dl class="dl-horizontal dl-leftside">
										<dt>
											<span class="pqn-title pqn-block">
												<strong><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_QUESTION_LABEL') ?>: </strong>
											</span>
										</dt>
										<dd>
											<span class="pqn-question pqn-block"><?php echo $question->question ?></span>
											<span class="pqn-author"><?php echo $question->questioner_name ?></span>
											<span class="pqn-date"><?php echo JHtml::_('date', $question->created, 'M d, Y'); ?></span>
										</dd>
										<dt>
											<span class="pqn-title pqn-block"><strong><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_ANSWER_LABEL') ?>: </strong></span></dt>
										<dd>
											<span class="pqn-answer pqn-block">
												<?php echo $question->answer ?>
											</span>
											<span class="answeredbyseller">
												<?php if(isset($question->seller)) :
													$repliedBy = $question->seller->store_name ?: $question->seller->title ?: $question->seller->name ?: $question->seller->username ?: '';
												?>
													<span class="pqn-author"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_REPLIED_BY') . $repliedBy ?></span>
													<span class="pqn-date">
														<?php
														if ($question->replied != $nullDt && !empty($question->replied))
														{
															echo JHtml::_('date', $question->replied, 'M d, Y');
														}
														?>
													</span>
												<?php endif; ?>
											</span>
										</dd>
									</dl>
									<?php
								} ?>
							</div>
						</div>
					<?php } ?>
					</div>
					<?php
					$html = ob_get_clean();
				}
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-question ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}



	public function stylesheets()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-product-question.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css'
		);
	}
}

