import CalendarHeader from './Header.js';

export default {
	components: {
		CalendarHeader
	},
	inject: [
		'date',
		'focusDate',
		'size'
	],
	emits: [
		'updateMode',
		'change:range',
		'input'
	]
}
