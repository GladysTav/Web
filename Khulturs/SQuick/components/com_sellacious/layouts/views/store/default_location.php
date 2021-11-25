<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Hyperlocal\Settings;

defined('_JEXEC') or die;
/** @var  SellaciousViewProducts  $this */

$storeId   = $this->state->get('store.id');
$me        = JFactory::getUser();
$store     = JFactory::getUser($storeId);
$seller    = $this->helper->seller->getItem(array('user_id' => $storeId));
$seller    = new Joomla\Registry\Registry($seller);

if (!$seller->get('store_location')) return;

$coords     = explode(',', $seller->get('store_location'));
$hyperlocal = Settings::getInstance();

if (!$hyperlocal->isEnabled()) return;

$api_key = $hyperlocal->getApiKey();

if (!$api_key) return;
?>
<a class="hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_STORE_LOCATION') ?>"href="#" id="open-store-location" >
	<i class="fa fa-map-marker ctech-text-dark"></i>
</a>

<div class="store-location-content">
    <div class="store-location-wrapper">
        <a href="#" id="close-store-location">
            <i class="fa fa-times"></i>
        </a>
        <div id="store-location" data-coords="<?php echo htmlspecialchars(json_encode($coords)); ?>"></div>
    </div>
    <div class="store-location-backdrop"></div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $api_key; ?>" async defer></script>
