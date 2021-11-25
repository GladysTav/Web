<div class="field-context" v-if="field.show !== 0">
	<div v-for="option in field.options"  class="context-option">
		<input type="checkbox" :name="option.name" :checked="option.show" v-model="option.show" /> {{ option.label | t }}
	</div>
</div>
