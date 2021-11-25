<div>
	<button type="button" class="ctech-btn ctech-rounded-pill ctech-btn-sm" :class="showSellerDetails ? 'ctech-btn-default' : 'ctech-btn-primary'" @click="showSellerDetails = !showSellerDetails">
		<template v-if="!showSellerDetails">
			{{ 'COM_SELLACIOUS_PRODUCT_PRICING_TYPE_EMAIL_EMAIL_US' | t }}
		</template>
		<template v-else>
			<template v-if="params.security == '1'">
				<img :src="'data:image/png;base64,'+ product.b64text_email"/>
			</template>
			<template v-else>
				{{ product.seller_email }}
			</template>
		</template>
	</button>
</div>
