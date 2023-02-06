import CalendarAbstract from './Abstract.js';

export default {
	mixins: [
		CalendarAbstract
	],
	inject: [
		'size',
		'focusDate'
	],
	data() {
		return {
			weeks: [...Array(this.focusDate.numWeeks).keys()].map(i => i+1)
		}
	},
	computed: {
		title() {
			return this.focusDate.format({year: 'numeric'});
		}
	},
	methods: {
		setWeek(week) {
			// TODO(chris): test is there a week jump on year select? => yes there is if the same month/day are in different weeks ... should we prevent that?
			this.focusDate.w = week;
			this.$emit('update:mode', 'week');
		}
	},
	template: `
	<div class="fhc-calendar-weeks">
		<calendar-header :title="title" @prev="focusDate.y--" @next="focusDate.y++" @click="$emit('update:mode', 'years')" />
		<div class="d-flex flex-wrap">
			<div v-for="(week, key) in weeks" :key="key" class="d-grid col-2">
				<button @click="setWeek(week)" class="btn btn-outline-secondary" :class="{'border-0': week != focusDate.w}">
					{{week}}
				</button>
			</div>
		</div>
	</div>`
}
