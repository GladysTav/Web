<div class="category-list-block category-block-elegant" :class="params.widthClasses">
	<div class="category-block-wrapper">
		<a :href="category.url">
			<span class="category-image" :style="{backgroundImage: `url('${category.images[0]}')`, backgroundSize: params.category_image_size_adjust}"></span>
			<div class="category-title">
				<h4 class="ctech-text-dark">{{ category.title }}</h4>
			</div>
			<div class="category-info" v-if="params.show_category_product_count == '1' && Number(category.product_count) > 0">
				<div class="category-info-pill">
					{{ category.product_count }} {{ 'COM_SELLACIOUS_CATEGORY_PRODUCTS' | t }}
				</div>
			</div>
		</a>
	</div>
</div>
