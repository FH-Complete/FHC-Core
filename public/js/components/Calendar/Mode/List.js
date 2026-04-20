import BaseSlider from '../Base/Slider.js';
import ListView from './List/View.js';

export default {
	name: "ModeList",
	components: {
		BaseSlider,
		ListView
	},
	props: {
		currentDate: {
			type: luxon.DateTime,
			required: true
		},
		length: {
			type: Number,
			default: 7
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
			let first = this.focusDate;
			let last = first.plus({ days: this.length });

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					first = first.plus({ days: this.rangeOffset });
				} else {
					last = first.plus({ days: this.rangeOffset + this.length });
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
				this.rangeOffset = this.currentDate.startOf('day').diff(this.focusDate.startOf('day'), 'days').days;
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
		updatePage(offset) {
			const newFocusDate = this.focusDate.plus({ days: offset });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
		},
		viewAttrs(offset) {
			const day = this.focusDate.plus({ days: offset });
			return { day, length: this.length };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
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
		class="fhc-calendar-mode-list flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<base-slider ref="slider" v-slot="slot">
			<list-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</list-view>
		</base-slider>
	</div>
	`
}
