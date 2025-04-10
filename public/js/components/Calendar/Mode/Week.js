import BaseSlider from '../Base/Slider.js';
import PickerWeek from '../Picker/Week.js';
import WeekView from './Week/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "ModeWeek",
	components: {
		BaseSlider,
		PickerWeek,
		WeekView
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
			weekPicker: false,
			focusDate: new Date(this.currentDate),
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			const range = {};

			range.first = CalendarDate.getFirstDayOfWeek(
				this.focusDate,
				this.locale
			);
			range.last = CalendarDate.addDays(range.first, 7);

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					range.first = CalendarDate.addDays(range.first, this.rangeOffset * 7);
				} else {
					range.last = CalendarDate.addDays(range.last, this.rangeOffset * 7);
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
			if (this.weekPicker)
				this.$refs.picker.toggleYearPicker();
			this.weekPicker = true;
		},
		prevPage() {
			if (this.weekPicker)
				return this.$refs.picker.prevPage();

			this.rangeOffset = this.$refs.slider.target - 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.prevPage().then(this.updatePage);
		},
		nextPage() {
			if (this.weekPicker)
				return this.$refs.picker.nextPage();

			this.rangeOffset = this.$refs.slider.target + 1;
			this.$emit('update:range', this.range);
			this.$refs.slider.nextPage().then(this.updatePage);
		},
		updatePage(offset) {
			const newFocusDate = CalendarDate.addDays(this.focusDate, offset * 7);
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate.getTime());
			this.$emit('update:range', this.range);
		},
		setWeek(week) {
			this.focusDate = week;
			this.weekPicker = false;
			this.$emit('update:currentDate', this.focusDate.getTime());
			this.$emit('update:range', this.range);
		},
		viewAttrs(offset) {
			const showDate = CalendarDate.addDays(this.focusDate, offset * 7);
			const week = CalendarDate.getWeek(showDate, this.locale);
			return {
				week: week.number,
				year: week.year
			}
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
			case 'day':
				// default: Set current-date
				this.focusDate = new Date(evt.detail.value);
				this.$emit('update:currentDate', this.focusDate.getTime());
				break;
			case 'event':
				// TODO(chris): IMPLEMENT!
				// default: ???
				break;
			}
		}
	},
	created() {
		this.title = Vue.computed(() => {
			const week = CalendarDate.getWeek(this.focusDate, this.locale)
			// TODO(chris): return this.$p.t('core/year_kw', week);
			return `${week.year} KW ${week.number}`;
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
		class="fhc-calendar-mode-week flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<Transition name="picker">
			<picker-week
				v-if="weekPicker"
				ref="picker"
				:current-date="focusDate"
				@update:current-date="setWeek"
				class="position-absolute w-100 h-100"
			/>
		</Transition>
		<base-slider ref="slider" v-slot="slot">
			<week-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" mode="week" /></template>
			</week-view>
		</base-slider>
	</div>
	`
}
