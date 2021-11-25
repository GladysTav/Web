<div :class="[params.widthClasses]" class="product-list-block product-block-minimal">
	<div class="product-block-wrap" :data-code="product.code">
		<div class="product-images-container">
			<div class="image-box">
				<a :href="product.url" :class="params.product_detail_page === '0' ? 'not-clickable' : ''">
					<span class="bgrollover" :style="productImageCss" :data-rollover="JSON.stringify(product.images)"></span>
				</a>
			</div>
			<img v-if="product.spl_badge" class="spl-cat-badge" :src="product.spl_badge" alt=""/>
			<div class="spl-badge-text hasTooltip" :title="product.spl_badge_text" v-if="product.spl_badge_text" :style="product.spl_badge_text_style">{{product.spl_badge_text}}</div>
		</div>
		<div class="product-information">
			<div class="product-info">
				<div class="product-title">
					<a :href="product.url" :class="product.url === '#' ? 'not-clickable' : ''">
						{{ product.product_title }}<span class="product-variant-title" v-if="product.variant_title"> - {{ product.variant_title }}</span>
					</a>
				</div>

				<template v-if="mod_params.displayproductprice == '1' && params.show_price" >
					<template v-if="params.login_to_see_price == '1' && user.guest">
						<a :href="params.login_url">{{ 'COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW' | t }}</a>
					</template>
					<div v-else class="product-price-container ctech-text-center">
						<component :is="`product-price-${product.pricing_type}`" :product="product" :params="params"></component>
					</div>
				</template>
			</div>
		</div>
		<div class="wishlist-remove-product" v-if="params.context.includes('wishlist')">
			<a @click="removeFromWishlist" href="#"><i class="fas fa-trash-alt ctech-text-danger"></i></a>
		</div>
	</div>
</div>
