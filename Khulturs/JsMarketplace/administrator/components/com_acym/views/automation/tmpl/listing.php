<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
    <div id="acym__automation" class="acym__content">
        <?php echo acym_teasing('<i class="material-icons acy_coming_soon_icon">new_releases</i>'.acym_translation('ACYM_COMING_SOON')) ?>
        <?php if (true) { ?>
            <div class="grid-x text-center">
                <h1 class="cell acym__listing__empty__title"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_AUTOMATION') ?></h1>
                <h1 class="cell acym__listing__empty__subtitle"><?php echo acym_translation('ACYM_CREATE_ONE_AND_LET_ACYAMAILING_DO_IT') ?></h1>
                <div class="medium-4"></div>
                <div class="medium-4">
                    <button type="button" class="button expanded" id="acym__automation__empty__button"><?php echo acym_translation('ACYM_CREATE_AUTOMATION'); ?></button>
                </div>
                <div class="medium-4"></div>
            </div>
        <?php } else { ?>
            <div class="grid-x grid-margin-x">
                <div class="medium-auto cell">
                    <?php echo acym_filterSearch($data["search"], 'automation_search', 'ACYM_SEARCH_AUTOMATION'); ?>
                </div>
                <div class="medium-auto cell">
                    <?php
                    $allTags = new stdClass();
                    $allTags->name = acym_translation('ACYM_ALL_TAGS');
                    $allTags->value = '';
                    array_unshift($data["allTags"], $allTags);

                    echo acym_select($data["allTags"], 'automation_tag', $data["tag"], 'class="acym__automations__filter__tags"', 'value', 'name');
                    ?>
                </div>
                <div class="xxlarge-4 xlarge-3 large-2 hide-for-medium-only hide-for-small-only cell"></div>
                <div class="medium-shrink cell">
                    <button type="button" class="button expanded"><?php echo acym_translation('ACYM_CREATE_AUTOMATION'); ?></button>
                </div>
                <div class="cell grid-x">
                    <div class="cell">
                        <?php echo acym_sortBy(
                            array(
                                'name' => acym_translation('ACYM_NAME'),
                            ),
                            'automation'
                        ) ?>
                    </div>
                </div>
            </div>


            <?php

            ?>
            <div class="grid-x acym__listing">
                <div class="grid-x cell acym__listing__header">
                    <div class="medium-shrink small-1 cell">
                        <input id="checkbox_all" type="checkbox" name="checkbox_all">
                    </div>
                    <div class="grid-x medium-auto small-11 cell">
                        <div class="medium-4 cell acym__listing__header__title">
                            <?php echo acym_translation('ACYM_AUTOMATION'); ?>
                        </div>
                        <div class="medium-auto cell text-center hide-for-small-only acym__listing__header__title">
                            <?php echo acym_translation('ACYM_ACTIVE'); ?>
                        </div>
                    </div>
                </div>
                <?php foreach ($data["allAutomations"] as $automation) { ?>
                    <div class="grid-x cell acym__listing__row">
                        <div class="medium-shrink small-1 cell">
                            <input id="checkbox_<?php echo $automation->id; ?>" type="checkbox">
                        </div>
                        <div class="grid-x medium-auto small-11 acym__automation__listing">
                            <div class="medium-4 small-8 cell acym__listing__title acym__automation__title">
                                <?php echo "<h6>".$automation->name."</h6>"; ?>
                            </div>
                            <div class="grid-x medium-auto small-4 text-center cell">
                                <?php echo acym_switch("", 0); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php echo $data['pagination']->display('automation'); ?>
        <?php } ?>
    </div>
    <?php echo acym_formOptions(true) ?>
</form>
