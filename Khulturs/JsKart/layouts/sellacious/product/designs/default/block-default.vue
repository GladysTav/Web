<div :class="[params.widthClasses]" class="product-list-block product-block-default">
	<div class="product-block-wrap" :class="product.css_class" :data-code="product.code">
		<div class="product-images-container" data-rollover="container">
			<div class="image-box">
				<a :href="product.url" :class="params.product_detail_page === '0' ? 'not-clickable' : ''">
					<span class="bgrollover" :style="productImageCss" :data-rollover="JSON.stringify(product.images)"></span>
				</a>
			</div>
			<div class="image-thumbs" v-if="params.show_thumbs == '1'">
				<div v-if="product.images.length > 1" class="image-thumb" v-for="(image, i) in product.images">
					<span :style="productImageThumbsCss[i]" @mouseover="changeImage(product.images[i])"></span>
				</div>
			</div>
			<div class="product-stock-info" v-if="params.context.includes('wishlist')">
				<span :class="(product.stock_capacity > 0 || product.disable_stock == 1) ? 'ctech-badge-info' : 'ctech-badge-danger'" class="ctech-badge">{{ product.stock_capacity > 0 ? 'COM_SELLACIOUS_PRODUCTS_IN_STOCK' : 'COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK' | t }}</span>
			</div>
		</div>
		<div class="product-information">
			<div class="product-info">
				<div class="product-title">
					<a :href="product.url" :class="params.product_detail_page === '0' ? 'not-clickable' : ''">{{ product.product_title }}<span
						class="product-variant-title" v-if="product.variant_title"> - {{ product.variant_title }}</span></a>
				</div>
				<img v-if="product.spl_badge" class="spl-cat-badge" :src="product.spl_badge"/>
				<div class="spl-badge-text hasTooltip" :title="product.spl_badge_text" v-if="product.spl_badge_text" :style="product.spl_badge_text_style">{{product.spl_badge_text}}</div>


				<div v-if="!(params.show_zero_rating == '0' && parseInt(product.product_rating.rating) == '0') " >
					<div class="product-rating rating-stars" v-if="params.allow_rating === '1' && mod_params.displayratings === '1' && params.rating_pages.includes('products') && product.hasOwnProperty('product_rating')">
						<i v-for="n in parseInt(product.product_rating.rating)" v-if="product.product_rating.rating > 0"></i>
						<span :class="`star-${Math.floor(product.product_rating.rating * 2)} fa fa-star solid-icon`"></span><span :class="`star-${10 - Math.floor(product.product_rating.rating * 2)} fa fa-star regular-icon`"></span>
					</div>
				</div>

				<template v-if="mod_params.displayproductprice == '1' && params.show_price" >
					<template v-if="params.login_to_see_price == '1' && user.guest">
						<a :href="params.login_url">{{ 'COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW' | t }}</a>
					</template>
					<div v-else class="product-price-container">
						<component :is="`product-price-${product.pricing_type}`" :product="product" :params="params"></component>
					</div>
				</template>

				<component :is="`product-stock-${product.pricing_type}`" :product="product" :params="params" />

				<ul class="product-features" v-if="params.features_pages.includes('products') && mod_params.featurelist == 1 && product.hasOwnProperty('product_features') && product.product_features.length > 0">
					<li v-for="feature in product.product_features"> {{ feature }}</li>
				</ul>
			<template v-if="params.cart_pages.includes('products') || params.buynow_pages.includes('products') || (product.compare_allow && product.compare_pages.includes('products')) || params.show_modal.includes('products') || (product.hasOwnProperty('rendered_attr'))">
				<div class="product-action-buttons">
					<template v-if="product.hasOwnProperty('rendered_attr') && product.rendered_attr.length > 0">
						<a class="ctech-btn ctech-btn-default btn-quick-view hasTooltip" :title="t('COM_SELLACIOUS_PRODUCTS_DELIVERY_SLOTS')" @click="quickView(product, context)">
							<i class="fa fa-calendar"></i>
						</a>
					</template>
					<component v-if="params.login_to_see_price == '0' || (params.login_to_see_price == '1' && !user.guest)" :is="`product-addtocart-${product.pricing_type}`" :product="product" :params="params" :mod_params="mod_params" :user="user" :show_nostock="true" variant="default" :label="true"></component>
					<component v-if="params.login_to_see_price == '0' || (params.login_to_see_price == '1' && !user.guest)" :is="`product-buynow-${product.pricing_type}`" :product="product" :params="params" :mod_params="mod_params" :user="user" :show_nostock="false" variant="default" :label="false"></component>
					<label class="product-compare ctech-btn ctech-btn-default hasTooltip" :title="t('COM_SELLACIOUS_PRODUCTS_COMPARE')" v-if="product.compare_allow && params.compare_pages.includes('products') && mod_params.displaycomparebtn == '1'">
						<i class="fa fa-balance-scale"></i>
						<input type="checkbox" class="btn-compare" :data-item="product.code"/>
					</label>
					<a class="ctech-btn ctech-btn-default btn-quick-view hasTooltip" :title="t('COM_SELLACIOUS_PRODUCTS_QUICK_VIEW')" @click="quickView(product, context)"
					   v-if="params.show_modal.includes('products') && mod_params.displayquickviewbtn == '1'">
						<i class="fa fa-eye"></i>
					</a>
				</div>
			</template>
		</div>

		<div class="clearfix"></div>
		<div class="wishlist-remove-product" v-if="params.context.includes('wishlist')">
			<a @click="removeFromWishlist" href="#"><i class="fas fa-trash-alt ctech-text-danger"></i></a>
		</div>
	</div>
</div>
