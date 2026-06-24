import { numberPadding } from "../../../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurveyCalendarTimeslotCard",
	props: {
		timeslot: Object | null,
		isTimeslotCardEditInProgress: Boolean,
	},
	emits: [
		"deleteTimeslot",
		"updateTimeslot",
		"setIsTimeslotCardEditInProgress",
	],
	data() {
		return {
			isEditInProgress: false,
			dateInput: null,
			startTimeInput: null,
			minStartTimeInput: "07:00",
			maxStartTimeInput: "22:55",
		};
	},
	computed: {
		formattedDate() {
			let dateFragments = this.$props.timeslot.datum.split("-");
			return dateFragments[2] + "." + dateFragments[1] + ".";
		},
		formattedStartEnd() {
			return (
				this.$props.timeslot?.beginn?.slice(0, 5) +
				"-" +
				this.$props.timeslot?.ende?.slice(0, 5)
			);
		},
	},
	watch: {
		timeslot: {
			handler() {
				this.isEditInProgress = false;
				this.setInputs();
			},
			deep: true,
		},
		isEditInProgress() {
			this.setInputs();
		},
		isTimeslotCardEditInProgress() {
			this.isEditInProgress = false;
		},
	},
	methods: {
		setInputs() {
			this.dateInput = this.$props.timeslot?.datum;
			this.startTimeInput = this.$props.timeslot?.beginn?.slice(0, 5);
		},
		async startEdit() {
			if (this.$props.isTimeslotCardEditInProgress) {
				this.$emit("setIsTimeslotCardEditInProgress", { value: false });
			}
			await this.$nextTick();
			this.$emit("setIsTimeslotCardEditInProgress", { value: true });
			await this.$nextTick();
			this.isEditInProgress = true;
		},
		cancelEdit() {
			this.isEditInProgress = false;
			this.$emit("setIsTimeslotCardEditInProgress", { value: false });
		},
		submitForm() {
			if (!this.dateInput || !this.startTimeInput) {
				window.alert("Check your inputs!");
				return;
			}

			if (
				this.dateInput === this.$props.timeslot?.datum &&
				this.startTimeInput ===
					this.$props.timeslot?.beginn?.slice(0, 5)
			) {
				this.isEditInProgress = false;
				return;
			}

			if (
				this.startTimeInput < this.minStartTimeInput ||
				this.startTimeInput > this.maxStartTimeInput
			) {
				window.alert(
					"Appointment start time must be between 07:00 and 22:55!",
				);
				return;
			}

			let minutes = parseInt(this.startTimeInput.slice(3));
			if (minutes % 5) {
				minutes = minutes - (minutes % 5);
				this.startTimeInput =
					this.startTimeInput.slice(0, 3) + numberPadding(minutes);
			}

			let startDate = new Date(
				this.dateInput + " " + this.startTimeInput,
			);
			if (this.$props.timeslot) {
				this.$emit("updateTimeslot", { newStartDate: startDate });
			} else {
				this.$emit("createTimeslot", { startDate });
			}
		},
	},
	created() {
		this.setInputs();
	},
	template: /*html*/ `
	<div class="w-100 p-2" style="height: 120px;">
		<div class="coodleCalendarTimeslotCard h-100 d-flex flex-row justify-content-between px-3 py-2 border border-2 rounded-2">
			<template v-if="$props.timeslot || isEditInProgress">
				<div
					v-if="!isEditInProgress"
					class="d-flex flex-column gap-2 justify-content-center align-items-center flex-grow-1"
				>
					<span class="fs-5 fw-bold">{{ formattedDate }}</span>
					<span>{{ formattedStartEnd }}</span>
				</div>
				<div v-else class="d-flex flex-column gap-2 flex-grow-1 justify-content-center pe-3">
					<div class="d-flex flex-row gap-2">
						<label :for="'dateInput-' + $props.timeslot?.isostart" class="fw-bold">{{ "Date: " }}</label>
						<input v-model="dateInput" type="date" class="flex-grow-1" />
					</div>
					<div class="d-flex flex-row gap-2">
						<label :for="'startTimeInput-' + $props.timeslot?.isostart" class="fw-bold">{{ "Start time: " }}</label>
						<input
							v-model="startTimeInput"
							type="time"
							step="300"
							:min="minStartTimeInput"
							:max="maxStartTimeInput"
							class="flex-grow-1"
						/>
					</div>
				</div>
				<div class="d-flex flex-row align-items-start justify-content-end">
					<template v-if="!isEditInProgress">
						<div
							@click="startEdit()"
							:title="'Edit'"
							type="button"
							class="p-1 coodleCalendarTimeslotCardIcon"
						>
							<i class="fa-solid fa-pen-to-square fa-lg"></i>
						</div>
						<div
							@click="$emit('deleteTimeslot')"
							:title="'Delete'"
							type="button"
							class="p-1 coodleCalendarTimeslotCardIcon"
						>
							<i class="fa-solid fa-trash-can fa-lg"></i>
						</div>
					</template>
					<template v-else>
						<div
							@click="cancelEdit()"
							:title="'Cancel'"
							type="button"
							class="p-1 coodleCalendarTimeslotCardIcon"
						>
							<i class="fa-regular fa-circle-left fa-lg"></i>
						</div>
						<div
							@click="submitForm()"
							:title="'Save'"
							type="button"
							class="p-1 coodleCalendarTimeslotCardIcon"
						>
							<i class="fa-solid fa-floppy-disk fa-lg"></i>
						</div>
					</template>
				</div>
			</template>
			<div v-else class="flex-grow-1 d-flex flex-row justify-content-center align-items-center">
				<i
					@click="startEdit()"
					:title="'Add'"
					type="button"
					class="coodleCalendarTimeslotCardIcon fa-solid fa-circle-plus fa-2xl"
				></i>
			</div>
		</div>
	</div>
	`,
};
