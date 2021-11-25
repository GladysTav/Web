/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Versha Verma <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */

Vue.filter('t', text => Joomla.JText._(text, text))

const initStoreBlock = (el, stores, params, user, id, context) => new Vue({
	el,
	data() {
		return {
			params: params,
			user: user,
			stores: stores,
			blockStyle: params.blockStyle,
			context
		}
	},
})

jQuery(document).ready(function ($) {
	const user = Joomla.getOptions('sellacious.user');
	$('.store-blocks-container').each((i, block) => {
		const id = $(block).data('module');
		const stores = Joomla.getOptions('sellacious.stores.data.module-' + id);
		const params = Joomla.getOptions('sellacious.stores.params.module-' + id);

		initStoreBlock(block, stores, params, user, id);

	})
})
