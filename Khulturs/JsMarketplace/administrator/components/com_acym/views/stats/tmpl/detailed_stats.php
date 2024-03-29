<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__content acym__stats" id="acym_stats_detailed">
    <?php if (!empty($data['emptyDetailed']) && $data['emptyDetailed'] == 'campaigns') { ?>
		<h1 class="acym__listing__empty__title text-center cell"><?php echo acym_translation('ACYM_DONT_HAVE_STATS_CAMPAIGN'); ?>. <a href="<?php echo acym_completeLink('campaigns&task=edit&step=chooseTemplate') ?>"><?php echo acym_translation('ACYM_CREATE_ONE') ?>!</a></h1>
    <?php } else if (!empty($data['emptyDetailed']) && $data['emptyDetailed'] == 'stats') { ?>
		<h1 class="acym__listing__empty__title text-center cell"><?php echo acym_translation('ACYM_DONT_HAVE_STATS_THIS_CAMPAIGN'); ?></a></h1>
    <?php } else { ?>
		<div class="cell grid-x">
			<div class="large-3 medium-4 small-12 cell acym_stats_detailed_search">
                <?php echo acym_filterSearch($data["search"], 'detailed_stats_search', 'ACYM_SEARCH_A_CAMPAIGN_NAME_OR_EMAIL'); ?>
			</div>
			<div class="large-3 medium-4 small-12 cell acym__stats__campaign-choose">
			</div>
		</div>
		<div class="grid-x">
			<div class="cell">
                <?php echo acym_sortBy(
                    array(
                        'send_date' => acym_translation('ACYM_SEND_DATE'),
                        'subject' => acym_translation('ACYM_NAME'),
                        'email' => acym_translation('ACYM_EMAIL'),
                        'open' => acym_translation('ACYM_MAILS_OPEN'),
                        'open_date' => acym_translation('ACYM_OPEN_DATE'),
                        'sent' => acym_translation('ACYM_SENT'),
                    ),
                    "detailed_stats"
                ) ?>
			</div>
		</div>
		<div class="grid-x acym__listing cell">
			<div class="grid-x cell acym__listing__header">
				<div class="grid-x medium-auto small-11 cell">
					<div class="large-2 medium-3 small-3 cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_SEND_DATE'); ?>
					</div>
					<div class="large-3 medium-3 small-3 cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_NAME'); ?>
					</div>
					<div class="large-3 medium-4 small-4 cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_USER'); ?>
					</div>
					<div class="large-1 medium-1 small-1 cell acym__listing__header__title text-center">
                        <?php echo acym_translation('ACYM_OPENED'); ?>
					</div>
					<div class="large-2 hide-for-small-only hide-for-medium-only cell acym__listing__header__title text-center">
                        <?php echo acym_translation('ACYM_OPEN_DATE'); ?>
					</div>
					<div class="large-1 medium-1 small-1 cell acym__listing__header__title text-center">
                        <?php echo acym_translation('ACYM_SENT'); ?>
					</div>
				</div>
			</div>
            <?php
            foreach ($data['detailed_stats'] as $detailed_stat) { ?>
				<div class="grid-x cell acym__listing__row">
					<div class="grid-x medium-auto small-11 cell">
						<div class="large-2 medium-3 small-3 cell acym__listing__detailed__stats__content">
                            <?php
                            echo acym_tooltip('<p>'.acym_date(acym_getTime($detailed_stat->send_date), 'd F H:i').'</p>', acym_date(acym_getTime($detailed_stat->send_date), 'd F Y H:i:s'));
                            ?>
						</div>
						<div class="large-3 medium-3 small-3 cell acym__listing__detailed__stats__content">
                            <?php
                            echo acym_tooltip('<a href="'.acym_completeLink('campaigns&task=edit&step=editEmail&id='.$detailed_stat->campaign_id).'" class="word-break acym__color__blue">'.$detailed_stat->name.'</a>', acym_translation('ACYM_SUBJECT').' : '.$detailed_stat->subject);
                            ?>
						</div>
						<div class="large-3 medium-4 small-4 cell acym__listing__detailed__stats__content">
							<a href="<?php echo acym_completeLink('users&task=edit&id='.$detailed_stat->user_id) ?>" class="acym__color__blue word-break"><?php echo $detailed_stat->email ?></a>
						</div>
						<div class="large-1 medium-1 small-1 cell acym__listing__detailed__stats__content text-center">
							<p class="hide-for-medium-only hide-for-small-only"><?php echo $detailed_stat->open ?></p>
						</div>
						<div class="large-2 hide-for-small-only hide-for-medium-only cell acym__listing__detailed__stats__content text-center">
                            <?php
                            echo empty($detailed_stat->open_date) ? '' : acym_tooltip('<p>'.acym_date(acym_getTime($detailed_stat->open_date), 'd F H:i').'</p>', acym_date(acym_getTime($detailed_stat->open_date), 'd F Y H:i:s')) ?>
						</div>
						<div class="large-1 medium-1  small-1 cell acym__listing__detailed__stats__content text-center cursor-default">
                            <?php
                            $targetSuccess = '<i class="material-icons acym__listing__detailed_stats_sent__success" >check_circle</i>';
                            $targetFail = '<i class="material-icons acym__listing__detailed_stats_sent__fail" >error</i>';
                            echo acym_tooltip(empty($detailed_stat->fail) ? $targetSuccess : $targetFail, acym_translation('ACYM_SENT').' : '.$detailed_stat->sent.' '.acym_translation('ACYM_FAIL').' : '.$detailed_stat->fail);
                            ?>
						</div>
					</div>
				</div>
                <?php
            }
            ?>
		</div>
        <?php
        echo $data['pagination']->display('detailed_stats');
    } ?>
</div>
<?php echo acym_formOptions(true); ?>
