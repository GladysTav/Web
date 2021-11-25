<?php
/**
 * @package   JD Simple Contact Form
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// no direct access
defined('_JEXEC') or die;
$title = $params->get('title', '');
$description = $params->get('description', '');
$session = JFactory::getSession();
$message = $session->get('jdscf-message-' . $module->id, '');
?>
<?php
if (!empty($message)) {
   echo '<div class="jd-simple-contact-form">' . $message . '</div>';
   $session->set('jdscf-message-' . $module->id, '');
} else {
   ?>
   <div class="jd-simple-contact-form">
      <div id="jdscf-message-<?php echo $module->id; ?>"></div>
      <div class="simple-contact-form-loader module-<?php echo $module->id; ?> d-none">
         <div class="loading"></div>
      </div>
      <?php if (!empty($title)) { ?>
         <h5 class="card-title"><?php echo JText::_($title); ?></h5>
      <?php } ?>
      <?php if (!empty($description)) { ?>
         <p class="card-subtitle mb-2 text-muted"><?php echo JText::_($description); ?></p>
      <?php } ?>
      <form method="POST" action="<?php echo JURI::root(); ?>index.php?option=com_ajax&module=jdsimplecontactform&format=json&method=submit" data-parsley-validate data-parsley-errors-wrapper="<ul class='text-danger list-unstyled mt-2 small'></ul>" data-parsley-error-class="border-danger" data-parsley-success-class="border-success" id="simple-contact-form-<?php echo $module->id; ?>">
         <div class="row">
            <?php
            ModJDSimpleContactFormHelper::renderForm($params);
            ?>
         </div>
         <div class="row">
            <?php
            $submit = new JLayoutFile('fields.submit', JPATH_SITE . '/modules/mod_jdsimplecontactform/layouts');
            echo $submit->render(['params' => $params]);
            ?>
         </div>
         <input type="hidden" name="returnurl" value="<?php echo urlencode(JUri::getInstance()); ?>"/>
         <input type="hidden" name="id" value="<?php echo $module->id; ?>" />
         <?php echo JHtml::_('form.token'); ?>
      </form>
   </div>
   <?php if ($params->get('ajaxsubmit', 0)) { ?>
      <script>
         (function ($) {
            $(function () {
               var showMessage = function (type, message) {
                  type = type == 'error' ? 'danger' : type;
                  var _alert = '<div class="alert alert-dismissible alert-' + type + '"><div>' + message + '</div><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                  $('#jdscf-message-<?php echo $module->id; ?>').html(_alert);
               }
               $('#simple-contact-form-<?php echo $module->id; ?>').on('submit', function (e) {
                  e.preventDefault();
                  var _form = $(this);
                  var _id = 'simple-contact-form-<?php echo $module->id; ?>';
                  var _loading = $('.simple-contact-form-loader.module-<?php echo $module->id; ?>');
                  if (_form.parsley().isValid()) {
                     $.ajax({
                        url: '<?php echo JURI::root(); ?>index.php?option=com_ajax&module=jdsimplecontactform&format=json&method=submitForm',
                        data: $(this).serialize(),
                        type: 'POST',
                        beforeSend: function () {
                           _loading.removeClass('d-none');
                        },
                        success: function (response) {

                           if (response.status == 'success') {
                              $('.jd-simple-contact-form').html(response.data.message);
                              if (response.data.redirect != '') {
                                 setTimeout(function () {
                                    window.location = response.data.redirect;
                                 }, 2000);
                              } else {
                                 _loading.addClass('d-none');
                                 document.getElementById(_id).reset();
                                 _form.parsley().reset();
                              }
                           } else {
                              _loading.addClass('d-none');
                              showMessage("error", response.data.message);
                           }
                        },
                        error: function (response) {
                           _loading.addClass('d-none');
                           showMessage("error", response.data.message);
                        }
                     });
                  }
               });
            });
         })(jQuery);
      </script>
   <?php } ?>
   <?php
}?>