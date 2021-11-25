<div class="color-selector ctech-wrapper ctech-d-flex">
	<div class="element-template" v-html="options.template"></div>
	<a class="ctech-btn-default ctech-btn-sm pencil" :class="!options.isPremium ? 'unclickable' : ''" @click.prevent="showEditor = true" v-if="!showEditor"><i class="fa fa-pencil"></i></a>
	<div class="editor" v-if="showEditor">
		<div v-for="attr in options.attributes" class="color-property">
			<div class="ctech-d-flex">
				<label v-html="attr.label"></label>
				<colorpicker :color="attr.current" v-model="attr.current"/>
			</div>
			<div class="ctech-d-flex" v-if="options.hover == 'true'">
				<label>{{ attr.label + ' :hover' }}</label>
				<colorpicker :color="attr.hoverCurrent" v-model="attr.hoverCurrent"/>
			</div>
		</div>
	</div>
	<a class="ctech-btn-default ctech-btn-sm refresh"v-if="options.isPremium" @click.prevent="resetColors"><i class="fa fa-refresh"></i></a>
	<a class="ctech-btn-default ctech-btn-sm cancel" v-if="showEditor" @click.prevent="closeEditor"><i class="fa fa-times"></i></a>
	<span v-html="css"></span>
	<input type="hidden" :id="options.id" :name="options.name" v-model="value">
</div>
