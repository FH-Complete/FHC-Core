import BaseSlider from '../Base/Slider.js';
//import PickerWeek from '../Picker/Week.js';
import WeekView from './Week/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "ModeWeek",
	components: {
		BaseSlider,
		//PickerWeek,
		WeekView
	},
	inject: {
		locale: "locale",
		timezone: "timezone",
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
			focusDate: luxon.DateTime.fromMillis(this.currentDate.ts).setZone(this.timezone),
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			const range = {};

			range.first = this.focusDate.startOf('week');
			range.last = this.focusDate.endOf('week');

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					range.first = range.first.plus({ weeks: this.rangeOffset });
				} else {
					range.last = range.last.plus({ weeks: this.rangeOffset });
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
			this.rangeOffset = this.currentDate.startOf('week').diff(this.focusDate.startOf('week'), 'weeks').weeks;
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
		updatePage(weeks) {
			const newFocusDate = this.focusDate.plus({ weeks });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
		},
		viewAttrs(weeks) {
			const showDate = this.focusDate.plus({ weeks });
			return {
				week: showDate.localWeekNumber,
				year: showDate.localWeekYear
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
		<base-slider ref="slider" v-slot="slot">
			<week-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" mode="week" /></template>
			</week-view>
		</base-slider>
	</div>
	`
}
