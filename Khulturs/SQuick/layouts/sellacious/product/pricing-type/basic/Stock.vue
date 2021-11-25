<div class="product-stock" v-if="params.display_stock_product_list === 1 && product.stock_capacity > '0'">
	<div v-if="product.disable_stock == '0' "><i class="fa fa-check-circle"></i> {{ product.stock_capacity }} {{'COM_SELLACIOUS_PRODUCT_STOCK_IN_CART' | t }}
	</div>
	<span v-else > <i class="fa fa-check-circle"></i> {{'COM_SELLACIOUS_PRODUCT_STOCK_IN_CART' | t }}</span>
</div>
