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
	template: /*html*/ `
	<div class="widgets-report-config-vars">
		<label v-if="details.length">{{ $p.t('dashboard/widget_report_vars') }}</label>
		<template v-for="detail in details" :key="detail.kurzbz">
			<report-var
				:model-value="modelValue[detail.kurzbz]"
				:detail="detail"
				@update:model-value="$emit('update:modelValue', { ...modelValue, [detail.kurzbz]: $event })"
			/>
		</template>
	</div>
	`,
};
