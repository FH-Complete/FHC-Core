import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarYearsPage from './Years/Page.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarYearsPage,
		CalendarPane
	},
	inject: [
		'size'
	],
	data() {
		return {
			start: 0
		}
	},
	computed: {
		range() {
			switch (this.size) {
			case 3:
			// eslint-disable-next-line
			case 2:
				return 24;
			}
			return 12;
		},
		end() {
			return this.start + this.range - 1;
		},
		title() {
			return this.start + ' - ' + this.end;
		}
	},
	methods: {
		paneChanged(dir) {
			this.start += this.range * dir;
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: -1, m: 0, d: 0 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 1, m: 0, d: 0 });
		}
	},
	created() {
		this.start = this.focusDate.y - this.focusDate.y%this.range;
	},
	template: `
	<div class="fhc-calendar-years">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" />
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-years-page :data-test="slot.index" :start="start+range*slot.offset" :end="start+range*slot.offset+range" @updateMode="$emit('updateMode')"/>
		</calendar-pane>
	</div>`
}
