<div class="category-list-block category-block-icontext" :class="params.widthClasses">
	<a class="category-block-wrapper" :href="category.url">
		<div class="category-icon">
			<span :style="{backgroundImage: `url('${category.icon}')`, backgroundSize: params.category_image_size_adjust}"></span>
		</div>
		<div class="category-info">
			<div class="category-title ctech-text-dark">{{ category.title }}</div>
			<div class="category-products" v-if="params.show_category_product_count == '1' && Number(category.product_count) > 0">
				<span class="category-bottom-value ctech-text-secondary">{{ category.product_count }} {{ 'COM_SELLACIOUS_CATEGORY_PRODUCTS' | t }}</span>
			</div>
			<div class="category-subcategories" v-if="params.show_subcat_count == '1' && Number(category.subcat_count) > 0">
				<span class="category-bottom-value ctech-text-secondary">{{ category.subcat_count }} {{ 'COM_SELLACIOUS_CATEGORY_CATEGORIES' | t }}</span>
			</div>
		</div>
	</a>
</div>
