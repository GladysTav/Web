<div class="cart-buttons-list" v-if="!(params.login_to_see_price == 1 && user.guest == 1) && params.disable_checkout == 0">
	<template v-if="product.check_stock || product.disable_stock == 1">
		<button v-if="params.cart_pages.indexOf('products') >= 0 && mod_params.displayaddtocartbtn === '1'" type="button" class="ctech-btn btn-add-cart add hasTooltip" :class="variant != undefined ? `ctech-btn-${variant}` : `ctech-btn-primary`" :data-item="product.code" data-placement="top"
			:title="t('COM_SELLACIOUS_PRODUCTS_ADD_TO_CART')"	@click="addToCart()">
			<i class="fa fa-shopping-cart"></i> <span class="product-button-text" v-if="label !== false">{{ 'COM_SELLACIOUS_PRODUCTS_ADD_TO_CART' | t }}</span>
		</button>
	</template>
	<template v-else>
		<button v-if="show_nostock !== false && (!params.context.includes('wishlist'))" class="ctech-btn lbl-no-stock ctech-btn-default hasTooltip" disabled :title="t('COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK')">
			<i class="fa fa-times"></i> <span>{{ 'COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK' | t }}</span>
		</button>
	</template>
</div>
