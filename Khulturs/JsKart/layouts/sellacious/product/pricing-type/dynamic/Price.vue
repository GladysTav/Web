<div>
	<del v-if="params.price_display == 2 && parseFloat(product.list_price).toFixed(2) >= 0.01" class="old-price">{{ product.display_list_price }}</del>
	<span class="product-price ctech-text-primary">{{ product.display_price }}</span>
</div>
