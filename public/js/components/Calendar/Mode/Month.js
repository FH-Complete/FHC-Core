import BaseSlider from '../Base/Slider.js';
import MonthView from './Month/View.js';

export default {
	name: "ModeMonth",
	components: {
		BaseSlider,
		MonthView
	},
	props: {
		currentDate: {
			type: luxon.DateTime,
			required: true
		}
	},
	emits: [
		"update:currentDate",
		"update:range",
		"click",
		"requestModalOpen"
	],
	data() {
		return {
			focusDate: this.currentDate,
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			let first = this.focusDate.startOf('month').startOf('week', { useLocaleWeeks: true });
			let last = first.plus({ days: 41 }).endOf('day'); // NOTE(chris): 6 weeks minus 1 day

			if (this.rangeOffset != 0) {
				const nextFocusDate = this.focusDate.plus({ months: this.rangeOffset});
				const nextRangeStart = nextFocusDate.startOf('month').startOf('week', { useLocaleWeeks: true });
				if (this.rangeOffset < 0) {
					first = nextRangeStart;
				} else {
					last = nextRangeStart.plus({ days: 41 }).endOf('day');
				}
			}

			return luxon.Interval.fromDateTimes(first, last);
		}
	},
	watch: {
		currentDate() {
			if (this.currentDate.locale != this.focusDate.locale) {
				this.focusDate = this.currentDate;
				this.$emit('update:range', this.range);
			} else {
				this.rangeOffset = this.currentDate.startOf('month').diff(this.focusDate.startOf('month'), 'months').months;
				if (this.rangeOffset) {
					this.$emit('update:range', this.range);
					this.$refs.slider.slidePages(this.rangeOffset).then(this.updatePage);
				}
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
				let dayInWeek = luxon.DateTime.fromObject({
					localWeekNumber: evt.detail.value.number,
					localWeekYear: evt.detail.value.year
				}, {
					zone: this.currentDate.zoneName,
					locale: this.currentDate.locale
				});

				if (!this.focusDate.hasSame(dayInWeek.startOf('week', { useLocaleWeeks: true }), 'month')) {
					this.$emit('update:currentDate', dayInWeek.startOf('week', { useLocaleWeeks: true }));
				} else if (!this.focusDate.hasSame(dayInWeek.endOf('week', { useLocaleWeeks: true }), 'month')) {
					this.$emit('update:currentDate', dayInWeek.endOf('week', { useLocaleWeeks: true }));
				}
				break;
			case 'day':
				// default: Set current-date
				this.$emit('update:currentDate', evt.detail.value);
				break;
			case 'event':
				// default: Request Modal
				this.$emit('requestModalOpen', { event: evt.detail.value });
				break;
			}
		}
	},
	mounted() {
		this.$emit('update:range', this.range);
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
