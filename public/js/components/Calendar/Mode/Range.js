import BaseSlider from '../Base/Slider.js';
import RangeView from './Range/View.js';

export default {
	name: "ModeRange",
	components: {
		BaseSlider,
		RangeView
	},
	inject: {
		rangeLength: {default: 30}
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
	computed: {
		range() {
			let first = this.$props.currentDate;
			let last = first.plus({days: this.rangeLength});

			return luxon.Interval.fromDateTimes(first, last);
		}
	},
	watch: {
		currentDate() {
			this.$emit('update:range', this.range);
		},
		currentDate() {
			this.$emit('update:range', this.range);
		},
	},
	methods: {
		viewAttrs() {
			const day = this.$props.currentDate.startOf("day");
			const rangeLength = this.rangeLength;
			return { ...this.$attrs, day, rangeLength };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
			case 'day':
				// default: Set current-date
				this.$emit('update:currentDate', {date: evt.detail.value});
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
		class="fhc-calendar-mode-range flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<base-slider ref="slider" v-slot="slot">
			<range-view ref="view" v-bind="viewAttrs()">
				<template v-slot="slot"><slot v-bind="slot" mode="range" /></template>
			</range-view>
		</base-slider>
	</div>
	`
}
