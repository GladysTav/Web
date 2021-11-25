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

/** @var SellaciousViewProduct $this */
?>

<div class="specification-box sell-infobox">

    <div class="innerspecinfo">
        <?php
        $specs          = array();
        $specifications = $this->item->get('specifications');

        // Rearrange group wise
        foreach ($specifications as $field)
        {
            if (!isset($specs[$field->parent_id]))
            {
                $specs[$field->parent_id] = array(
                    'group_id'    => $field->parent_id,
                    'group_title' => $field->group_title,
                    'fields'      => array(),
                );
            }

            $specs[$field->parent_id]['fields'][$field->id] = $field;
        }

        foreach ($specs as $group)
        {
            ?>
            <div class="specificationgroup">
                <dl class="dl-horizontal dl-leftside">
                    <?php
                    foreach ($group['fields'] as $field)
                    {
                        ?>
                        <dt><?php echo $this->escape($field->title) ?></dt>
                        <dd><?php echo $this->helper->field->renderValue($field->value, $field->type, $field) ?></dd>
                        <?php
                    }
                    ?>
                </dl>
            </div>
            <?php
        }
        ?>
    </div>
</div>

