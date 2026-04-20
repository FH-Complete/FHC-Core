export default {
	props:{
		event: {
			type: Object,
			required: true
		}
	},
	computed:{
		tooltipString() {
			const tooltipArray = [];

			tooltipArray.push([
				this.$p.t('global/uhrzeit'),
				[this.start, this.end].join(' - ')
			].join(": "));
			
			tooltipArray.push([
				this.$p.t('profilUpdate/topic'),
				this.event.topic
			].join(": "));
			
			tooltipArray.push([
				this.$p.t('person/ort'),
				this.event.ort_kurzbz
			].join(": "));
			
			if (Array.isArray(this.event.lektor) && this.event.lektor.length > 0) {
				if (this.event.lektor.length > 3) {
					tooltipArray.push([
						this.$p.t('lehre/lektor'),
						this.event.lektor.slice(0, 3).map(lektor => lektor.kurzbz).join("\n")
						+ "\n" + this.$p.t('lehre/weitereLektoren', [this.event.lektor.length - 3])
					].join(": "));
				} else {;
					tooltipArray.push([
						this.$p.t('lehre/lektor'),
						this.event.lektor.map(lektor => lektor.kurzbz).join("\n")
					].join(": "));
				}
			}
			
			return tooltipArray.join("\n");
		},
		start() {
			return luxon.Duration
				.fromISOTime(this.event.beginn)
				.toISOTime({ suppressSeconds: true });
		},
		end() {
			return luxon.Duration
				.fromISOTime(this.event.ende)
				.toISOTime({ suppressSeconds: true });
		}
	},
	template: /*html*/`
	<div
		class="cis-renderer-lehreinheit-calendar-event calendar-event-default h-100 w-100 p-1"
		@wheel.stop
	>
		<div
			v-if="!event.allDayEvent && event?.beginn && event?.ende"
			class="event-time d-none d-xl-grid h-100"
		>
			<span>{{ start }}</span>
			<span>{{ end }}</span>
		</div>
		<div class="event-text" v-tooltip="tooltipString">
			<span class="event-topic">{{ event.topic }}</span>
			<span class="event-place">{{ event.ort_kurzbz }}</span>
			<span
				v-for="(lektor,index) in event.lektor.slice(0, 3)"
				class="event-lectors"
			>
				{{ lektor.kurzbz }}
			</span>
			<span
				v-if="event.lektor.length > 3"
				class="event-lectors-plus"
			>
				... +{{ event.lektor.length - 3 }}
			</span>
		</div>
	</div>
	`,
}
