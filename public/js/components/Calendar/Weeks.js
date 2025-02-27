import CalendarAbstract from './Abstract.js';

export default {
	mixins: [
		CalendarAbstract
	],
	emits: [
		'change'
	],
	inject: [
		'size',
		'focusDate'
	],
	props: {
		header: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		weeks(){
			return [...Array(this.focusDate.numWeeks).keys()].map(i => i + 1);
		},
		title() {
			return this.focusDate.format({year: 'numeric'});
		}
	},
	methods: {
		setWeek(week) {
			// TODO(chris): test is there a week jump on year select? => yes there is if the same month/day are in different weeks ... should we prevent that?
			this.focusDate.w = week;
			this.$emit('change', week);
		},
		prev(){
			this.focusDate.y--;
			this.focusDate._clean();
		},
		next() {
			this.focusDate.y++;
			this.focusDate._clean();
		},
	},
	template: `
	<div class="fhc-calendar-weeks h-100">
		<calendar-header v-if="header" :title="title" @prev="prev" @next="next" @click="$emit('updateMode', 'years')" @updateMode="$emit('updateMode', $event)" />
		<div class="d-flex flex-wrap">
			<div v-for="(week, key) in weeks" :key="key" class="d-grid col-2">
				<button @click="setWeek(week)" class="btn btn-outline-secondary" :class="{'border-0': week != focusDate.w}">
					{{week}}
				</button>
			</div>
		</div>
	</div>`
}
