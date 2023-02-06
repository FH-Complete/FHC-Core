import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarMonthPage from './Month/Page.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarMonthPage,
		CalendarPane
	},
	data() {
		return {
			syncOnNextChange: false
		}
	},
	computed: {
		title() {
			return this.focusDate.format({month: ['short','long','long','long'][this.size], year: 'numeric'});
		}
	},
	methods: {
		paneChanged(dir) {
			if (this.syncOnNextChange) {
				this.syncOnNextChange = false;
				this.focusDate.set(this.date);
			} else {
				this.focusDate.m += dir;
			}
			this.$emit('change:range', {
				start: new Date(this.focusDate.y, this.focusDate.m, 1), 
				end: new Date(this.focusDate.y, this.focusDate.m+1, 0)
			});
		},
		prev() {
			this.$refs.pane.prev();
		},
		next() {
			this.$refs.pane.next();
		},
		selectDay(day) {
			let m = day.getMonth();
			if (this.focusDate.m != m) {
				this.syncOnNextChange = true;
				if (this.focusDate.m-1 == m || (m == 11 && !this.focusDate.m))
					this.$refs.pane.prev();
				else
					this.$refs.pane.next();
			} else {
				this.focusDate.set(this.date);
			}
			this.$emit('input', ['select:day',day])
		}
	},
	created() {
		this.$emit('change:range', {
			start: new Date(this.focusDate.y, this.focusDate.m, 1), 
			end: new Date(this.focusDate.y, this.focusDate.m+1, 0)
		});
	},
	template: `
	<div class="fhc-calendar-month">
		<calendar-header :title="title" @prev="prev" @next="next" @click="$emit('update:mode', 'months')" />
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-month-page :year="focusDate.y" :month="focusDate.m+slot.offset" @update:mode="$emit('update:mode', $event)" @page:back="prev" @page:forward="next" @input="selectDay" />
		</calendar-pane>
	</div>`
}
