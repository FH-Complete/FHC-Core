import ReportVar from './Vars/Var.js';

export default {
	name: "WidgetsGenteratorReportVars",
	components: {
		ReportVar,
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
		details: {
			type: Array,
			required: true
		},
	},
	emits: [
		"update:modelValue",
	],
	watch: {
		details() {
			this.cleanupVars();
		},
	},
	methods: {
		cleanupVars() {
			/*const allowedKeys = this.details.map(detail => detail.kurzbz);
			Object.keys(this.modelValue).forEach(key => {
				if (!allowedKeys.includes(key))
					delete this.modelValue[key];
			});
			this.details.forEach(detail => {
				if (!this.modelValue[detail.kurzbz])
					this.modelValue[detail.kurzbz] = { type: 'fix' };
			});
			this.$emit('update:modelValue', this.modelValue);*/
		},
	},
	created() {
		this.cleanupVars();
	},
	template: /*html*/ `
	<div class="widgets-report-config-vars">
		<label v-if="details.length">{{ $p.t('dashboard/widget_report_vars') }}</label>
		<template v-for="detail in details" :key="detail.kurzbz">
			<report-var
				v-model="modelValue[detail.kurzbz]"
				:detail="detail"
				@update:model-value="cleanupVars"
			/>
		</template>
	</div>
	`,
};
