export default {
	props:{
		event: {
			type: Object,
			required: true
		},
		timeSlotDisplayBehavior: {
			type: String,
			default: "default",
			// options: default, always, never 
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
				} else {
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
		},
		timeSlotDisplayClasses() {
			switch (this.$props.timeSlotDisplayBehavior) {
				case "always":
					return "d-grid";
				case "never":
					return "d-none";
				default:
					return "d-none d-xl-grid";
			}
		},
		// link into the FHC-Core-Anwesenheiten tool
		// digi_anw_data is attached per event by the extension's `extendStundenplanData` listener
		anwLink() {
			const d = this.event && this.event.digi_anw_data;
			if (!d || !d.stg_kz || !d.lv_id || !d.sem_kurzbz)
				return null;
			const base = FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/extensions/FHC-Core-Anwesenheiten/';
			return base
				+ `?stg_kz=${encodeURIComponent(d.stg_kz)}`
				+ `&sem=${encodeURIComponent(d.sem ?? '')}`
				+ `&lvid=${encodeURIComponent(d.lv_id)}`
				+ `&sem_kurzbz=${encodeURIComponent(d.sem_kurzbz)}`;
		},
		anwLinkTitle() {
			return this.$p.t('global/digitalesAnwManagement');
		},
	},
	template: /*html*/`
	<div
		class="cis-renderer-lehreinheit-calendar-event calendar-event-default h-100 w-100 p-1"
		@wheel.stop
	>
		<div
			v-if="!event?.allDayEvent && event?.beginn && event?.ende"
			:class="timeSlotDisplayClasses"
			class="event-time h-100"
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
		<a
			v-if="anwLink"
			:href="anwLink"
			target="_blank"
			rel="noopener"
			class="d-flex align-items-center flex-shrink-0"
			style="margin-right: 4px"
			:title="anwLinkTitle"
			:aria-label="anwLinkTitle"
			draggable="false"
			@click.stop
			@mousedown.stop
		>
			<i class="fa fa-arrow-up-right-from-square fs-5" aria-hidden="true"></i>
		</a>
	</div>
	`,
}
