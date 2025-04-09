import BaseSlider from '../Base/Slider.js';
import DayView from './Day/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

export default {
	name: "ModeDay",
	components: {
		BaseSlider,
		DayView
	},
	inject: {
		locale: "locale",
		title: "title"
	},
	props: {
		currentDate: Number
	},
	emits: [
		"update:view",
		"update:range",
		"click"
	],
	data() {
		return {
			focusDate: this.currentDate,
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			const range = {};

			range.first = new Date(this.focusDate);
			range.last = CalendarDate.addDays(range.first, 1);

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					range.first = CalendarDate.addDays(range.first, this.rangeOffset);
				} else {
					range.last = CalendarDate.addDays(range.last, this.rangeOffset);
				}
			}

			return range;
		}
	},
	methods: {
		prevPage() {
			this.rangeOffset = this.$refs.slider.target - 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.prevPage().then(this.updatePage);
		},
		nextPage() {
			this.rangeOffset = this.$refs.slider.target + 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.nextPage().then(this.updatePage);
		},
		updatePage(offset) {
			const newFocusDate = this.focusDate + offset * CalendarDate.msPerDay;
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', new Date(this.focusDate));
			this.$emit('update:range', this.range);
		},
		viewAttrs(offset) {
			const showDate = this.focusDate + offset * CalendarDate.msPerDay;
			return {
				day: showDate
			}
		}
	},
	created() {
		this.title = Vue.computed(() => {
			return CalendarDate.format(new Date(this.focusDate), {day: "2-digit", month: "2-digit", year: "numeric"}, this.locale);
		});
	},
	mounted() {
		this.$emit('update:range', this.range);
	},
	beforeUnmount() {
		this.title = null;
	},
	template: `
	<div
		class="fhc-calendar-mode-day flex-grow-1 position-relative"
	>
		<base-slider ref="slider" v-slot="slot">
			<day-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</day-view>
		</base-slider>
	</div>
	`
}
