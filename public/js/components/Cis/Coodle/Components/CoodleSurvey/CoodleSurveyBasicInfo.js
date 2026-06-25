import { formatDate } from "../../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurveyBasicInfo",
	props: {
		survey: Object | null,
		surveyFormDataModelValue: Object | null,
		parsedSelectedTimeslot: Object | null,
		isEditInProgress: Boolean,
	},
	emits: ["update:surveyFormDataModelValue"],
	computed: {
		formattedSurveyDescription() {
			return this.$props.survey?.description?.replaceAll("\n", "<br>");
		},
		formattedSurveyTimeslotDuration() {
			if (this.isNewSurvey) {
				return "";
			}

			const minutes = this.$props.survey.timeslotDuration % 60;
			const hours = (this.$props.survey.timeslotDuration - minutes) / 60;
			let formattedDuration = hours ? hours + " hr " : "";
			formattedDuration += minutes + " min";
			return formattedDuration;
		},
		formattedSurveyEndsAt() {
			if (this.$props.survey?.endsAt) {
				return formatDate(this.$props.survey.endsAt);
			} else {
				return "";
			}
		},
		formattedSurveyCreatedAt() {
			if (this.$props.survey?.createdAt) {
				return formatDate(this.$props.survey.createdAt);
			} else {
				return "";
			}
		},
		formattedSurveyUpdatedAt() {
			if (this.$props.survey?.updatedAt) {
				return formatDate(this.$props.survey.updatedAt);
			} else {
				return "";
			}
		},
		formattedSurveyCanceledAt() {
			if (this.$props.survey?.canceledAt) {
				return formatDate(this.$props.survey.canceledAt);
			} else {
				return "";
			}
		},
		formattedSurveyCompletedAt() {
			if (this.$props.survey?.completedAt) {
				return formatDate(this.$props.survey.completedAt);
			} else {
				return "";
			}
		},
		surveyCreatorProfileHref() {
			if (this.isNewSurvey) {
				return "";
			}

			return this.$router.resolve({
				name: "ProfilView",
				params: { uid: this.survey?.creator?.uid },
			}).href;
		},
		isNewSurvey() {
			return !this.$props.survey?.id;
		},
		surveyFormData: {
			get() {
				return this.$props.surveyFormDataModelValue;
			},
			set(newValue) {
				this.$emit("update:surveyFormDataModelValue", newValue);
			},
		},
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-3">
		<template v-if="!$props.isEditInProgress">
			<span v-if="$props.survey?.completedAt" class="fst-italic">
				{{ "This survey was completed on " + formattedSurveyCompletedAt + ". " }}
				<span v-if="$props.survey?.selectedTimeslotId">
					{{
						"The timeslot (((timeslot))) was selected.".replace(
							"(((timeslot)))",
							parsedSelectedTimeslot?.weekday +
								" " +
								parsedSelectedTimeslot?.fullDate +
								" " +
								parsedSelectedTimeslot?.startTime +
								"-" +
								parsedSelectedTimeslot?.endTime
						)
					}}
				</span>
				<span v-else>{{ "No timeslot was selected." }}</span>
			</span>
			<span v-else-if="$props.survey?.canceledAt" class="fst-italic">
				{{ "This survey was canceled on " + formattedSurveyCanceledAt + "." }}
			</span>
			<div class="d-flex flex-row gap-3 flex-wrap">
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-user fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Created by: " }}</span>
						{{ this.$props.survey?.creator?.name }}
						<a :href="surveyCreatorProfileHref" target="_blank" class="fhc-primary-color">
							<i class="fa-solid fa-up-right-from-square"></i>
						</a>
						{{ " on " + formattedSurveyCreatedAt}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-calendar fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Planned to end on: " }}</span>
						{{ formattedSurveyEndsAt }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-clock fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Appointment duration: " }}</span>
						{{ formattedSurveyTimeslotDuration }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-calendar-check fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Maximum selectable timeslots: " }}</span>
						{{ $props.survey?.maxSelections}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-user-secret fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Are participants anonymous: " }}</span>
						{{ $props.survey?.areParticipantsAnonymized ? "yes" : "no" }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-eye-slash fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Are votes anonymous: " }}</span>
						{{ $props.survey?.areSelectionsAnonymized ? "yes" : "no" }}
					</span>
				</div>
			</div>
			<span
				v-if="$props.survey?.description?.length"
				class="text-wrap fst-italic"
				v-html="formattedSurveyDescription"
			></span>
		</template>
		<template v-else>
			<div class="row">
				<div class="d-flex flex-column gap-2 col-12 col-md-6 pb-2">
					<div class="d-flex flex-column gap-1">
						<label for="surveyTitleInput" class="fw-bold">{{ "* " + "Title" }}</label>
						<input v-model="surveyFormData.title" id="surveyTitleInput" type="text" />
					</div>
					<div class="d-flex flex-column gap-1">
						<label for="surveyDescriptionInput" class="fw-bold">{{ "Description" }}</label>
						<textarea v-model="surveyFormData.description" rows="4" class="flex-grow-1" />
					</div>
				</div>
				<div class="col-12 col-md-6">
					<div class="row">
						<div class="col-12 col-xxl-6 pb-2">
							<div class="d-flex flex-column gap-1">
								<label
									for="surveyMaxSelectionsInput"
									class="fw-bold text-wrap"
								>
									{{ "Maximum number of selections" }}
								</label>
								<input
									v-model="surveyFormData.maxSelections"
									id="surveyMaxSelectionsInput"
									type="number"
									class="flex-grow-1"
								/>
							</div>
						</div>
						<div class="col-12 col-xxl-6 pb-2">
							<div class="d-flex flex-column gap-1">
								<label
									for="surveyEndsAtInput"
									class="fw-bold text-wrap"
								>
									{{ "* " + "Planned end date" }}
								</label>
								<input
									v-model="surveyFormData.endsAt"
									id="surveyEndsAtInput"
									type="date"
									class="flex-grow-1"
								/>
							</div>
						</div>
						<div class="col-12">
							<div class="row">
								<div class="col-12 col-lg-6 d-flex flex-row align-items-center gap-2 pb-2">
									<label
										for="surveyAreParticipantsAnonymizedInput"
										class="fw-bold text-wrap"
									>
										{{ "Are participants anonymous?" }}
									</label>
									<input
										v-model="surveyFormData.areParticipantsAnonymized"
										id="surveyAreParticipantsAnonymizedInput"
										type="checkbox"
										style="height:15px;width:15px;"
									/>
								</div>
								<div class="col-12 col-lg-6 d-flex flex-row align-items-center gap-2 pb-2">
									<label
										for="surveyAreSelectionsAnonymizedInput"
										class="fw-bold text-wrap"
									>
										{{ "Are votes anonymous?" }}
									</label>
									<input
										v-model="surveyFormData.areSelectionsAnonymized"
										id="surveyAreSelectionsAnonymizedInput"
										type="checkbox"
										style="height:15px;width:15px;"
									/>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</template>
	</div>
	`,
};
