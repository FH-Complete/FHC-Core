import CalendarAbstract from './Abstract.js';

export default {
	mixins: [
		CalendarAbstract
	],
	emits: [
		'change'
	],
	inject: [
		'size'
	],
	data() {
		return {
			// TODO: 36, 24, 16 (2+ 12 + 2) months to enable year switch?
			monthIndices: [...Array(12).keys()]
		}
	},
	computed: {
		title() {
			return this.focusDate.format({year: 'numeric'});
		},
		months() {
			return this.monthIndices.map(i => (new Date(0, i, 1)).toLocaleString(this.$p.user_locale.value, {month: this.size < 2 ? 'short' : 'long'}));
		}
	},
	template: `
	<div class="fhc-calendar-months">
		<div class="d-flex flex-wrap">
			<div v-for="(month, key) in months" :key="key" class="d-grid col-4">
				<button @click=" $emit('change', key); focusDate.m = key;" class="btn btn-outline-secondary" :class="{'border-0': key != focusDate.m}">
					{{month}}
				</button>
			</div>
		</div>
	</div>`
}
