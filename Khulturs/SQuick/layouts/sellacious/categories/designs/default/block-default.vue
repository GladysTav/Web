<div class="category-list-block category-block-default" :class="params.widthClasses">
	<div class="category-block-wrapper">
		<a :href="category.url" class="category-back" :style="{backgroundImage: `url('${category.images[0]}')`, backgroundSize: params.category_image_size_adjust}">
			<h6 class="category-title ctech-text-dark">{{ category.title }}</h6>
			<div class="category-bottom-info ctech-d-flex" v-if="Number(category.product_count) > 0 || Number(category.subcat_count) > 0">
				<div class="category-bottom-left ctech-text-left" v-if="params.show_category_product_count == '1' && Number(category.product_count) > 0">
					<span class="category-bottom-value">{{ category.product_count }}</span>
					{{ 'COM_SELLACIOUS_CATEGORY_PRODUCTS' | t }}
				</div>
				<div class="category-bottom-right ctech-text-right" v-if="params.show_subcat_count == '1' && Number(category.subcat_count) > 0">
					<span class="category-bottom-value">{{ category.subcat_count }}</span>
					{{ 'COM_SELLACIOUS_CATEGORY_CATEGORIES' | t }}
				</div>
			</div>
		</a>
	</div>
</div>
