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
	methods: {
		cleanupVars() {
			const allowedKeys = this.details.map(detail => detail.kurzbz);
			Object.keys(this.modelValue).forEach(key => {
				if (!allowedKeys.includes(key))
					delete this.modelValue[key];
			});
		},
	},
	created() {
		this.details.forEach(detail => {
			if (!this.modelValue[detail.kurzbz])
				this.modelValue[detail.kurzbz] = { type: 'fix' };
		});
	},
	template: /*html*/ `
	<div class="widgets-report-config-vars">
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
