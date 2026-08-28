import FieldString from './FieldString.js';
import FieldWysiwyg from './FieldWysiwyg.js';

export default {
	name: 'XsdForm',
	components: {
		'field-string': FieldString,
		'field-wysiwyg': FieldWysiwyg
	},
	props: {
		schema: Object,
		modelValue: Object,
		disabled: Boolean
	},
	emits: ['update:modelValue'],
	methods: {
		updateField(name, value) {
			this.$emit('update:modelValue', { ...this.modelValue, [name]: value });
		},
		collectValues() {
			const vals = { ...this.modelValue };
			if (!this.schema?.fields) return vals;
			for (const field of this.schema.fields) {
				if (field.type === 'wysiwyg') {
					const ref = this.$refs['wysiwyg-' + field.name];
					const comp = Array.isArray(ref) ? ref[0] : ref;
					if (comp?.getContent) vals[field.name] = comp.getContent();
				}
			}
			return vals;
		}
	},
	template: `
		<div v-if="schema && schema.fields">
			<template v-for="field in schema.fields" :key="field.name">
				<field-wysiwyg
					v-if="field.type === 'wysiwyg'"
					:ref="'wysiwyg-' + field.name"
					:modelValue="modelValue[field.name] || ''"
					:label="field.name"
					:required="field.required"
					:disabled="disabled"
					@update:modelValue="updateField(field.name, $event)"
				></field-wysiwyg>
				<div v-else-if="field.type === 'boolean'" class="mb-3">
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							:checked="!!modelValue[field.name]"
							:disabled="disabled"
							@change="updateField(field.name, $event.target.checked)"
							:id="'field-' + field.name"
						>
						<label class="form-check-label" :for="'field-' + field.name">
							{{ field.name }}<span v-if="field.required" class="text-danger"> *</span>
						</label>
					</div>
				</div>
				<field-string
					v-else
					:modelValue="modelValue[field.name] || ''"
					:label="field.name"
					:required="field.required"
					:disabled="disabled"
					:type="field.type === 'date' ? 'date' : 'text'"
					@update:modelValue="updateField(field.name, $event)"
				></field-string>
			</template>
		</div>
	`
};
