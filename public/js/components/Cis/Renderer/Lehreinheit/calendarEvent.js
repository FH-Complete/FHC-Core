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
	computed:{
		calendarEventTooltip: function(){
			let lektorenEmpty = true;
			let tooltipString = `${this.$p.t('global','uhrzeit')}: ${this.convertTime(this.event.beginn.split(":"))} / ${this.convertTime(this.event.ende.split(":")) }`;
			
			tooltipString += `\n${this.$p.t('profilUpdate', 'topic')}: ${this.event.topic}`;
			
			if(Array.isArray(this.event.lektor) && this.event.lektor.length > 0){
				lektorenEmpty = false;
				tooltipString += `\n${this.$p.t('lehre','lektor')}: `;
				this.event.lektor.forEach(lektor => {
					tooltipString += `${lektor.kurzbz}\n`;
				})
			}
			if(lektorenEmpty){
				tooltipString += "\n";	
			}
			tooltipString += `${this.$p.t('person','ort')}: ${this.event.ort_kurzbz}`;
			
			return tooltipString;
		},
	},
	props:{
		event: {
			type:Object,
			required:true,
		},
	},
	template: /*html*/`
	<div class="lehreinheitEventContent h-100 w-100 p-1" >
		<div id="lehreinheitEventHeader" class="h-100 " v-if="!event.allDayEvent && event?.beginn && event?.ende" >
			<span class="small">{{convertTime(event.beginn.split(":"))}}</span>
			<span class="small">{{convertTime(event.ende.split(":"))}}</span>
		</div>
		<div id="lehreinheitEventText" v-tooltip="calendarEventTooltip">
			<span id="lehreinheitTopic">{{event.topic}}</span>
			<span id="lehreinheitLektoren" v-for="lektor in event.lektor">{{lektor.kurzbz}}</span>
			<span id="lehreinheitOrt">{{event.ort_kurzbz}}</span>
		</div>
	</div>
	`,
}
