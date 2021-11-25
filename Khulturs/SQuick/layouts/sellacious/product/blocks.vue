<component :is="'product-block-' + blockStyle" v-for="product in products" :key="product.product_id" :product="product" :params="params" :user="user" :context="context" @open-modal="openModal"></component>

<product-quick-view :url="modal.url" :attributes="modal.attributes" :content="modal.content" :product="product" :user="user" :params="params" :context="context"></product-quick-view>

<ctech-modal :id="`checkout-questions-form`" classes="checkout-questions-form" :footer="false">
	<template v-slot:header>
		{{ 'COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_TITLE' | t }}
	</template>
</ctech-modal>
