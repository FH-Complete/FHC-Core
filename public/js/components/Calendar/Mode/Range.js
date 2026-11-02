import BaseSlider from '../Base/Slider.js';
import RangeView from './Range/View.js';
import FormInput from '../../Form/Input.js';

export default {
	name: 'ModeRange',
	components: {
		BaseSlider,
		RangeView,
		FormInput
	},
	inject: {
		rangeLength: {
			default: 30,
		},
		rangeViewPresetsConfig: {
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
	emits: ['update:currentDate', 'update:range', 'update:date', 'click', 'requestModalOpen'],
	data() {
		return {
			selectedRangePresetChangedAt: null,
			selectedRangePreset: null,
		};
	},
	computed: {
		range() {
			const rangeLength = Math.max(parseInt(this.rangeLength) || 1, 1);
			const first = this.currentDate.startOf('week', { useLocaleWeeks: true });
			const last = this.currentDate
				.plus({ days: rangeLength - 1 })
				.endOf('week', { useLocaleWeeks: true });

			return luxon.Interval.fromDateTimes(first, last);
		}
	},
	watch: {
		currentDate() {
			this.$emit('update:range', this.range);
		},
		rangeLength() {
			this.$emit('update:range', this.range);
		},
		selectedRangePreset() {
			if (!this.selectedRangePreset) return;

			this.selectedRangePresetChangedAt = luxon.DateTime.now().toMillis();

			const preset = this.rangeViewPresetsConfig.presets.find(
				(preset) => preset.name === this.selectedRangePreset,
			);
			if (!preset) return;

			this.$emit('update:date', {
				date: preset.startDate,
				rangeLength: preset.endDate.diff(preset.startDate, 'days').days + 1,
			});

		},
		range(newRange, oldRange) {
			if (newRange.equals(oldRange)) return;

			if (this.selectedRangePresetChangedAt  > luxon.DateTime.now().minus({ milliseconds: 100 }).toMillis()) {
				return;
			}

			let matchingPreset = this.getMatchingPresetForCurrentRange();
			if (!matchingPreset) {
				this.selectedRangePreset = null;
				return;
			}

			this.selectedRangePreset = matchingPreset.name;
		}
	},
	methods: {
		getMatchingPresetForCurrentRange() {
			if (!this.rangeViewPresetsConfig?.presets?.length) return;
			if (!this.range) return;

			const matchingPreset = this.rangeViewPresetsConfig.presets.find(
				(preset) =>
					(preset.startDate >= this.range.start && preset.endDate <= this.range.end) 

			);
			if (!matchingPreset) return;
			
			return matchingPreset;
		},
		viewAttrs() {
			const day = this.$props.currentDate.startOf('day');
			const rangeLength = parseInt(this.rangeLength);
			return { ...this.$attrs, day, rangeLength };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
				case 'day':
					// default: Set current-date
					this.$emit('update:currentDate', {
						date: evt.detail.value,
					});
					break;
				case 'event':
					// default: Request Modal
					this.$emit('requestModalOpen', { event: evt.detail.value });
					break;
			}
		},
		preparePrintLayout() {
			const view = this.$el?.querySelector('.fhc-calendar-mode-range-view');
			if (!view) return;

			Array.from(view.children).forEach((week) => {
				const grid = week.querySelector('.fhc-calendar-base-grid');
				if (!grid) return;

				const gridRect = grid.getBoundingClientRect();
				const contentRight = Array.from(grid.querySelectorAll('*')).reduce(
					(right, el) => Math.max(right, el.getBoundingClientRect().right),
					gridRect.right,
				);
				const gridWidth = Math.ceil(
					Math.max(week.scrollWidth, grid.scrollWidth, gridRect.width, contentRight - gridRect.left),
				);
				if (gridWidth) {
					week.style.setProperty('--fhc-calendar-print-layout-width', gridWidth + 'px');
				}
			});
		},
		resetPrintLayout() {
			const view = this.$el?.querySelector('.fhc-calendar-mode-range-view');
			if (!view) return;
			Array.from(view.children).forEach((week) =>
				week.style.removeProperty('--fhc-calendar-print-layout-width'),
			);
		},
	},
	mounted() {
		this.$emit('update:range', this.range);

		let matchingPreset = this.getMatchingPresetForCurrentRange();
		if (matchingPreset) {
			this.selectedRangePreset = matchingPreset.name;
		}

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
		class="fhc-calendar-mode-range flex-grow-1 position-relative d-flex flex-column"
		@cal-click-default.capture="handleClickDefaults"
	>
		<div
			v-if="rangeViewPresetsConfig?.presets?.length"
			id="rangePresetSelector"
			class="w-100 d-flex flex-row gap-2 justify-content-center align-items-center py-2 border-bottom"
		>
			<span>{{ rangeViewPresetsConfig.label }}</span>
			<form-input
				name="rangePresetSelector"
				type="select"
				v-model="selectedRangePreset"
				>
				<option
					v-for="preset in rangeViewPresetsConfig.presets"
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
				<range-view ref="view" v-bind="viewAttrs(slot.offset)">
					<template v-slot="slot"><slot v-bind="slot" mode="range" /></template>
				</range-view>
			</base-slider>
		</div>
	</div>
	`
}
