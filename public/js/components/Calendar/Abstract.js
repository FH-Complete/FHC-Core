import CalendarHeader from './Header';

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
		'update:mode',
		'change:range',
		'input'
	]
}
