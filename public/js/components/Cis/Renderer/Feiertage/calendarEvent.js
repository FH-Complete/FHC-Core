
export default {
	props: {
		event: {
			type: Object,
			required: true,
		},
	},
	template: `
		<div class="feiertagEventContent " >
			<i id="ferienEventIcon" class="fa-regular fa-calendar"></i>
			<span id="ferienEventTitle" ><strong>{{event.titel}}</strong></span>
		</div>`,
};