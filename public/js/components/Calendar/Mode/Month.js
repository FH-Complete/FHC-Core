import BaseSlider from '../Base/Slider.js';
import PickerMonth from '../Picker/Month.js';
import MonthView from './Month/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "ModeMonth",
	components: {
		BaseSlider,
		PickerMonth,
		MonthView
	},
	inject: {
		locale: "locale",
		title: "title"
	},
	props: {
		currentDate: Number
	},
	emits: [
		"update:currentDate",
		"update:view",
		"update:range",
		"click"
	],
	data() {
		return {
			monthPicker: false,
			focusDate: new Date(this.currentDate),
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			const range = {};

			range.first = CalendarDate.getFirstDayOfWeek(
				new Date(this.focusDate.getFullYear(), this.focusDate.getMonth(), 1),
				this.locale
			);
			range.last = CalendarDate.addDays(range.first, 41);

			if (this.rangeOffset != 0) {
				const nextFocusDate = CalendarDate.addMonths(this.focusDate, this.rangeOffset);
				const nextRangeStart = CalendarDate.getFirstDayOfWeek(
					new Date(nextFocusDate.getFullYear(), nextFocusDate.getMonth(), 1),
					this.locale
				);
				if (this.rangeOffset < 0) {
					range.first = nextRangeStart;
				} else {
					range.last = CalendarDate.addDays(nextRangeStart, 41);
				}
			}

			return range;
		}
	},
	watch: {
		locale() {
			this.$emit('update:range', this.range);
		}
	},
	methods: {
		showPicker() {
			if (this.monthPicker)
				this.$refs.picker.toggleYearPicker();
			this.monthPicker = true;
		},
		prevPage() {
			if (this.monthPicker)
				return this.$refs.picker.prevPage();

			this.rangeOffset = this.$refs.slider.target - 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.prevPage().then(this.updatePage);
		},
		nextPage() {
			if (this.monthPicker)
				return this.$refs.picker.nextPage();

			this.rangeOffset = this.$refs.slider.target + 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.nextPage().then(this.updatePage);
		},
		updatePage(offset) {
			const newFocusDate = CalendarDate.addMonths(this.focusDate, offset);
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:range', this.range);
		},
		setMonth(month) {
			this.focusDate = month;
			this.monthPicker = false;
			this.$emit('update:range', this.range);
		},
		viewAttrs(offset) {
			const showDate = CalendarDate.addMonths(this.focusDate, offset);
			return {
				month: showDate.getMonth(),
				year: showDate.getFullYear()
			}
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
		<Transition name="picker">
			<picker-month
				v-if="monthPicker"
				ref="picker"
				:current-date="focusDate"
				@update:current-date="setMonth"
				class="position-absolute w-100 h-100"
			/>
		</Transition>
		<base-slider ref="slider" v-slot="slot">
			<month-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</month-view>
		</base-slider>
	</div>
	`
}
