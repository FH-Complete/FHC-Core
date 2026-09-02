import TableView from './Table/View.js';

export default {
	name: "ModeTable",
	components: {
		TableView
	},
	props: {
		currentDate: {
			type: luxon.DateTime,
			required: true
		},
		rangeEnd: {
			type: luxon.DateTime,
			default: null
		}
	},
	emits: [
		"update:range",
	],
	data() {
		return {
			focusDate: this.currentDate,
			focusEnd: this.rangeEnd || this.currentDate
		};
	},
	computed: {
		range() {
			return luxon.Interval.fromDateTimes(this.focusDate.startOf('day'), this.focusEnd.endOf('day'));
		}
	},
	watch: {
		currentDate(newDate) {
			this.focusDate = newDate;
			if (!this.rangeEnd)
				this.focusEnd = newDate;
			this.$emit('update:range', this.range);
		},
		rangeEnd(newEnd) {
			this.focusEnd = newEnd || this.focusDate;
			this.$emit('update:range', this.range);
		}
	},
	methods: {

	},
	mounted() {
		this.$emit('update:range', this.range);
	},
	template: `
	<div>
		<table-view ref="view" v-bind="$attrs" :day="focusDate" :end="focusEnd">
			<template v-slot="slot"><slot v-bind="slot" mode="week" /></template>
		</table-view>
	</div>
	`
}