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
			tooltipString += `${this.$p.t('person', 'ort')}: ${this.event.ort_kurzbz}`;
			if(Array.isArray(this.event.lektor) && this.event.lektor.length > 0){
				lektorenEmpty = false;
				tooltipString += `\n${this.$p.t('lehre','lektor')}: `;
				this.event.lektor.slice(0,3).forEach(lektor => {
					tooltipString += `${lektor.kurzbz}\n`;
				})
				if(this.event.lektor.length > 3){
					tooltipString += `${this.$p.t('lehre', 'weitereLektoren', [(this.event.lektor.length - 3)])}\n`;
				}
			}
			if(lektorenEmpty){
				tooltipString += "\n";	
			}
			
			
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
	<div class="lehreinheitEventContent h-100 w-100 p-1" @wheel.stop >
		<div id="lehreinheitEventHeader" class="d-none d-xl-grid h-100 " v-if="!event.allDayEvent && event?.beginn && event?.ende" >
			<span >{{convertTime(event.beginn.split(":"))}}</span>
			<span >{{convertTime(event.ende.split(":"))}}</span>
		</div>
		<div id="lehreinheitEventText" v-tooltip="calendarEventTooltip">
			<span id="lehreinheitTopic">{{event.topic}}</span>
			<span id="lehreinheitOrt">{{event.ort_kurzbz}}</span>
			<span id="lehreinheitLektoren" v-for="(lektor,index) in event.lektor.slice(0,3)">
				{{lektor.kurzbz}}
			</span>
			<span id="lektorEllipsis" class="fw-bold" v-if="event.lektor.length > 3">...+ 
		 	{{event.lektor.length-3}}</span>
		</div>
	</div>
	`,
}
