import ActiveCoodleSurveys from "./Components/ActiveCoodleSurveys.js";
import InactiveCoodleSurveys from "./Components/InactiveCoodleSurveys.js";
import CoodleSurvey from "./Components/CoodleSurvey.js";
import CoodleFreeBusy from "./Components/CoodleFreeBusy.js";
import CoodleIcal from "./Components/CoodleIcal.js";

import ApiAuthinfo from "../../../api/factory/authinfo.js";
import CoodleApi from "../../../api/factory/coodle.js";

export default {
	name: "Coodle",
	components: {
		ActiveCoodleSurveys,
		InactiveCoodleSurveys,
		CoodleSurvey,
		CoodleFreeBusy,
		CoodleIcal,
	},
	data() {
		return {
			authInfo: null,
			view: "activeSurveysTable",
			survey: null,
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
	},
	methods: {
		getTabStylingClass(isSelected) {
			if (this.isDarkMode && isSelected) {
				return "btn-light";
			} else if (this.isDarkMode && !isSelected) {
				return "btn-outline-light";
			} else if (!this.isDarkMode && isSelected) {
				return "btn-dark";
			} else {
				return "btn-outline-dark";
			}
		},
		switchToTab(tab) {
			if (this.view === tab && tab !== "survey") return;

			this.view = tab;
			this.survey = null;
		},
		async getAuthInfo() {
			const authInfoResponse = await this.$api.call(
				ApiAuthinfo.getAuthInfo(),
			);
			this.authInfo = {
				uid: authInfoResponse.data.uid,
				name: authInfoResponse.data.name,
			};
		},
		async showSurveyDetails(surveyId) {
			const surveyResponse = await this.$api.call(
				CoodleApi.getSurvey(surveyId),
			);
			this.survey = this.parseIncomingSurveyData(surveyResponse.data);
			this.view = "survey";
		},
		parseIncomingSurveyData(surveyData) {
			return {
				id: surveyData.id,
				creator: surveyData.creator,
				title: surveyData.title,
				description: surveyData.description,
				timeslotDuration: surveyData.timeslot_duration,
				maxSelections: surveyData.max_selections,
				areParticipantsAnonymized:
					surveyData.are_participants_anonymized,
				areSelectionsAnonymized: surveyData.are_selections_anonymized,
				selectedTimeslotId: surveyData.selected_timeslot_id,
				endsAt: surveyData.ends_at.split(" ")[0],
				completedAt: surveyData.completed_at,
				canceledAt: surveyData.canceled_at,
				createdAt: surveyData.created_at.split(" ")[0],
				updatedAt: surveyData.updated_at.split(" ")[0],
				timeslots: surveyData.timeslots.map((timeslot) => {
					return {
						id: timeslot.id,
						startsAt: timeslot.starts_at,
					};
				}),
				participants: surveyData.participants,
				externalParticipants: surveyData.external_participants,
				voteTallies: surveyData.vote_tallies,
			};
		},
	},
	async created() {
		await this.getAuthInfo();
		if (this.$route.query?.id?.length) {
			this.showSurveyDetails(this.$route.query.id);
			this.$router.replace({ query: null });
		}
	},
	template: /*html*/ `
	<div class="h-100 d-flex flex-column gap-2">
		<h2 class="m-0">Coodle</h2>
		<div>
			<div class="d-flex flex-row gap-2 w-100 overflow-x-auto">
				<div
					@click="switchToTab('activeSurveysTable')"
					:class="getTabStylingClass(view === 'activeSurveysTable')"
					class="btn d-flex flex-row gap-2 align-items-center py-2"
				>
					<div>
						<i class="fa-solid fa-person-booth fa-lg"></i>
					</div>
					<span class="text-nowrap">{{ "Active surveys" }}</span>
				</div>
				<div
					@click="switchToTab('pastSurveysTable')"
					:class="getTabStylingClass(view === 'pastSurveysTable')"
					class="btn d-flex flex-row gap-2 align-items-center py-2"
				>
					<div>
						<i class="fa-solid fa-flag-checkered fa-lg"></i>
					</div>
					<span class="text-nowrap">{{ "Past surveys" }}</span>
				</div>
				<div
					@click="switchToTab('survey')"
					:class="getTabStylingClass(view === 'survey' && !survey?.id)"
					class="btn d-flex flex-row gap-2 align-items-center py-2"
				>
					<div>
						<i class="fa-solid fa-circle-plus fa-lg"></i>
					</div>
					<span class="text-nowrap">{{ "Create new survey" }}</span>
				</div>
				<div
					@click="switchToTab('freeBusySettings')"
					:class="getTabStylingClass(view === 'freeBusySettings')"
					class="btn d-flex flex-row gap-2 align-items-center py-2"
				>
					<div>
						<i class="fa-solid fa-gear fa-lg"></i>
					</div>
					<span class="text-nowrap">{{ "FreeBusy Settings" }}</span>
				</div>
				<div
					@click="switchToTab('ical')"
					:class="getTabStylingClass(view === 'ical')"
					class="btn d-flex flex-row gap-2 align-items-center py-2"
				>
					<div>
						<i class="fa-solid fa-calendar fa-lg"></i>
					</div>
					<span class="text-nowrap">{{ "Coodle iCal" }}</span>
				</div>
			</div>
		</div>
		<div class="flex-grow-1 mt-1">
			<active-coodle-surveys
				v-if="view === 'activeSurveysTable'"
				@showSurveyDetails="showSurveyDetails($event.surveyId)"
			/>
			<inactive-coodle-surveys
				v-else-if="view === 'pastSurveysTable'"
				@showSurveyDetails="showSurveyDetails($event.surveyId)"
			/>
			<coodle-survey
				v-else-if="view === 'survey'"
				@surveyCreationCanceled="switchToTab('activeSurveysTable')"
				@surveyCreated="showSurveyDetails($event.surveyId)"
				@surveyUpdated="showSurveyDetails($event.surveyId)"
				:survey="survey"
				:authInfo="authInfo"
			/>
			<coodle-free-busy v-else-if="view === 'freeBusySettings'" :authUid="authInfo?.uid" />
			<coodle-ical v-else-if="view === 'ical'" :authUid="authInfo?.uid" />
		</div>
	</div>`,
};
