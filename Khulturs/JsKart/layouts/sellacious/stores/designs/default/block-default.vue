<div :class="[params.widthClasses]" class="store-list-block store-block-default" :data-store="`store-${store.user_id}`">
	<div class="store-wrap">
		<div class="store-box">
			<div class="image-box" :style="{height: `${params.store_logo_image_height}px`}" alt="logo">
				<a :href="store.url">
					<span class="store-image" :style="{backgroundImage: `url('${store.logo}')`, backgroundSize: params.store_logo_image_size}"
				  		alt="logo"></span>
				</a>
			</div>
			<div class="store-info-box">
				<div class="store-title">
					<a :href="store.url">
						{{ store.name }}
					</a>
				</div>
				<div class="product-count" v-if="params.store_product_count == 1 && mod_params.display_product_count == 1">
					<span> {{store.product_count ? store.product_count : '0'}} {{'COM_SELLACIOUS_STORES_PRODUCT' | t }} </span>
				</div>

				<div class="store-rating rating-stars">
					<div v-if="!(params.show_zero_rating == '0' && parseInt(store.rating.rating) === 0) " >
						<div v-if="params.show_store_rating == 1 && params.allow_ratings_for.includes('seller') && mod_params.display_ratings == 1">
							<span :class="`star-${Math.round(store.rating.rating * 2)} fa fa-star solid-icon`"></span><span :class="`star-${10 - Math.round(store.rating.rating * 2)} fa fa-star regular-icon`"></span>
							<span class="ctech-text-primary"> - {{store.rating.rating.toFixed(1)}}</span>
						</div>
					</div>
				</div>
					<div><span>{{store.rating.count ? store.rating.count : 'No' }} {{'COM_SELLACIOUS_STORES_PRODUCT_REVIEWS' | t }}</span></div>
			<div class="remove-from-fav" v-if="params.context.includes('favorite')">
				<a @click="removeFromFavorites" href="#"><i class="fa fa-trash-alt ctech-text-danger"></i></a>
			</div>
			</div>
		</div>
	</div>
</div>
