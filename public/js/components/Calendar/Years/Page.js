export default {
	inject: [
		'focusDate'
	],
	props: {
		start: Number,
		end: Number
	},
	emits: [
		'updateMode'
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
	mounted() {
		const container = document.getElementById("calendarContainer")
		if(container) {
			container.style['overflow-y'] = 'scroll'
			container.style['overflow-x'] = 'auto'
		}
	},
	template: `
	<div class="fhc-calendar-years-page d-flex flex-wrap">
		<div v-for="year in years" :key="year" class="d-grid col-4">
			<button class="btn btn-outline-secondary card justify-content-center" :class="{'border-0': year != focusDate.y}" @click="focusDate.y = year; $emit('updateMode')">
				{{year}}
			</button>
		</div>
	</div>`
}
