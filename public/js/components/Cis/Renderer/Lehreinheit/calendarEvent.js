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
	<div class="lehreinheitEventContent h-100 w-100 p-1" >
		<div id="lehreinheitEventHeader" class="h-100 " v-if="!event.allDayEvent && event?.beginn && event?.ende" >
			<span class="small">{{convertTime(event.beginn.split(":"))}}</span>
			<span class="small">{{convertTime(event.ende.split(":"))}}</span>
		</div>
		<div id="lehreinheitEventText">
			<span id="lehreinheitTopic">{{event.topic}}</span>
			<span id="lehreinheitLektoren" v-for="lektor in event.lektor">{{lektor.kurzbz}}</span>
			<span id="lehreinheitOrt">{{event.ort_kurzbz}}</span>
		</div>
	</div>
	`,
}
