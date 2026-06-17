import CoodleSurveyVotingTable from "./CoodleSurveyVotingTable.js";

import {
	formatDate,
	numberPadding,
	addMinutesToDate,
} from "../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurvey",
	components: { CoodleSurveyVotingTable },
	props: {
		survey: { type: Object | null },
		uid: { type: String | null },
	},
	data() {
		return {
			surveyFormData: null,
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
		isAuthUserSurveyCreator() {
			return (
				this.$props.uid &&
				this.$props.uid === this.$props.survey?.creator?.uid
			);
		},
		isAuthUserSurveyParticipant() {
			return (
				this.$props.uid &&
				this.$props.survey?.participants
					?.map((participant) => participant.uid)
					.includes(this.$props.uid)
			);
		},
		isSurveyActive() {
			return (
				!this.$props.survey.completedAt &&
				!this.$props.survey.canceledAt
			);
		},
		headerTitle() {
			if (this.survey?.id && !this.isEditInProgress) {
				return this.survey.title;
			} else if (this.survey?.id && this.isEditInProgress) {
				return "Edit survey";
			} else {
				return "New survey";
			}
		},
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
		parsedSelectedTimeslot() {
			return this.$props.survey?.selectedTimeslotId
				? this.parseTimeslot(
						this.$props.survey.timeslots.find(
							(timeslot) =>
								timeslot.id ===
								this.$props.survey.selectedTimeslotId,
						),
					)
				: null;
		},
		parsedTimeslotsForVotingTable() {
			return (
				this.$props.survey?.timeslots
					.sort((timeslotA, timeslotB) => {
						return timeslotA.startsAt < timeslotB.startsAt ? -1 : 1;
					})
					.map((timeslot) => this.parseTimeslot(timeslot)) ?? []
			);
		},
	},
	watch: {
		survey: {
			handler() {
				this.setSurveyFormData();
				if (!this.survey?.id) {
					this.isEditInProgress = true;
				}
			},
			deep: true,
		},
	},
	methods: {
		setSurveyFormData() {
			if (this.$props.survey) {
				this.surveyFormData = { ...this.$props.survey };
				return;
			}

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
				endsAt: null, //todo: set to current date + 7
				completedAt: null,
				canceledAt: null,
				updatedAt: null,
				createdAt: null,
				timeslots: [],
				participants: [],
			};
		},
		parseTimeslot(timeslot) {
			const timeslotStartsAt = new Date(timeslot.startsAt + " UTC");
			const timeslotEndsAt = addMinutesToDate(
				timeslotStartsAt,
				this.$props.survey?.timeslotDuration ?? 0,
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
		cancelSurvey() {
			if (!this.$props.survey?.id) return;

			if (
				!window.confirm(
					'Are you sure you want to cancel Coodle survey "(((surveyTitle)))"?'.replace(
						"(((surveyTitle)))",
						this.$props.survey?.title,
					),
				)
			) {
				return;
			}
			console.log("canceling...");
			// todo
		},
	},
	created() {
		this.setSurveyFormData();
		// todo: localize weekdays and months
	},
	template: /*html*/ `
	<div class="card mb-4">
		<div class="card-header">
			<div class="d-flex flex-row align-items-center justify-content-between">
				<h4 class="text-wrap m-0">{{ headerTitle }}</h4>
				<div
					v-if="isAuthUserSurveyCreator && !isEditInProgress && isSurveyActive"
					class="dropdown"
				>
					<div
						role="button"
						class="px-2"
						data-bs-toggle="dropdown"
						data-bs-auto-close="true"
						aria-expanded="false"
					>
						<i class="fa-solid fa-ellipsis-vertical fa-lg"></i>
					</div>
					<ul class="dropdown-menu dropdown-menu-end" style="min-width:0;'">
						<li>
							<div
								@click="isEditInProgress = true"
								role="button"
								class="dropdown-item px-3 py-1"
							>
								{{ "Edit survey" }}
							</div>
						</li>
						<li>
							<div
								@click="cancelSurvey()"
								role="button"
								class="dropdown-item px-3 py-1"
							>
								{{ "Cancel survey" }}
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>	
		<div class="card-body">
			<div v-if="!isEditInProgress" class="d-flex flex-column gap-3">
				<span v-if="$props.survey?.completedAt" class="fst-italic">
					{{ "This survey was completed on " + formattedSurveyCompletedAt + ". " }}
					<span v-if="$props.survey?.selectedTimeslotId">
						{{
							"The timeslot (((timeslot))) was selected.".replace(
								"(((timeslot)))",
								parsedSelectedTimeslot.weekday +
									" " +
									parsedSelectedTimeslot.fullDate +
									" " +
									parsedSelectedTimeslot.startTime +
									"-" +
									parsedSelectedTimeslot.endTime
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
				<hr class="my-2">
				<div class="d-flex flex-column gap-2">
					<coodle-survey-voting-table
						v-if="$props.survey"
						:uid="$props.uid"
						:survey="$props.survey"
						:timeslots="parsedTimeslotsForVotingTable"
					/>
				</div>
			</div>
		</div>
	</div>`,
};
