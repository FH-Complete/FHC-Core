import BaseSlider from '../Base/Slider.js';
import MonthView from './Month/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "ModeMonth",
	components: {
		BaseSlider,
		MonthView
	},
	inject: {
		locale: "locale",
		title: "title"
	},
	props: {
		currentDate: {
			type: luxon.DateTime,
			required: true
		}
	},
	emits: [
		"update:currentDate",
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

			range.first = this.focusDate.startOf('month').startOf('week');
			range.last = range.first.plus({ days: 41 }).endOf('day'); // NOTE(chris): 6 weeks minus 1 day

			if (this.rangeOffset != 0) {
				const nextFocusDate = this.focusDate.plus({ months: this.rangeOffset});
				const nextRangeStart = nextFocusDate.startOf('month').startOf('week');
				if (this.rangeOffset < 0) {
					range.first = nextRangeStart;
				} else {
					range.last = nextRangeStart.plus({ days: 41 }).endOf('day');
				}
			}

			return range;
		}
	},
	watch: {
		locale() {
			this.$emit('update:range', this.range);
		},
		currentDate() {
			this.rangeOffset = this.currentDate.startOf('month').diff(this.focusDate.startOf('month'), 'months').months;
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
		updatePage(months) {
			const newFocusDate = this.focusDate.plus({ months });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
		},
		viewAttrs(months) {
			const day = this.focusDate.plus({ months });
			return { day };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
			case 'week':
				// default: Move to week if not in month
				console.log('week default');
				return this.clickDefaultWeek(evt.detail.value);
			case 'day':
				// default: Move to day and set current-date
				return this.clickDefaultDay(new Date(evt.detail.value));
			case 'event':
				// TODO(chris): IMPLEMENT!
				// default: ???
				break;
			}
		},
		clickDefaultWeek(week) {
			const weekdays = CalendarDate.getDaysInWeek(week.number, week.year, this.locale);
			let day = null;
			
			if (weekdays[0].getMonth() != this.focusDate.getMonth()) {
				day = weekdays[0];
			} else if (weekdays[6].getMonth() != this.focusDate.getMonth()) {
				day = weekdays[6];
			}
			
			if (day) {
				const monthsFocus = this.focusDate.getFullYear() * 12 + this.focusDate.getMonth();
				const monthsTarget = day.getFullYear() * 12 + day.getMonth();

				const diffMonths = monthsTarget - monthsFocus;

				this.rangeOffset = this.$refs.slider.target + monthsTarget - monthsFocus;
				this.$emit('update:range', this.range);
				this.$refs.slider.slidePages(monthsTarget - monthsFocus).then(offset => {
					this.focusDate = day;
					this.rangeOffset = 0;
					this.$emit('update:currentDate', day.getTime());
					this.$emit('update:range', this.range);
				});
			}
		},
		clickDefaultDay(day) {
			const monthsFocus = this.focusDate.getFullYear() * 12 + this.focusDate.getMonth();
			const monthsTarget = day.getFullYear() * 12 + day.getMonth();

			const diffMonths = monthsTarget - monthsFocus;

			if (diffMonths) {
				this.rangeOffset = this.$refs.slider.target + monthsTarget - monthsFocus;
				this.$emit('update:range', this.range);
				this.$refs.slider.slidePages(monthsTarget - monthsFocus).then(offset => {
					this.focusDate = day;
					this.rangeOffset = 0;
					this.$emit('update:currentDate', day.getTime());
					this.$emit('update:range', this.range);
				});
			} else {
				this.focusDate = day;
				this.$emit('update:currentDate', day.getTime());
			}
		}
	},
	created() {
		this.title = Vue.computed(() => CalendarDate.format(this.focusDate, {month: 'long', year: 'numeric'}, this.locale));
	},
	mounted() {
		this.$emit('update:range', this.range);
	},
	beforeUnmount() {
		this.title = null;
	},
	template: `
	<div
		class="fhc-calendar-mode-month flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<base-slider ref="slider" v-slot="slot">
			<month-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" mode="month" /></template>
			</month-view>
		</base-slider>
	</div>
	`
}
