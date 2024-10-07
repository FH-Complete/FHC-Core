import CalendarAbstract from './Abstract.js';

export default {
	mixins: [
		CalendarAbstract
	],
	inject: [
		'size'
	],
	data() {
		return {
			monthIndices: [...Array(12).keys()]
		}
	},
	computed: {
		title() {
			return this.focusDate.format({year: 'numeric'});
		},
		months() {
			return this.monthIndices.map(i => (new Date(0, i, 1)).toLocaleString(undefined, {month: this.size < 2 ? 'short' : 'long'}));
		}
	},
	template: `
	<div class="fhc-calendar-months">
		<calendar-header :title="title" @prev="focusDate.y--" @next="focusDate.y++" @click="$emit('updateMode', 'years')" @updateMode="$emit('updateMode', $event)" />
		<div class="d-flex flex-wrap">
			<div v-for="(month, key) in months" :key="key" class="d-grid col-4">
				<button @click="focusDate.m = key; $emit('updateMode', 'month')" class="btn btn-outline-secondary" :class="{'border-0': key != focusDate.m}">
					{{month}}
				</button>
			</div>
		</div>
	</div>`
}
