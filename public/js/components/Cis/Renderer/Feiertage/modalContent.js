import { formatDate } from "../../../../helpers/DateHelpers.js"

export default {
	props:{
		event: {
			type: Object,
			required: true,
		},
		
	},
	methods:{
		methodFormatDate: function (d) {
			return formatDate(d);
		},
	},
	template: `
	<div>
	<h2>{{event.titel ? event.titel : event.topic}}</h2>
	<p><i id="ferienEventIcon" class="fa-regular fa-calendar me-2"></i>{{methodFormatDate(event?.datum)}}</p>
	</div>`,
}