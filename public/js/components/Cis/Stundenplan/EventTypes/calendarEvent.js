export default {
	methods:{
		convertTime: function ([hour, minute]) {
			let date = new Date();
			date.setHours(hour);
			date.setMinutes(minute);
			// returns date string as hh:mm
			return date.toLocaleTimeString(this.$p.user_locale, { hour: '2-digit', minute: '2-digit', hour12: false });

		},
	},
	props:{
		event: {
			type:Object,
			required:true,
		},
	},
	template: `
	<div class="lehreinheitEventHeader" v-if="!event.allDayEvent && event?.beginn && event?.ende" >
		<span class="small">{{convertTime(event.beginn.split(":"))}}</span>
		<span class="small">{{convertTime(event.ende.split(":"))}}</span>
	</div>
	<div class="lehreinheitEventContent">
		<span>{{event.topic}}</span>
		<span v-for="lektor in event.lektor">{{lektor.kurzbz}}</span>
		<span>{{event.ort_kurzbz}}</span>
	</div>
	
	`,
}
