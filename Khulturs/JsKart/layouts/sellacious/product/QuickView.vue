<div class="ctech-modal modal-quick-view" role="dialog" :id="attributes.id">
	<div class="ctech-modal-dialog" :class="attributes.size === 'lg' ? 'ctech-modal-lg' : attributes.size === 'sm' ? 'ctech-modal-sm' : ''" role="document">
		<div class="ctech-modal-content">
			<div class="ctech-modal-body">
				<button type="button" class="ctech-close" data-dismiss="ctech-modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="product-quick-view">
					<div class="quick-image-container">
						<div class="owl-carousel quick-carousel owl-theme">
							<div v-for="(image, i) in product.orImages" :key="i">
								<img :src="image" :alt="product.product_title">
							</div>
						</div>
					</div>
					<div class="quick-info-container">
						<h1 class="quick-product-title">{{ product.product_title }}</h1>
						<div class="quick-divider"></div>
						<div class="quick-price-container">
							<template v-if="params.login_to_see_price == '1' && user.guest">
								<a :href="product.login_url">{{ 'COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW' | t }}</a>
							</template>
							<div v-else class="product-price-container">
								<component v-if="params.price_d_pages.indexOf('product_modal') >= 0" :is="`product-price-${product.pricing_type}`" :product="product" :params="params"></component>
							</div>
						</div>

						<div v-if="!(params.show_zero_rating == '0' && product.hasOwnProperty('product_rating') && parseInt(product.product_rating.rating) == '0')" >
							<div class="quick-rating-container rating-stars"
								 v-if="params.allow_rating === '1' && mod_params.displayratings === '1' && params.rating_pages.includes('product_modal') && product.hasOwnProperty('product_rating')">
								<i v-for="n in parseInt(product.product_rating.rating)" v-if="product.product_rating.rating > 0"></i>
								<span :class="`star-${product.product_rating.rating * 2} fa fa-star solid-icon`"></span><span :class="`star-${10 - (product.product_rating.rating * 2)} fa fa-star regular-icon`"></span>
							</div>
						</div>

						<div class="quick-intro-container">
							<p class="quick-introtext">{{ product.product_introtext }}</p>
						</div>
						<ul class="product-features" v-if="params.features_pages.includes('product_modal') && mod_params.featurelist == 1 && product.hasOwnProperty('product_features') && product.product_features.length > 0">
							<li v-for="feature in product.product_features"><i class="fa fa-angle-right"></i> {{ feature }}</li>
						</ul>
						<div class="quick-cart-container">
							<div class="quick-quantity-container ctech-float-left">
								<label for="product-quantity">{{ 'COM_SELLACIOUS_PRODUCTS_QUANTITY' | t }}</label>
								<input class="ctech-text-primary ctech-rounded-0" type="number" name="quantity" v-model="quantity" id="product-quantity" min="1" value="1"/>
							</div>
							<component :is="`product-addtocart-${product.pricing_type}`" v-if="params.login_to_see_price == '0' || (params.login_to_see_price == '1' && !user.guest)" :product="product" :params="params" :mod_params="mod_params" :user="user" variant="success" :show_nostock="true" class="ctech-float-right"></component>
							<div class="ctech-clearfix"></div>
						</div>
						<div class="quick-other-info ctech-mt-3">
							<div class="quick-product-categories ctech-float-left" v-if="product.category_ext">
								<label>{{ 'COM_SELLACIOUS_PRODUCTS_CATEGORIES' | t }}</label> <span v-for="(cat, i) in product.category_ext" v-if="i !== '1'">{{ cat }}</span>
							</div>
							<a v-if="params.product_detail_page === '1'" class="product-details-link ctech-float-right" :href="product.url"><span>{{ 'COM_SELLACIOUS_PRODUCTS_GO_TO_PRODUCT' | t }}</span></a>
							<div class="ctech-clearfix"></div>
						</div>
				</div>
			</div>
		</div>
	</div>
</div>
