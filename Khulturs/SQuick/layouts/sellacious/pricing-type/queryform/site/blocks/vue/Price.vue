<ctech-modal :id="`query-form-${product.code}`" classes="product-query-form" :url="`index.php?option=com_sellacious&view=product&p=${product.code}&layout=query&tmpl=component`" :footer="false">
	<template v-slot:button>
		<a :href="`#query-form-${product.code}`" role="button" data-toggle="ctech-modal" class="ctech-btn ctech-btn-primary ctech-rounded-pill">
			<i class="fa fa-file-text"></i> {{ 'COM_SELLACIOUS_PRODUCT_PRICING_TYPE_QUERYFORM_OPEN_QUERY_FORM' | t }}
		</a>
	</template>
	<template v-slot:header>
		Query form
	</template>
</ctech-modal>
