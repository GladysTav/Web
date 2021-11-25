<div class="category-list-block category-block-textimage" :class="params.widthClasses">
	<a :href="category.url" class="category-block-wrapper">
		<div class="category-image" :style="{backgroundImage: `url('${category.images[0]}')`, backgroundSize: params.category_image_size_adjust}"></div>
		<div class="category-info">
			<h6 class="category-title ctech-text-dark">{{ category.title }}</h6>
			<div class="category-products" v-if="params.show_category_product_count == '1' && Number(category.product_count) > 0">
				<span class="category-bottom-value ctech-text-secondary">{{ category.product_count }} {{ 'COM_SELLACIOUS_CATEGORY_PRODUCTS' | t }}</span>
			</div>
			<div class="category-subcategories" v-if="params.show_subcat_count == '1' && Number(category.subcat_count) > 0">
				<span class="category-bottom-value ctech-text-secondary">{{ category.subcat_count }} {{ 'COM_SELLACIOUS_CATEGORY_CATEGORIES' | t }}</span>
			</div>
		</div>
	</a>
</div>
