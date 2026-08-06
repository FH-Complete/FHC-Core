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
	methods: {
		showCreatorProfile() {
			if (this.isNewSurvey || !this.survey?.creator?.uid) {
				return;
			}

			const creatorHref = this.$router.resolve({
				name: "ProfilView",
				params: { uid: this.survey?.creator?.uid },
			}).href;
			window.open(creatorHref, "_blank");
		},
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-3">
		<template v-if="!$props.isEditInProgress">
			<span v-if="$props.survey?.completedAt" class="fst-italic">
				{{
					$p.t("coodle/survey_completed_info").replace(
						'(((completedAt)))',
						formattedSurveyCompletedAt
					)
				}}
				<span v-if="$props.survey?.selectedTimeslotId">
					{{
						$p.t("coodle/selected_timeslot_info").replace(
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
				<span v-else>{{ $p.t("coodle/no_selected_timeslot_info") }}</span>
			</span>
			<span v-else-if="$props.survey?.canceledAt" class="fst-italic">
				{{
					$p.t("coodle/survey_canceled_info").replace(
						'(((canceledAt)))',
						formattedSurveyCanceledAt
					)
				}}
			</span>
			<div class="d-flex flex-row gap-3 flex-wrap">
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-user fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/created_by") + ": " }}</span>
						{{ this.$props.survey?.creator?.name }}
						<span
							v-if="this.$props.survey?.creator?.uid"
							@click="showCreatorProfile()"
							type="button"
							class="fhc-primary-color"
						>
							<i class="fa-solid fa-up-right-from-square"></i>
						</span>
						{{ " " + $p.t("ui/am") + " " + formattedSurveyCreatedAt}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-calendar fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/planned_end_date") + ": " }}</span>
						{{ formattedSurveyEndsAt }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-clock fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/timeslot_duration") + ": " }}</span>
						{{ formattedSurveyTimeslotDuration }}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-calendar-check fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/max_selectable_timeslots") + ": " }}</span>
						{{ $props.survey?.maxSelections}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-user-secret fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/anon_participants") + ": " }}</span>
						{{ $props.survey?.areParticipantsAnonymized ? $p.t("coodle/yes") : $p.t("coodle/no")}}
					</span>
				</div>
				<div class="d-flex flex-row">
					<div class="d-flex flex-row justify-content-center align-items-center" style="width: 30px;">
						<i class="fa-solid fa-eye-slash fa-lg"></i>
					</div>
					<span>
						<span class="fw-bold">{{ $p.t("coodle/anon_votes") + ": " }}</span>
						{{ $props.survey?.areSelectionsAnonymized ? $p.t("coodle/yes") : $p.t("coodle/no") }}
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
						<label for="surveyTitleInput" class="fw-bold">{{ "* " + $p.t("coodle/title") }}</label>
						<input v-model="surveyFormData.title" id="surveyTitleInput" type="text" maxlength="255" />
					</div>
					<div class="d-flex flex-column gap-1">
						<label for="surveyDescriptionInput" class="fw-bold">{{ $p.t("coodle/description") }}</label>
						<textarea v-model="surveyFormData.description" rows="4" class="flex-grow-1" maxlength="1000" />
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
									{{ $p.t("coodle/max_selectable_timeslots") }}
								</label>
								<input
									v-model="surveyFormData.maxSelections"
									id="surveyMaxSelectionsInput"
									type="number"
									min="1"
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
									{{ "* " + $p.t("coodle/planned_end_date") }}
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
										{{ $p.t("coodle/anon_participants") + "?" }}
									</label>
									<input
										v-model="surveyFormData.areParticipantsAnonymized"
										id="surveyAreParticipantsAnonymizedInput"
										type="checkbox"
										style="min-height:15px;min-width:15px;"
									/>
								</div>
								<div class="col-12 col-lg-6 d-flex flex-row align-items-center gap-2 pb-2">
									<label
										for="surveyAreSelectionsAnonymizedInput"
										class="fw-bold text-wrap"
									>
										{{ $p.t("coodle/anon_votes") + "?" }}
									</label>
									<input
										v-model="surveyFormData.areSelectionsAnonymized"
										id="surveyAreSelectionsAnonymizedInput"
										type="checkbox"
										style="min-height:15px;min-width:15px;"
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
