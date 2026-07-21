import { numberPadding } from "../../../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurveyCalendarTimeslotCard",
	components: {
		VueDatePicker,
	},
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
			const tomorrow = new Date(
				new Date().getTime() + 24 * 60 * 60 * 1000,
			);
			this.dateInput =
				this.$props.timeslot?.datum ??
				tomorrow.toISOString().slice(0, 10);
			this.startTimeInput =
				this.$props.timeslot?.beginn?.slice(0, 5) ?? "12:00";
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
				this.$fhcAlert.alertError("Check your inputs!");
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
				this.$fhcAlert.alertError(
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
		updateDateInput(value) {
			let date = new Date(value);
			this.dateInput = date.toISOString().slice(0, 10);
		},
		getDateInputPreview() {
			if (!this.dateInput?.length) return "";

			const day = this.dateInput.slice(8);
			const month = this.dateInput.slice(5, 7);
			const year = this.dateInput.slice(0, 4);

			return day + "." + month + "." + year;
		},
		updateStartTimeInput(value) {
			this.startTimeInput =
				numberPadding(value.hours) + ":" + numberPadding(value.minutes);
		},
		getStartTimeInputPreview() {
			if (!this.startTimeInput?.length) return "";

			const hours = this.startTimeInput.slice(0, 2);
			const minutes = this.startTimeInput.slice(3, 5);

			return hours + ":" + minutes;
		},
		getInitialStartTimeValue() {
			if (!this.startTimeInput?.length) {
				return {
					hours: 12,
					minutes: 0,
				};
			}

			return {
				hours: parseInt(this.startTimeInput.split(":")[0]),
				minutes: parseInt(this.startTimeInput.split(":")[1]),
			};
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
					<div class="d-flex flex-row align-items-center gap-2">
						<label :for="'dateInput-' + $props.timeslot?.isostart" class="fw-bold">{{ "Date: " }}</label>
						<vue-date-picker
							@update:model-value="updateDateInput($event)"
							:model-value="dateInput"
							:format="() => getDateInputPreview()"
							:text-input="true"
							:clearable="false"
							:enable-time-picker="false"
							:now-button-label="$p.t('calendar/today')"
							:week-num-name="$p.t('calendar/kw')"
							auto-apply
							six-weeks
							teleport
						/>
					</div>
					<div class="d-flex flex-row gap-2">
						<label :for="'startTimeInput-' + $props.timeslot?.isostart" class="fw-bold">{{ "Start time: " }}</label>
						<vue-date-picker
							@update:model-value="updateStartTimeInput($event)"
							:model-value="getInitialStartTimeValue()"
							:format="() => getStartTimeInputPreview()"
							:text-input="true"
							:clearable="false"
							:minutes-increment="5"
							:min-time="{ hours: 7, minutes: 0 }"
							:max-time="{ hours: 22, minutes: 55 }"
							time-picker
							teleport
						/>
					</div>
				</div>
				<div class="d-flex flex-row align-items-start justify-content-end">
					<template v-if="!isEditInProgress">
						<div
							@click="startEdit()"
							:title="'Edit'"
							type="button"
							class="p-1 fhcPrimaryHover"
						>
							<i class="fa-solid fa-pen-to-square fa-lg"></i>
						</div>
						<div
							@click="$emit('deleteTimeslot')"
							:title="'Delete'"
							type="button"
							class="p-1 fhcPrimaryHover"
						>
							<i class="fa-solid fa-trash-can fa-lg"></i>
						</div>
					</template>
					<template v-else>
						<div
							@click="cancelEdit()"
							:title="'Cancel'"
							type="button"
							class="p-1 fhcPrimaryHover"
						>
							<i class="fa-regular fa-circle-left fa-lg"></i>
						</div>
						<div
							@click="submitForm()"
							:title="'Save'"
							type="button"
							class="p-1 fhcPrimaryHover"
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
					class="fhcPrimaryHover fa-solid fa-circle-plus fa-2xl"
				></i>
			</div>
		</div>
	</div>
	`,
};
