<div class="ctech-container-fluid">
	<div class="ctech-row">
		<component :is="`category-block-${blockStyle}`" v-for="(category, i) in categories" :key="i" :category="category" :params="params" :user="user"/>
	</div>
</div>
