
<?php
defined('_JEXEC') or die;
// Licensed under the GPL v3
//echo "<pre>";
//print_r($profiles);
?>
<style>
.card-img-overlayteam-member-name{
  color:<?php echo  $params->get('NameColor'); ?>;
}
.team-member-designation{
  color:<?php echo  $params->get('designationColor'); ?>;
}
.card-img-overlayteam-member-bio{
  color:<?php echo  $params->get('shortBio'); ?>;
}
<?php if($params->get('social_icons')=="c") { ?>
.jd-team-showcase-wrapper .social-profile li a {
  color: <?php echo $params->get('icon_color');?>;
  border: 1px solid <?php echo $params->get('icon_background');?>;
  background-color: <?php echo $params->get('icon_background');?>;
}
<?php } ?>
</style>
  <section>
    <div class="container py-5">
      <div class="jd-team-showcase-wrapper jd-grid-layout-view jd-grid-circle-layout">
        <div class="row">
          <div class="col-12">
            <div class="row <?php echo ($params->get('gutter_space')=='nomargin') ? 'no-gutters' : '' ?>">
              <!-- Team Item wrapper start -->
              <?php foreach($profiles as $profile) { ?>
                  <div class="jd-team-columns col-12 col-md-6 col-lg-<?php echo  $params->get('grid_coloumns'); ?>" <?php if($params->get('gutter_space')=='custom') { ?> style="padding-right:<?php echo $params->get('margin');?>px; padding-left:<?php echo $params->get('margin');?>px;" <?php } ?>>
                    <div class="card-team jd-team-items">
                      <div class="team-mamber-image-wrapper">
                        <img src="<?php echo $profile->image;  ?>" alt="" class="card-img-top team-mamber-image">
                      </div>
                      <?php if($params->get('display_name') or $params->get('display_designation') ) { ?>
                        <div class="card-team-body">
                          <div class="team-member-content-wrapper">
                            <?php if($params->get('display_name')) { ?>
                              <h5 class="card-img-overlayteam-member-name">
                                <?php if($params->get('enable_link')){ ?>
                                  <a href="<?php echo JRoute::_('index.php?option=com_jdprofiles&view=profile&id='.(int) $profile->id); ?>"><?php  echo $profile->name; ?></a>
                                <?php }else {?>
                                  <?php  echo $profile->name; ?>
                                <?php  } ?>
                              </h5>
                            <?php } ?>
                            <?php if($params->get('display_designation')) { ?>
                              <?php if(!empty($profile->designation)) { ?>  
                                <p class="team-member-designation">
                                  <small><?php echo $profile->designation;  ?></small>
                                </p>
                              <?php } ?>
                            <?php } ?>
                            <?php if(!empty($profile->sbio)) { ?>
                              <?php if($params->get('display_sbio')) { ?>
                                <p class="card-img-overlayteam-member-bio"><?php echo $profile->sbio;  ?></p>
                              <?php } ?>
                            <?php } ?>
                          </div>
                        </div>
                      <?php }?>
                      <?php if($params->get('show_socialsIcon')) { ?>
                        <?php if(!empty($profile->social)) { ?>
                            <div class="card-team-footer social-profile-wrapper">
                              <ul class="social-profile <?php echo $params->get('IconStyle'); ?>">
                                <?php  $socials=  json_decode($profile->social);?>
                                  <?php  foreach($socials as $social){?>
                                      <li>
                                          <a href="<?php echo $social->link?>" target="<?php if($params->get('new_tab')){echo '1'; } ?>" >
                                            <i class="<?php echo $social->icon?>"></i>
                                          </a>
                                      </li>
                                  <?php } ?>
                              </ul>
                            </div>
                          <?php } ?> 
                        <?php } ?>
                    </div>
                  </div>
              <?php  } ?>
              <!-- Team Item wrapper end -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
