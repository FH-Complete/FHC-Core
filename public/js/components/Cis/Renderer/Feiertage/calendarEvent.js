
export default {
	props: {
		event: {
			type: Object,
			required: true,
		},
	},
	template: `
	<div class="cis-renderer-feiertage-calendar-event">
		<i class="event-icon" class="fa-regular fa-calendar"></i>
		<span class="event-title fw-bold text-center">{{ event.titel }}</span>
	</div>`,
};