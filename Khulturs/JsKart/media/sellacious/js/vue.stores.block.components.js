/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Versha Verma <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */

const storesBlocksMixins = {
	props: ['store', 'params', 'mod_params', 'user', 'context'],
	methods: {
		removeFromFavorites: function (e) {
			e.preventDefault();

			var paths = Joomla.getOptions('system.paths', {});
			var baseUrl = (paths.base || paths.root || '');
			var token = Joomla.getOptions('csrf.token');

			const data = new FormData;
			data.append('seller_uid', this.store.user_id)
			data.append(token, 1)

			fetch(`${baseUrl}/index.php?option=com_sellacious&task=stores.removeFavoriteStoreAjax`, {
				method: 'post',
				body: data,
				cache: 'no-cache',
				redirect: 'follow',
				referrer: 'no-referrer'
			})
				.then(response => response.json())
				.then(response => {
					if (response.success) {
						jQuery(`[data-store="store-${this.store.user_id}"]`).fadeOut('fast', function () {
							jQuery(this).remove()
							if (jQuery('.store-blocks-container').find('.store-list-block').length === 0) {
								jQuery('.no-stores-found').removeClass('ctech-d-none');
							}
						})
					}
				})
		}
	}
}

Vue.component('stores-block-default', {
	template: '#vue-stores-block-default',
	mixins: [storesBlocksMixins]
});
