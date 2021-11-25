<?php
/**
 * @version     1.1.0
 * @package     Slider Ninja
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Asfaque Ali Ansari <info@bhartiy.com> - http://www.bhartiy.com
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_('stylesheet', 'mod_sliderninja/owl.carousel.min.css', null, true);

if ($sliderNinja_Params['loadjquery'] == "1" ) :
	JHtml::_('script', 'mod_sliderninja/jquery.min.js', null, true);
endif;

JHtml::_('stylesheet', 'mod_sliderninja/style.css', null, true);
?>

<div class="sliderninja <?php echo $sliderNinja_Params['sliderclass']; ?>">
	<div id="sliderninja_container<?php echo $module->id ?>" class="owl-carousel">

		<?php
		// Slide 1
		if ($sliderNinja_Params['enableitem1'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg1']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title1']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description1']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn1'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink1']; ?>" class="btn btn-primary"><?php echo $sliderNinja_Params['btn1']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn1'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink1']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg1']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg1']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif;

		// Slide 2

		if ($sliderNinja_Params['enableitem2'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg2']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title2']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description2']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn2'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink2']; ?>" class="btn btn-primary">
											<?php echo $sliderNinja_Params['btn2']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn2'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink2']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg2']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg2']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif;

		// Slide 3

		if ($sliderNinja_Params['enableitem3'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg3']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title3']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description3']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn3'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink3']; ?>" class="btn btn-primary">
											<?php echo $sliderNinja_Params['btn3']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn3'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink3']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg3']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg3']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif;

		// Slide 4

		if ($sliderNinja_Params['enableitem4'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg4']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title4']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description4']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn4'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink4']; ?>" class="btn btn-primary">
											<?php echo $sliderNinja_Params['btn4']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn4'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink4']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg4']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg4']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif;

		// Slide 5

		if ($sliderNinja_Params['enableitem5'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg5']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title5']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description5']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn5'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink5']; ?>" class="btn btn-primary">
											<?php echo $sliderNinja_Params['btn5']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn5'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink5']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg5']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg5']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif;

		// Slide 6

		if ($sliderNinja_Params['enableitem6'] == "1" ) : ?>
			<div class="item">
				<?php if ($sliderNinja_Params['enablecaption'] == "1" ) { ?>
					<div class="slideimg" style="background-image: url('<?php echo $sliderNinja_Params['slideimg6']; ?>');"></div>
					<div class="slidecaption">
						<div class="slidercaptioninner">
							<div class="slidercaptiontext">
								<h2 class="slidetitle"><?php echo $sliderNinja_Params['title6']; ?></h2>
								<div class="slidedescription"><?php echo $sliderNinja_Params['description6']; ?></div>

								<?php if ($sliderNinja_Params['enablebtn6'] == "1" ) : ?>
									<div class="slidebtn">
										<a href="<?php echo $sliderNinja_Params['btnlink6']; ?>" class="btn btn-primary">
											<?php echo $sliderNinja_Params['btn6']; ?></a>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php } else{ ?>
					<div class="imgslide">
						<?php if ($sliderNinja_Params['enablebtn6'] == "1" ) { ?>
							<a href="<?php echo $sliderNinja_Params['btnlink6']; ?>">
								<img src="<?php echo $sliderNinja_Params['slideimg6']; ?>" alt="Slide 1" />
							</a>
						<?php } else { ?>
							<img src="<?php echo $sliderNinja_Params['slideimg6']; ?>" alt="Slide 1" />
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php endif; ?>


	</div>
</div>


<?php
if ($sliderNinja_Params['navarrows'] == "1"){
	$navarrow = 'true';
}else{
	$navarrow = 'false';
}

if ($sliderNinja_Params['dotnav'] == "1"){
	$dotnav = 'true';
}else{
	$dotnav = 'false';
}

if ($sliderNinja_Params['autoplay'] == "1"){
	$autoplay = 'true';
}else{
	$autoplay = 'false';
}


?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		var owl = $('#sliderninja_container<?php echo $module->id ?>');
		owl.owlCarousel({
			items:1,
			nav : <?php echo $navarrow; ?>,
			navText : [
				"<i class='fa fa-angle-left'></i>",
				"<i class='fa fa-angle-right'></i>"
			],
			rewind:true,
			dots: <?php echo $dotnav; ?>,
			animateOut: 'fadeOut',
			autoplay: <?php echo $autoplay; ?>,
			autoplayTimeout: <?php echo $sliderNinja_Params['autoplayspeed']; ?>,
			autoplayHoverPause:true
		});

	});
</script>
<?php
JHtml::_('script', 'mod_sliderninja/owl.carousel.js', null, true);
?>
