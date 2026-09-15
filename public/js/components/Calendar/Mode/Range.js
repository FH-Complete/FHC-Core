import BaseSlider from "../Base/Slider.js";
import RangeView from "./Range/View.js";
import FormInput from "../../Form/Input.js";

export default {
	name: "ModeRange",
	components: {
		BaseSlider,
		RangeView,
		FormInput,
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
			required: true,
		},
	},
	emits: ["update:currentDate", "update:date", "update:range", "click", "requestModalOpen"],
	setup() {
		const selectedRangePreset = Vue.inject(
			"rangeViewSelectedPreset",
			Vue.ref(null),
		);

		return { selectedRangePreset };
	},
	data() {
		return {
			applyingRangePreset: false,
		};
	},
	computed: {
		activeRangePreset() {
			return this.rangeViewPresets?.presets?.find(
				(preset) => preset.name === this.selectedRangePreset,
			);
		},
		range() {
			const first = this.$props.currentDate.startOf("day");
			const last = first
				.plus({ days: Math.max(Number(this.rangeLength) - 1, 0) })
				.endOf("day");

			return luxon.Interval.fromDateTimes(first, last);
		},
	},
	watch: {
		currentDate() {
			this.syncSelectedRangePreset();
			this.$emit("update:range", this.range);
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
		viewAttrs() {
			const day = this.$props.currentDate.startOf("day");
			const rangeLength = parseInt(this.rangeLength);
			return { ...this.$attrs, day, rangeLength };
		},
		handleClickDefaults(evt) {
			switch (evt.detail.source) {
				case "day":
					// default: Set current-date
					this.$emit("update:currentDate", {
						date: evt.detail.value,
					});
					break;
				case "event":
					// default: Request Modal
					this.$emit("requestModalOpen", { event: evt.detail.value });
					break;
			}
		},
	},
	mounted() {
		this.$emit("update:range", this.range);
	},
	template: /*html*/ `
	<div
		class="fhc-calendar-mode-range flex-grow-1 position-relative d-flex flex-column"
		@cal-click-default.capture="handleClickDefaults"
	>
		<div
			v-if="rangeViewPresets?.presets?.length || rangeViewPreviewLink"
			id="rangePresetSelector"
			class="w-100 d-flex flex-row gap-2 justify-content-center align-items-center py-2"
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
				<range-view ref="view" v-bind="viewAttrs()">
					<template v-slot="slot"><slot v-bind="slot" mode="range" /></template>
				</range-view>
			</base-slider>
		</div>
	</div>
	`,
};
