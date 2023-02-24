import CalendarAbstract from './Abstract.js';

export default {
	mixins: [
		CalendarAbstract
	],
	inject: [
		'size',
		'minimized',
		'date',
		'classHeader'
	],
	data() {
		return {
			start: 0
		}
	},
	methods: {
		maximize() {
			// NOTE(chris): set "app.config.unwrapInjectedRef = true" for this to work
			this.minimized = false;
		}
	},
	template: `
	<div class="fhc-calendar-minimized">
		<div class="card-header d-grid" :class="classHeader">
			<button class="btn btn-link link-secondary text-decoration-none" @click="maximize">{{ date.format({dateStyle: ['long','full','full','full'][this.size]}) }}</button>
		</div>
	</div>`
}
