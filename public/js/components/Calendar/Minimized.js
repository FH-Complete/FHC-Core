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
	<div class="fhc-calendar-minimized h-100 d-flex flex-column">
		<slot name="minimizedPage"></slot>
	</div>`
}
