export default {
	name: "WidgetsGenteratorReportPickerOption",
	props: {
		option: {
			type: Object,
			required: true
		},
	},
	template: /*html*/ `
	<div class="widgets-report-config-picker-option">
		<span>{{ option.bezeichnung || option.statistik_kurzbz }} </span>
		<small class="text-muted">
			[<b v-if="option.gruppe" class="text-muted">{{ option.gruppe }}: </b>
			{{ option.statistik_kurzbz }}]
		</small>
	</div>
	`,
};
