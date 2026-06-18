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
			return this.$router.resolve({
				name: "ProfilView",
				params: { uid: this.survey?.creator?.uid },
			}).href;
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
			<span
				v-if="$props.survey?.description?.length"
				class="text-wrap"
				v-html="formattedSurveyDescription"
			></span>
			<div class="d-flex flex-column gap-1">
				<div>
					{{ "Created by " }}
					<a :href="surveyCreatorProfileHref" :target="'_blank'">{{ this.$props.survey?.creator?.name }}</a>
					{{ " on " + formattedSurveyCreatedAt}}
				</div>
				<div>{{ "Last edited on " + formattedSurveyUpdatedAt }}</div>
				<div>{{ "Planned to end on " + formattedSurveyEndsAt }}</div>
			</div>
			<div class="d-flex flex-column gap-1">
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-clock fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Proposed appointment duration: " }}</span>
						{{ formattedSurveyTimeslotDuration }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-calendar-check fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Maximum selectable timeslots: " }}</span>
						{{ $props.survey.maxSelections}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-user-slash fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Are participants anonymized: " }}</span>
						{{ $props.survey.areParticipantsAnonymized ? "yes" : "no" }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-person-booth fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ "Are votes anonymized: " }}</span>
						{{ $props.survey.areSelectionsAnonymized ? "yes" : "no" }}
					</span>
				</div>
			</div>
		</template>
		<template v-else>
			<span class="fst-italic">{{ "Required inputs are marked with *." }}</span>
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
									class="fw-bold text-nowrap text-truncate"
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
									class="fw-bold text-nowrap text-truncate"
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
										class="fw-bold text-nowrap text-truncate"
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
										class="fw-bold text-nowrap text-truncate"
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
