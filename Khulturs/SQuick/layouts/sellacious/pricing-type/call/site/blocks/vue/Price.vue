<div>
	<button type="button" class="ctech-btn ctech-btn-primary ctech-rounded-pill" v-if="!showSellerDetails" @click="showSellerDetails = true">
		{{ 'COM_SELLACIOUS_PRODUCT_PRICING_TYPE_CALL_CALL_US' | t }}
	</button>

	<button type="button" class="ctech-btn ctech-btn-default" v-if="showSellerDetails" @click="showSellerDetails = false">
		<template v-if="params.security == '1'">
			<img :src="'data:image/png;base64,'+ product.b64text_mobile"/>
		</template>
		<template v-else>
			{{ product.seller_mobile }}
		</template>
	</button>
</div>
