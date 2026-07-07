import CoodleSurveyVotingTable from "./CoodleSurvey/CoodleSurveyVotingTable.js";
import CoodleSurveyCalendar from "./CoodleSurvey/CoodleSurveyCalendar.js";
import CoodleSurveyParticipants from "./CoodleSurvey/CoodleSurveyParticipants.js";
import CoodleSurveyDurationSelector from "./CoodleSurvey/CoodleSurveyDurationSelector.js";
import CoodleSurveyBasicInfo from "./CoodleSurvey/CoodleSurveyBasicInfo.js";
import CoodleSurveyHeader from "./CoodleSurvey/CoodleSurveyHeader.js";

import { formatDate, numberPadding } from "../../../../helpers/DateHelpers.js";

import CoodleApi from "../../../../api/factory/coodle.js";

export default {
	name: "CoodleSurvey",
	components: {
		CoodleSurveyVotingTable,
		CoodleSurveyCalendar,
		CoodleSurveyParticipants,
		CoodleSurveyDurationSelector,
		CoodleSurveyBasicInfo,
		CoodleSurveyHeader,
	},
	props: {
		survey: { type: Object | null },
		uid: { type: String | null },
	},
	emits: ["surveyCreationCanceled", "surveyCreated", "surveyUpdated"],
	data() {
		return {
			surveyFormData: null,
			participantScheduleColors: [
				{
					uid: null,
					color: "#FF0000",
				},
				{
					uid: null,
					color: "#00FF00",
				},
				{
					uid: null,
					color: "#0000FF",
				},
				{
					uid: null,
					color: "#FF00FF",
				},
				{
					uid: null,
					color: "#00FFFF",
				},
				{
					uid: null,
					color: "#FFFF00",
				},
				{
					uid: null,
					color: "#FFA500",
				},
				{
					uid: null,
					color: "#800080",
				},
				{
					uid: null,
					color: "#228B22",
				},
				{
					uid: null,
					color: "#B87333",
				},
			],
			isEditInProgress: false,
			weekdays: [
				"Sunday",
				"Monday",
				"Tuesday",
				"Wednesday",
				"Thursday",
				"Friday",
				"Saturday",
			],
			months: [
				"January",
				"February",
				"March",
				"April",
				"May",
				"June",
				"July",
				"August",
				"September",
				"October",
				"November",
				"December",
			],
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
		parsedSelectedTimeslot() {
			if (!this.$props.survey?.selectedTimeslotId) {
				return null;
			}

			const selectedTimeslot = this.$props.survey.timeslots.find(
				(timeslot) =>
					timeslot.id === this.$props.survey.selectedTimeslotId,
			);
			if (!selectedTimeslot) {
				return null;
			}

			return this.parseTimeslotForDisplay(selectedTimeslot);
		},
		parsedTimeslotsForVotingTable() {
			return (
				this.$props.survey?.timeslots
					.sort((timeslotA, timeslotB) => {
						return timeslotA.startsAt < timeslotB.startsAt ? -1 : 1;
					})
					.map((timeslot) =>
						this.parseTimeslotForDisplay(timeslot),
					) ?? []
			);
		},
	},
	watch: {
		survey: {
			async handler() {
				this.setSurveyFormData();
				if (!this.$props.survey?.id) {
					this.isEditInProgress = true;
				} else {
					this.isEditInProgress = false;
				}
			},
			deep: true,
		},
		isEditInProgress() {
			if (!this.isEditInProgress) {
				this.setSurveyFormData();
			}
		},
	},
	methods: {
		setSurveyFormData() {
			if (this.$props.survey) {
				this.surveyFormData = { ...this.$props.survey };
			} else {
				let defaultEndsAtDate = new Date();
				defaultEndsAtDate.setDate(defaultEndsAtDate.getDate() + 7);

				this.surveyFormData = {
					id: null,
					creator: null,
					title: "",
					description: "",
					timeslotDuration: 60,
					maxSelections: 1,
					areParticipantsAnonymized: false,
					areSelectionsAnonymized: false,
					selectedTimeslot: null,
					endsAt: defaultEndsAtDate.toISOString().slice(0, 10),
					completedAt: null,
					canceledAt: null,
					updatedAt: null,
					createdAt: null,
					timeslots: [],
					participants: [],
				};
			}

			this.surveyFormData.participants.forEach((participant) => {
				participant.isCalendarShown = false;
			});

			this.participantScheduleColors.forEach((participantColor) => {
				participantColor.uid = null;
			});
		},
		parseTimeslotForDisplay(timeslot) {
			const timeslotStartsAt = new Date(timeslot.startsAt);
			let timeslotEndsAt = new Date(timeslot.startsAt);
			timeslotEndsAt.setMinutes(
				timeslotEndsAt.getMinutes() +
					(this.$props.survey?.timeslotDuration ?? 0),
			);

			return {
				id: timeslot.id,
				weekday: this.weekdays[timeslotStartsAt.getDay()],
				date: numberPadding(timeslotStartsAt.getDate()),
				month: this.months[timeslotStartsAt.getMonth()],
				year: timeslotStartsAt.getFullYear().toString(),
				fullDate: formatDate(timeslot.startsAt + " UTC"),
				startTime:
					numberPadding(timeslotStartsAt.getHours()) +
					":" +
					numberPadding(timeslotStartsAt.getMinutes()),
				endTime:
					numberPadding(timeslotEndsAt.getHours()) +
					":" +
					numberPadding(timeslotEndsAt.getMinutes()),
			};
		},
		cancelEditForm() {
			this.isEditInProgress = false;
			if (!this.$props.survey?.id) {
				this.$emit("surveyCreationCanceled");
			}
		},
		submitForm() {
			if (
				!this.surveyFormData.title?.length ||
				!this.surveyFormData.endsAt?.length ||
				!this.surveyFormData.timeslotDuration
			) {
				window.alert("Check your inputs!");
				return;
			}

			if (!this.surveyFormData.participants.length) {
				if (
					!window.confirm(
						"You haven't added any participants. Are you sure you want to proceed?",
					)
				) {
					return;
				}
			}

			if (!this.surveyFormData.timeslots.length) {
				if (
					!window.confirm(
						"You haven't added any proposed appointments. Are you sure you want to proceed?",
					)
				) {
					return;
				}
			}

			let surveyData = this.formatOutgoingSurveyData(this.surveyFormData);
			if (this.surveyFormData.id) {
				this.updateSurvey(surveyData);
			} else {
				this.createSurvey(surveyData);
			}
		},
		formatOutgoingSurveyData(surveyFormData) {
			return {
				id: surveyFormData.id,
				title: surveyFormData.title,
				description: surveyFormData.description,
				timeslotDuration: surveyFormData.timeslotDuration,
				maxSelections: surveyFormData.maxSelections,
				areParticipantsAnonymized:
					surveyFormData.areParticipantsAnonymized,
				areSelectionsAnonymized: surveyFormData.areSelectionsAnonymized,
				endsAt: surveyFormData.endsAt,
				timeslots: surveyFormData.timeslots,
				participants: surveyFormData.participants.map((participant) => {
					return {
						uid: participant.uid,
					};
				}),
			};
		},
		async createSurvey(surveyData) {
			const surveyCreationResponse = await this.$api.call(
				CoodleApi.createSurvey(surveyData),
			);
			if (surveyCreationResponse.meta.status === "success") {
				this.$emit("surveyCreated", {
					surveyId: surveyCreationResponse.data,
				});
			}
		},
		async updateSurvey(surveyData) {
			const surveyUpdateResponse = await this.$api.call(
				CoodleApi.updateSurvey(surveyData),
			);
			if (surveyUpdateResponse.meta.status === "success") {
				this.$emit("surveyUpdated", {
					surveyId: surveyData.id,
				});
			}
		},
	},
	created() {
		this.setSurveyFormData();
		if (!this.$props.survey?.id) {
			this.isEditInProgress = true;
		}
		// todo: localize weekdays and months
	},
	template: /*html*/ `
	<div class="card mb-4" style="min-height:100%">
		<div class="card-header">
			<coodle-survey-header
				@editSurvey="isEditInProgress = true"
				:survey="$props.survey"
				:isEditInProgress="isEditInProgress"
				:uid="$props.uid"
			/>
		</div>
		<div class="card-body">
			<div class="d-flex flex-column gap-3">
				<div v-if="isEditInProgress" class="d-flex flex-row justify-content-between">
					<span class="fst-italic">
						{{ "Required inputs are marked with *." }}
					</span>
					<div class="d-flex flex-row gap-2 justify-content-end align-items-start px-0">
						<div>
							<div
								@click="cancelEditForm()"
								:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
								class="btn text-nowrap"
								>
								{{ "Cancel" }}
							</div>
						</div>
						<div>
							<div
								@click="submitForm()"
								:class="isDarkMode ? 'btn-light' : 'btn-dark'"
								class="btn text-nowrap"
							>
								{{ "Save" }}
							</div>
						</div>
					</div>
				</div>
			</div>
				<coodle-survey-basic-info
					v-model:surveyFormDataModelValue="surveyFormData"
					:survey="$props.survey"
					:parsedSelectedTimeslot="parsedSelectedTimeslot"
					:isEditInProgress="isEditInProgress"
				/>
				<hr>
				<div v-if="!isEditInProgress" class="d-flex flex-column gap-2">
					<coodle-survey-voting-table
						v-if="$props.survey"
						:uid="$props.uid"
						:timeslots="parsedTimeslotsForVotingTable"
						:survey="$props.survey"
					/>
				</div>
				<div v-else class="d-flex flex-column gap-3">
					<div class="row">
						<div class="col-12 col-xxl-4 pb-3">
							<coodle-survey-participants
								v-if="surveyFormData?.participants"
								v-model:participantsModelValue="surveyFormData.participants"
								v-model:participantScheduleColorsModelValue="participantScheduleColors"
							/>
						</div>
						<div class="col-12 col-xxl-8">
							<div class="d-flex flex-column gap-4">
								<coodle-survey-duration-selector
									v-if="surveyFormData?.timeslotDuration"
									v-model:durationModelValue="surveyFormData.timeslotDuration"
									:survey="$props.survey"
								/>
								<coodle-survey-calendar
									v-model:timeslotsModelValue="surveyFormData.timeslots"
									:survey="$props.survey"
									:timeslotDuration="surveyFormData.timeslotDuration"
									:surveyFormDataParticipants="surveyFormData.participants"
									:participantScheduleColors="participantScheduleColors"
								/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>`,
};
