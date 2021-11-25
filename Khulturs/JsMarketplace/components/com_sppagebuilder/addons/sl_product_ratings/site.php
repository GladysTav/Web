<?php
/**
 * @version     2.2.0
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

class SppagebuilderAddonSL_Product_Ratings extends SppagebuilderAddons
{

	public function render()
	{

		$class             = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title             = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector  = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_ratings      = (isset($this->addon->settings->show_ratings) && $this->addon->settings->show_ratings) ? $this->addon->settings->show_ratings : '0';
		$show_ratings_form = (isset($this->addon->settings->show_ratings_form) && $this->addon->settings->show_ratings_form) ? $this->addon->settings->show_ratings_form : '0';
		$show_reviews_list = (isset($this->addon->settings->show_reviews_list) && $this->addon->settings->show_reviews_list) ? $this->addon->settings->show_reviews_list : '0';
		$box_title  = (isset($this->addon->settings->ratings_title) && $this->addon->settings->ratings_title) ? $this->addon->settings->ratings_title : '';

		$rClass = '';
		if ($show_ratings && $show_ratings_form)
		{
			$rClass = 'col-md-5';
			$rfClass = 'col-md-7';
		}
		if (!$show_ratings && $show_ratings_form || $show_ratings && !$show_ratings_form)
		{
			$rClass = 'col-md-12';
			$rfClass = 'col-md-12';
		}

		$app     = JFactory::getApplication();
		$input   = $app->input;
		$product = $input->getInt('product');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($product)
		{
			$ratings = $helper->rating->getProductRating($product);
			$seller  = $helper->product->getSellers($product, false);

			if ($seller[0]->seller_uid)
			{
				$stats   = $this->getReviewStats($product, $helper);
				$reviews = $this->getReviews($product, $helper);
				$form    = $helper->rating->getForm($product, 0, $seller[0]->seller_uid);

				if (!($form instanceof JForm) || count($fieldset = $form->getFieldset()) == 0)
				{
					$html = '';
				}
				else
				{
					ob_start();
					?>
					<div class="moreinfo-box">
						<?php echo ($box_title) ?'<h3>' . $box_title . '</h3>' : ''; ?>
						<div class="innermoreinfo">
							<div class="row">
								<?php if ($show_ratings)
								{ ?>
									<div class="<?php echo $rClass ?>">
										<div class="ratingmeter">
											<div class="ratingaverage">
												<div class="star-lg"><?php echo number_format($ratings->rating, 1); ?></div>
												<h4 class="avg-rating"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_RATING_AVERAGE_BASED_ON', $ratings->count); ?></h4>
											</div>
											<table class="rating-statistics">
												<tbody>
												<?php for ($i = 1; $i <= 5; $i++): ?>
													<?php
													$stat    = \Joomla\Utilities\ArrayHelper::getValue($stats, $i, null);
													$count   = isset($stat->count) ? $stat->count : 0;
													$percent = isset($stat) ? ($stat->count / $stat->total * 100) : 0;
													?>
													<tr>
														<td class="nowrap" style="width:90px;">
															<div class="rating-stars rating-stars-md star-<?php echo $i * 2 ?>">
																&nbsp;<?php echo number_format($i, 1); ?></div>
														</td>
														<td class="nowrap rating-progress">
															<div class="progress progress-sm">
																<div class="progress">
																	<div class="progress-bar" role="progressbar"
																		style="width: <?php echo $percent ?>%"></div>
																</div>
															</div>
														</td>
														<td class="nowrap" style="width:60px;"><?php echo $count ?> ratings</td>
													</tr>
												<?php endfor; ?>
												</tbody>
											</table>
										</div>
									</div>
								<?php } ?>
								<?php if ($show_ratings_form) { ?>
									<div class="<?php echo $rfClass ?>">
										<div class="reviewform" id="reviewBox">
											<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="ratingForm"
												id="ratingForm" class="form-validate form-vertical" enctype="multipart/form-data">

												<fieldset>
													<?php
													echo $form->getInput('product_id');
													echo $form->getInput('variant_id');
													echo $form->getInput('seller_uid');

													$author_name  = $form->getField('author_name');
													$author_email  = $form->getField('author_email');

													?>

													<div class="revformarea">
														<?php if ($author_name || $author_email): ?>
															<div class="row nomargin">
																<?php if ($field = $author_name): ?>
																<div class="<?php echo $author_name ? 'col-sm-6' : 'col-sm-12'; ?> nopadd">
																	<div class="formfield">
																		<?php echo $field->input; ?>
																		<?php echo $field->label; ?>
																	</div>
																</div>
																<?php endif; ?>

																<?php if ($field = $author_email): ?>
																<div class="<?php echo $author_email ? 'col-sm-6' : 'col-sm-12'; ?> nopadd">
																	<div class="formfield">
																		<?php echo $field->input; ?>
																		<?php echo $field->label; ?>
																	</div>
																</div>
																<?php endif; ?>
															</div>
														<?php endif; ?>

														<?php if ($field = $form->getField('rating', 'product')): ?>
															<div class="formfieldstar">
																<?php echo $field->label; ?>
																<?php echo $field->input; ?>
															</div>
														<?php endif; ?>
														<?php if ($field = $form->getField('title', 'product')): ?>
															<div class="formfieldinput">
																<?php echo $field->input; ?>
															</div>
														<?php endif; ?>
														<?php if ($field = $form->getField('comment', 'product')): ?>
															<div class="formfieldinput">
																<?php echo $field->input; ?>
															</div>
														<?php endif; ?>

														<?php
														$fieldSR[] = $form->getField('rating', 'seller');
														$fieldSR[] = $form->getField('rating', 'packaging');
														$fieldSR[] = $form->getField('rating', 'shipment');
														$fieldSR   = array_filter($fieldSR);

														if (count($fieldSR)): ?>
														<div class="formfieldstar">
															<?php foreach ($fieldSR as $field): ?>
																<div class="form-field">
																	<?php echo $field->label; ?>
																	<?php echo $field->input; ?>
																</div>
															<?php endforeach; ?>
															<?php endif; ?>

															<?php if ($field = $form->getField('title', 'seller')): ?>
																<div class="formfield">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<?php if ($field = $form->getField('comment', 'seller')): ?>
																<div class="formfieldinput">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<?php if ($field = $form->getField('title', 'packaging')): ?>
																<div class="formfield">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<?php if ($field = $form->getField('comment', 'packaging')): ?>
																<div class="formfieldinput">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<?php if ($field = $form->getField('title', 'shipment')): ?>
																<div class="formfield">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<?php if ($field = $form->getField('comment', 'shipment')): ?>
																<div class="formfieldinput">
																	<?php echo $field->input; ?>
																</div>
															<?php endif; ?>

															<button type="button" class="btn btn-primary reviewbtn" onclick="Joomla.submitform('product.saveRating', this.form);">
																<i class="fa fa-edit"></i> Submit
															</button>
														</div>
												</fieldset>

												<input type="hidden" name="task" value="" />
												<input type="hidden" name="option" value="com_sellacious" />
												<?php echo JHtml::_('form.token'); ?>
											</form>
										</div>
										<script type="text/javascript">
											jQuery('.revformarea .formfield input').blur(function () {
												if (jQuery(this).val() !== '') {
													jQuery(this).next().addClass('gone');
												}
												if (jQuery(this).val() == '') {
													jQuery(this).next().removeClass('gone');
												}
											});
										</script>
									</div>
								<?php } ?>
							</div>
							<div class="clearfix"></div>
							<?php if ($show_reviews_list)
							{ ?>
								<div class="reviewslist">
									<?php
									foreach ($reviews as $review)
									{
										?>
										<div class="row nomargin">
											<div class="col-xs-3 col-xxs-12 nopadd">
												<div class="reviewauthor">
													<div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
														<span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
													</div>
													<h4 class="pr-author"><?php echo $review->author_name ?></h4>
													<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
													<?php if ($review->buyer == 1): ?>
														<div class="buyer-badge">Certified Buyer</div>
													<?php endif; ?>
												</div>
											</div>
											<div class="col-xs-9 col-xxs-12 nopadd">
												<div class="reviewtyped">
													<h3 class="pr-title"><?php echo $review->title ?></h3>
													<p class="pr-body"><?php echo $review->comment ?></p>
												</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							<?php } ?>
						</div>
					</div>
					<?php
					$html = ob_get_clean();
				}
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function getReviewStats($id, $helper)
	{
		$list = array();

		$filters = array(
			'list.select' => array('COUNT(1) AS count'),
			'list.where'  => array('a.rating > 0'),
			'type'        => 'product',
			'product_id'  => (int) $id,
			'state'       => 1,
		);
		$total   = (int) $helper->rating->loadResult($filters);

		if ($total > 0)
		{
			$filters['list.select'] = array('a.rating', 'COUNT(1) AS count', "$total AS total");
			$filters['list.group']  = 'a.rating';
			$filters['list.limit']  = '10';

			$list = $helper->rating->loadObjectList($filters, 'rating');
		}

		return $list;
	}

	public function getReviews($id, $helper)
	{
		$filters = array(
			'type'       => 'product',
			'product_id' => (int) $id,
			'state'      => 1,
			'list.where' => "a.comment != ''"
		);
		$list    = $helper->rating->loadObjectList($filters);

		return $list;
	}

	public function stylesheets()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-product-rating.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-ratings.css'
		);
	}
}

