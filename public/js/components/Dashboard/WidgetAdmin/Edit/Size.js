import FormInput from "../../../Form/Input.js";

export default {
	name: 'WidgetsAdminEditSize',
	components: {
		FormInput,
	},
	props: {
		modelValue: [ Object, Number ],
		disabled: Boolean,
	},
	emits: [
		"update:modelValue"
	],
	computed: {
		min: {
			get() {
				if (Number.isInteger(this.modelValue))
					return this.modelValue;
				if (this.modelValue?.min === undefined)
					return '';
				return this.modelValue.min;
			},
			set(v) {
				if (v === '') {
					if (this.max === '')
						this.$emit('update:modelValue', undefined);
					else
						this.$emit('update:modelValue', { max: this.max });
				} else if (v == this.max) {
					this.$emit('update:modelValue', v);
				} else {
					if (this.max === '')
						this.$emit('update:modelValue', { min: v });
					else
						this.$emit('update:modelValue', {
							min: v,
							max: this.max,
						});
				}
			},
		},
		max: {
			get() {
				if (Number.isInteger(this.modelValue))
					return this.modelValue;
				if (this.modelValue?.max === undefined)
					return '';
				return this.modelValue.max;
			},
			set(v) {
				if (v === '') {
					if (this.min === '')
						this.$emit('update:modelValue', undefined);
					else
						this.$emit('update:modelValue', { min: this.min });
				} else if (v == this.min) {
					this.$emit('update:modelValue', v);
				} else {
					if (this.min === '')
						this.$emit('update:modelValue', { max: v });
					else
						this.$emit('update:modelValue', {
							min: this.min,
							max: v,
						});
				}
			},
		},
	},
	template: /* html */`
	<div class="widgets-admin-edit-size input-group">
		<span class="input-group-text"><slot /></span>
		<form-input
			v-model="min"
			type="text"
			placeholder="1"
			:disabled="disabled"
			input-group
		/>
		<span class="input-group-text">-</span>
		<form-input
			v-model="max"
			type="text"
			placeholder="&infin;"
			:disabled="disabled"
			input-group
		/>
	</div>
	`
}
