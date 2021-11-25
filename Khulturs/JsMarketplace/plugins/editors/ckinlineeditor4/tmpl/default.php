<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  PlgEditorCKInlineEditor4  $this */

/** @var  stdClass  $displayData */
$tmpl = $displayData;

$options  = $tmpl->options;
$name     = $tmpl->name;
$id       = $tmpl->id;
$content  = $tmpl->content;

?>
<textarea contenteditable="true" name="<?php echo $name ?>" id="<?php echo $id ?>"
		  data-ckeditor="<?php echo htmlspecialchars(json_encode($options)) ?>"><?php echo $content ?></textarea>
