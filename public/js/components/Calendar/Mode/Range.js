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
	},
	props: {
		currentDate: {
			type: luxon.DateTime,
			required: true,
		},
	},
	emits: ["update:currentDate", "update:range", "click", "requestModalOpen"],
	data() {
		return {
			selectedRangePreset: null,
		};
	},
	computed: {
		range() {
			let first = this.$props.currentDate;
			let last = first.plus({ days: this.rangeLength });

			return luxon.Interval.fromDateTimes(first, last);
		},
	},
	watch: {
		currentDate() {
			this.$emit("update:range", this.range);
		},
		rangeLength() {
			this.$emit("update:range", this.range);
		},
		selectedRangePreset() {
			if (!this.selectedRangePreset) return;

			const preset = this.rangeViewPresets.presets.find(
				(preset) => preset.name === this.selectedRangePreset,
			);
			if (!preset) return;

			this.$emit("update:date", {
				date: preset.startDate,
				rangeLength:
					preset.endDate.diff(preset.startDate, "days").days + 1,
			});

			this.selectedRangePreset = null;
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
		class="fhc-calendar-mode-range flex-grow-1 position-relative"
		@cal-click-default.capture="handleClickDefaults"
	>
		<div
			v-if="rangeViewPresets?.presets?.length"
			class="w-100 d-flex flex-row gap-2 justify-content-center align-items-center py-2"
		>
			<span>{{ rangeViewPresets.label }}</span>
			<form-input
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
		</div>
		<base-slider ref="slider" v-slot="slot">
			<range-view ref="view" v-bind="viewAttrs()">
				<template v-slot="slot"><slot v-bind="slot" mode="range" /></template>
			</range-view>
		</base-slider>
	</div>
	`,
};
