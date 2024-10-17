import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarWeekPage from './Week/Page.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarWeekPage,
		CalendarPane
	},
	computed: {
		title() {
			return this.focusDate.format({year: 'numeric'}) + ' KW ' + this.focusDate.w;
		}
	},
	methods: {
		paneChanged(dir) {
			this.focusDate.d += dir * 7;
			this.emitRangeChanged();
		},
		emitRangeChanged() {
			let start = this.focusDate.firstDayOfWeek;
			let end = this.focusDate.lastDayOfWeek;
			this.$emit('change:range', { start, end });
		},
		prev() {
			this.$refs.pane.prev();
		},
		next() {
			this.$refs.pane.next();
		},
		selectEvent(event) {
			this.$emit('input', ['select:event',event]);
		}
	},
	created() {
		this.emitRangeChanged();
	},
	template: `
	<div class="fhc-calendar-week">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="$emit('updateMode', 'weeks')"/>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-week-page v-slot="{event,day}" :year="focusDate.y" :week="focusDate.w+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectEvent" >
				<slot :event="event" :day="day" ></slot>
			</calendar-week-page>
		</calendar-pane>
	</div>`
}
