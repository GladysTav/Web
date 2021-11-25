/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */

Vue.filter('t', str => Joomla.JText._(str, str));

Vue.component('category-block-default', {
	template: '#vue-category-block-default',
	props: ['category', 'params', 'user']
})

Vue.component('category-block-textimage', {
	template: '#vue-category-block-textimage',
	props: ['category', 'params', 'user']
})

Vue.component('category-block-textonly', {
	template: '#vue-category-block-textonly',
	props: ['category', 'params', 'user']
})

Vue.component('category-block-icontext', {
	template: '#vue-category-block-icontext',
	props: ['category', 'params', 'user']
})

Vue.component('category-block-elegant', {
	template: '#vue-category-block-elegant',
	props: ['category', 'params', 'user']
})

Vue.component('category-block-list', {
	template: '#vue-category-block-list',
	props: ['category', 'params', 'user']
})

const initCategoryBlock = (el, context, categories, params, user) => new Vue({
	el,
	data() {
		return {
			categories,
			user,
			params,
			blockStyle: params.blockStyle,
			context
		}
	},
	mounted() {

	}
})

jQuery(document).ready(function ($) {
	const user = Joomla.getOptions('sellacious.user');

	$('.category-blocks-container').each((i, block) => {
		const id = $(block).data('id');
		const categories = Joomla.getOptions(`sellacious.category.data.module-${id}`);
		const params     = Joomla.getOptions(`sellacious.category.params.module-${id}`);

		initCategoryBlock(block, id, categories, params, user);
	})
});
