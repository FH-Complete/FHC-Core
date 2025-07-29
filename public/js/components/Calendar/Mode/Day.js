import BaseSlider from '../Base/Slider.js';
import DayView from './Day/View.js';

export default {
	name: "ModeDay",
	components: {
		BaseSlider,
		DayView
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
		"requestModalOpen",
		"requestModalClose"
	],
	data() {
		return {
			focusDate: this.currentDate,
			rangeOffset: 0
		};
	},
	computed: {
		range() {
			let first = this.focusDate.startOf('day');
			let last = this.focusDate.endOf('day');
			
			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					first = first.plus({ days: this.rangeOffset });
				} else {
					last = last.plus({ days: this.rangeOffset });
				}
			}

			return luxon.Interval.fromDateTimes(first, last);
		}
	},
	watch: {
		currentDate() {
			this.rangeOffset = this.currentDate.startOf('day').diff(this.focusDate.startOf('day'), 'days').days;
			if (this.rangeOffset) {
				this.$refs.view.$refs.grid.disableAutoScroll();
				this.$emit('update:range', this.range);
				this.$refs.slider.slidePages(this.rangeOffset).then(this.updatePage);
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
		updatePage(days) {
			const newFocusDate = this.focusDate.plus({ days });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
			this.$refs.view.$refs.grid.enableAutoScroll();
		},
		viewAttrs(days) {
			const day = this.focusDate.plus({ days });
			return { ...this.$attrs, day };
		}
	},
	mounted() {
		this.$emit('update:range', this.range);
		this.$refs.view.$refs.grid.enableAutoScroll();
	},
	template: `
	<div
		class="fhc-calendar-mode-day flex-grow-1 position-relative"
	>
		<base-slider ref="slider" v-slot="slot">
			<day-view
				ref="view"
				v-bind="viewAttrs(slot.offset)"
				@request-modal-open="$emit('requestModalOpen', $event)"
				@request-modal-close="$emit('requestModalClose', $event)"
			>
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</day-view>
		</base-slider>
	</div>
	`
}
