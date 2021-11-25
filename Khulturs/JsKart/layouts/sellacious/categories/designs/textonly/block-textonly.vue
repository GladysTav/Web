<div class="category-list-block category-block-textonly" :class="params.widthClasses">
	<a class="category-block-wrapper" :href="category.url">
		<div class="category-info">
			<span class="category-title ctech-text-dark">{{ category.title }}</span>
			<span class="category-products ctech-text-secondary" v-if="params.show_category_product_count == '1' && Number(category.product_count) > 0">({{ category.product_count }} {{ 'COM_SELLACIOUS_CATEGORY_PRODUCTS' | t }})</span>
		</div>
		<span class="category-subcategories ctech-text-secondary" v-if="params.show_subcat_count == '1' && Number(category.subcat_count) > 0">{{ category.subcat_count }} {{ 'COM_SELLACIOUS_CATEGORY_CATEGORIES' | t }}</span>
	</a>
</div>
