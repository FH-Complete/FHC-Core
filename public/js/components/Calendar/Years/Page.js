export default {
	inject: [
		'focusDate'
	],
	props: {
		start: Number,
		end: Number
	},
	emits: [
		'update:mode'
	],
	data() {
		return {
		}
	},
	computed: {
		years() {
			return [...Array(this.end - this.start).keys()].map(i => i + this.start);
		}
	},
	template: `
	<div class="fhc-calendar-years-page d-flex flex-wrap">
		<div v-for="year in years" :key="year" class="d-grid col-4">
			<button class="btn btn-outline-secondary" :class="{'border-0': year != focusDate.y}" @click="focusDate.y = year; $emit('update:mode')">
				{{year}}
			</button>
		</div>
	</div>`
}
