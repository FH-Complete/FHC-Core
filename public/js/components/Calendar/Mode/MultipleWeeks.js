import BaseSlider from '../Base/Slider.js';
import MultipleWeeksView from './MultipleWeeks/View.js';
import FormInput from "../../Form/Input.js";

export default {
	name: "ModeMultipleWeeks",
	components: {
		BaseSlider,
		MultipleWeeksView,
		FormInput
	},
	inject: {
		rangeLength: {
			default: 30,
		},
		rangeViewPresets: {
			default: {},
		},
		rangeViewPreviewLink: {
			default: null,
		},
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
	setup() {
		const selectedRangePreset = Vue.inject(
			"rangeViewSelectedPreset",
			Vue.ref(null),
		);

		return { selectedRangePreset };
	},
	data() {
		return {
			focusDate: this.currentDate,
			rangeOffset: 0,
			beforePrintHandler: null,
			afterPrintHandler: null,
		};
	},
	computed: {
		activeRangePreset() {
			return this.rangeViewPresets?.presets?.find(
				(preset) => preset.name === this.selectedRangePreset,
			);
		},
		range() {
			const targetDate = this.focusDate.plus({ weeks: this.rangeOffset });
			const first = targetDate.startOf('week', { useLocaleWeeks: true });
			const rangeLength = Math.max(parseInt(this.rangeLength) || 1, 1);
			const last = targetDate
				.plus({ days: rangeLength - 1 })
				.endOf('week', { useLocaleWeeks: true });

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
					this.$refs.view.$refs.grids.forEach(grid => {
						grid.disableAutoScroll();
					});
					this.$emit('update:range', this.range);
					this.$refs.slider.slidePages(this.rangeOffset).then(this.updatePage);
				}
			}
		},
		rangeLength() {
			this.syncSelectedRangePreset();
			this.$emit("update:range", this.range);
		},
		activeRangePreset: {
			handler(preset) {
				if (!preset) {
					this.applyingRangePreset = false;
					return;
				}

				if (this.matchesRangePreset(preset)) {
					this.applyingRangePreset = false;
					return;
				}

				this.applyingRangePreset = true;
				this.$emit("update:date", {
					date: preset.startDate,
					rangeLength:
						preset.endDate.diff(preset.startDate, "days").days + 1,
				});
			},
			immediate: true,
		},
	},
	methods: {
		prevPage() {
			this.rangeOffset = this.$refs.slider.target - 1;

			this.$refs.view.$refs.grids.forEach(grid => {
				grid.disableAutoScroll();
			});

			this.$emit('update:range', this.range);
			this.$refs.slider.prevPage().then(this.updatePage);
		},
		nextPage() {
			this.rangeOffset = this.$refs.slider.target + 1;
			
			this.$refs.view.$refs.grids.forEach(grid => {
				grid.disableAutoScroll();
			});

			this.$emit('update:range', this.range);
			this.$refs.slider.nextPage().then(this.updatePage);
		},
		updatePage(weeks) {
			const newFocusDate = this.focusDate.plus({ weeks });
			this.focusDate = newFocusDate;
			this.rangeOffset = 0;
			this.$emit('update:currentDate', this.focusDate);
			this.$emit('update:range', this.range);
			this.$refs.view.$refs.grids.forEach(grid => {
				grid.enableAutoScroll();
			});
		},
		viewAttrs(weeks) {
			const day = this.focusDate.plus({ weeks });
			const rangeLength = parseInt(this.rangeLength);
			return { ...this.$attrs, day, rangeLength };
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
		},
		matchesRangePreset(preset) {
			const rangeLength =
				preset.endDate.diff(preset.startDate, "days").days + 1;

			return (
				this.currentDate.hasSame(preset.startDate, "day") &&
				Number(this.rangeLength) === rangeLength
			);
		},
		syncSelectedRangePreset() {
			if (!this.activeRangePreset) return;

			if (this.matchesRangePreset(this.activeRangePreset)) {
				this.applyingRangePreset = false;
				return;
			}

			if (!this.applyingRangePreset)
				this.selectedRangePreset = null;
		},
		preparePrintLayout() {
			const view = this.$el?.querySelector('.fhc-calendar-mode-week-view');
			if (!view)
				return;

			const weeks = Array.from(view.children);
			weeks.forEach(week => {
				week.style.setProperty('--fhc-calendar-print-scale', '1');
				week.style.setProperty('--fhc-calendar-print-layout-width', 'max-content');
			});


			const viewWidth = view.getBoundingClientRect().width;
			const printViewportWidth = document.querySelector(".fhc-calendar-base-slider").getBoundingClientRect().width;
			const availableWidth = printViewportWidth
				? Math.min(viewWidth, printViewportWidth)
				: viewWidth;
			if (!availableWidth)
				return;

			weeks.forEach(week => {
				const grid = week.querySelector('.fhc-calendar-base-grid');
				if (!grid)
					return;

				const gridRect = grid.getBoundingClientRect();
				const contentRight = Array.from(grid.querySelectorAll('*')).reduce(
					(right, element) => Math.max(right, element.getBoundingClientRect().right),
					gridRect.right,
				);
				const gridWidth = Math.ceil(Math.max(
					week.scrollWidth,
					grid.scrollWidth,
					gridRect.width,
					contentRight - gridRect.left,
				));
				if (!gridWidth)
					return;

				const scale = Math.min(1, availableWidth / (gridWidth));

				week.style.setProperty('--fhc-calendar-print-scale', scale.toFixed(4));
				week.style.setProperty('--fhc-calendar-print-layout-width', gridWidth + 'px');
			});
		},
		resetPrintLayout() {
			const view = this.$el?.querySelector('.fhc-calendar-mode-week-view');
			if (!view)
				return;

			Array.from(view.children).forEach(week => {
				week.style.removeProperty('--fhc-calendar-print-scale');
				week.style.removeProperty('--fhc-calendar-print-layout-width');
			});
		},
	},
	mounted() {
		this.$emit('update:range', this.range);

		this.$refs.view.$refs.grids.forEach(grid => {
			grid.enableAutoScroll();
		});

		this.beforePrintHandler = () => this.preparePrintLayout();
		this.afterPrintHandler = () => this.resetPrintLayout();
		window.addEventListener('beforeprint', this.beforePrintHandler);
		window.addEventListener('afterprint', this.afterPrintHandler);
	},
	beforeUnmount() {
		window.removeEventListener('beforeprint', this.beforePrintHandler);
		window.removeEventListener('afterprint', this.afterPrintHandler);
	},
	template: /* html */`
	<div
		class="fhc-calendar-mode-week flex-grow-1 position-relative d-flex flex-column"
		@cal-click-default.capture="handleClickDefaults"
	>
		<div ref="printMeasure" class="fhc-calendar-print-measure" aria-hidden="true"></div>
		<div
			v-if="rangeViewPresets?.presets?.length"
			id="rangePresetSelector"
			class="w-100 d-flex flex-row gap-2 justify-content-center align-items-center py-2 border-bottom"
		>
			<span v-if="rangeViewPresets?.presets?.length">{{ rangeViewPresets.label }}</span>
			<form-input
				v-if="rangeViewPresets?.presets?.length"
				name="rangePresetSelector"
				type="select"
				v-model="selectedRangePreset"
				>
				<option
					v-for="preset in rangeViewPresets.presets"
					:key="preset.name"
					:value="preset.name"
					>
					{{ preset.name }}
				</option>
			</form-input>
			<a
				v-if="rangeViewPreviewLink"
				:href="rangeViewPreviewLink"
				target="_blank"
				rel="noopener"
				class="btn btn-outline-secondary"
				aria-label="Vorschau öffnen"
				title="Vorschau öffnen"
			>
				<i class="fa-solid fa-eye"></i>
			</a>
		</div>
		<div class="flex-grow-1">
			<base-slider ref="slider" v-slot="slot">
				<multiple-weeks-view ref="view" v-bind="viewAttrs(slot.offset)">
					<template v-slot="slot"><slot v-bind="slot" mode="week" /></template>
				</multiple-weeks-view>
			</base-slider>
		</div>
	</div>
	`
}
