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
		timezone: "timezone",
		title: "title"
	},
	props: {
		currentDate: luxon.DateTime
	},
	emits: [
		"update:currentDate",
		"update:view",
		"update:range",
		"click"
	],
	data() {
		return {
			focusDate: luxon.DateTime.fromMillis(this.currentDate.ts).setZone(this.timezone),
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			const range = {};

			range.first = this.focusDate.startOf('day');
			range.last = this.focusDate.endOf('day');
			
			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					range.first = range.first.plus({ days: this.rangeOffset });
				} else {
					range.last = range.last.plus({ days: this.rangeOffset });
				}
			}

			return range;
		}
	},
	watch: {
		currentDate() {
			this.rangeOffset = this.currentDate.startOf('day').diff(this.focusDate.startOf('day'), 'days').days;
			if (this.rangeOffset) {
				this.$emit('update:range', this.range);
				this.$refs.slider.slidePages(this.rangeOffset).then(this.updatePage);
			}
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
		updatePage(days) {
			const newFocusDate = this.focusDate.plus({ days });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
		},
		viewAttrs(days) {
			const showDate = this.focusDate.plus({ days });
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
