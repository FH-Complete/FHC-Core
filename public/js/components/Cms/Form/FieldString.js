export default {
	name: 'FieldString',
	props: {
		modelValue: { type: String, default: '' },
		label: String,
		required: Boolean,
		disabled: Boolean,
		type: { type: String, default: 'text' }
	},
	emits: ['update:modelValue'],
	template: `
		<div class="mb-3">
			<label class="form-label">
				{{ label }}<span v-if="required" class="text-danger"> *</span>
			</label>
			<input
				class="form-control"
				:type="type"
				:value="modelValue"
				:disabled="disabled"
				@input="$emit('update:modelValue', $event.target.value)"
			>
		</div>
	`
};
