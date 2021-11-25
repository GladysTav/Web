<div class="address-fields">
	<draggable v-model="fields">
		<div v-for="(field, i) in fields" :key="i" class="address-field" :class="field.class">
			<b class="field-label">{{ field.label | t }}</b>
			<input type="text" class="noborder" v-model="field.labelValue" />
			<div class="btn-group" >
				<label :for="id + '0'" class="btn btn-default" @click.prevent="field.show = 0" :class="field.show === 0 ? 'active' : ''">
					<span>{{ 'JHIDE' | t }}</span>
					<input type="radio" :id="id + '0'" :name="name" :value="0" :checked="field.show === 0" />
				</label>
				<label :for="id + '1'" class="btn btn-default" @click.prevent="field.show = 1" :class="field.show === 1 ? 'active' : ''">
					<span>{{ 'JSHOW' | t }}</span>
					<input type="radio" :id="id + '1'" :name="name" :value="1" :checked="field.show === 1" />
				</label>
				<label :for="id + '2'" class="btn btn-default" @click.prevent="field.show = 2" :class="field.show === 2 ? 'active' : ''">
					<span>{{ 'JREQUIRED' | t }}</span>
					<input type="radio" :id="id + '2'" :name="name" :value="2" :checked="field.show === 2" />
				</label>
			</div>
			<component :is="`field-${field.type}`" :id="`${id}_${field.name}`" :field="field"></component>
		</div>
	</draggable>
	<input type="hidden" :id="id" :name="name" :value="output" />
</div>
