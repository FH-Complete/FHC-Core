export default {
	props: {
		event: {
			type: Object,
			required: true,
		},
	},
	template: /* html */ `
	<div
		class="coodle-calendar-event calendar-event-default h-100 w-100 p-1 d-flex flex-row"
		v-tooltip="tooltipString"
	></div>
	`,
};
