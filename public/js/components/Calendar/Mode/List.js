import BaseSlider from '../Base/Slider.js';
import ListView from './List/View.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

export default {
	name: "ModeList",
	components: {
		BaseSlider,
		ListView
	},
	inject: {
		locale: "locale",
		title: "title"
	},
	props: {
		currentDate: Number,
		length: {
			type: Number,
			default: 7
		}
	},
	emits: [
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

			range.first = new Date(this.focusDate);
			range.last = CalendarDate.addDays(range.first, this.length);

			if (this.rangeOffset != 0) {
				if (this.rangeOffset < 0) {
					range.first = CalendarDate.addDays(range.first, this.rangeOffset * this.length);
				} else {
					range.last = CalendarDate.addDays(range.last, this.rangeOffset * this.length);
				}
			}

			return range;
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
			const newFocusDate = this.focusDate + offset * this.length * CalendarDate.msPerDay;
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', new Date(this.focusDate));
			this.$emit('update:range', this.range);
		},
		viewAttrs(offset) {
			const showDate = this.focusDate + offset * this.length * CalendarDate.msPerDay;
			return {
				day: showDate,
				length: this.length
			}
		}
	},
	created() {
		this.title = Vue.computed(() => {

			if (this.range.first.getFullYear() != this.range.last.getFullYear()) {
				return CalendarDate.format(
					this.range.first,
					{ day: "numeric", month: "short", year: "numeric" },
					this.locale
				) + ' - ' +
				CalendarDate.format(
					this.range.last,
					{ day: "numeric", month: "short", year: "numeric" },
					this.locale
				);
			}
			if (this.range.first.getMonth() != this.range.last.getMonth()) {
				const helperdate = new Date(this.range.first.getFullYear() + "-02-01 00:00");
				const templateAll = CalendarDate.format(
					helperdate,
					{ day: "numeric", month: "short", year: "numeric" },
					this.locale
				);
				const templatePart = CalendarDate.format(
					helperdate,
					{ day: "numeric", month: "short" },
					this.locale
				);
				return templateAll.replace(
					templatePart,
					CalendarDate.format(
						this.range.first,
						{ day: "numeric", month: "short" },
						this.locale
					) + ' - ' +
					CalendarDate.format(
						this.range.last,
						{ day: "numeric", month: "short" },
						this.locale
					)
				);
			}
			const template = CalendarDate.format(
				new Date("2000-" + (this.range.first.getMonth()+1) + "-01 00:00:00"),
				{ day: "numeric", month: "short", year: "numeric" },
				this.locale
			);
			return template.replace(
				'1',
				CalendarDate.format(
					this.range.first,
					{ day: "numeric" },
					this.locale
				) + ' - ' +
				CalendarDate.format(
					this.range.last,
					{ day: "numeric" },
					this.locale
				)
			).replace('2000', this.range.first.getFullYear());
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
		class="fhc-calendar-mode-list flex-grow-1 position-relative"
	>
		<base-slider ref="slider" v-slot="slot">
			<list-view v-bind="viewAttrs(slot.offset)">
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</list-view>
		</base-slider>
	</div>
	`
}
