import BaseSlider from '../Base/Slider.js';
import WeekView from './Week/View.js';

export default {
	name: "ModeWeek",
	components: {
		BaseSlider,
		WeekView
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
			let first = this.focusDate.startOf('week', { useLocaleWeeks: true });
			let last = this.focusDate.endOf('week', { useLocaleWeeks: true });

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					first = first.plus({ weeks: this.rangeOffset });
				} else {
					last = last.plus({ weeks: this.rangeOffset });
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
				this.rangeOffset = this.currentDate.startOf('week', { useLocaleWeeks: true }).diff(this.focusDate.startOf('week', { useLocaleWeeks: true }), 'weeks').weeks;
				if (this.rangeOffset) {
					this.$refs.view.$refs.grid.disableAutoScroll();
					this.$emit('update:range', this.range);
					this.$refs.slider.slidePages(this.rangeOffset).then(this.updatePage);
				}
			}
		}
	},
	methods: {
		prevPage() {
			this.rangeOffset = this.$refs.slider.target - 1;
			this.$refs.view.$refs.grid.disableAutoScroll();
			this.$emit('update:range', this.range);
			this.$refs.slider.prevPage().then(this.updatePage);
		},
		nextPage() {
			this.rangeOffset = this.$refs.slider.target + 1;
			this.$refs.view.$refs.grid.disableAutoScroll();
			this.$emit('update:range', this.range);
			this.$refs.slider.nextPage().then(this.updatePage);
		},
		updatePage(weeks) {
			const newFocusDate = this.focusDate.plus({ weeks });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
			this.$refs.view.$refs.grid.enableAutoScroll();
		},
		viewAttrs(weeks) {
			const day = this.focusDate.plus({ weeks });
			return { ...this.$attrs, day };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
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
		this.$refs.view.$refs.grid.enableAutoScroll();
	},
	template: `
	<div
		class="fhc-calendar-mode-week flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<base-slider ref="slider" v-slot="slot">
			<week-view ref="view" v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" mode="week" /></template>
			</week-view>
		</base-slider>
	</div>
	`
}
