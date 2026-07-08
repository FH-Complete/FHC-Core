import CoodleSurvey from "./Components/CoodleSurvey.js";
import CoodleFreeBusy from "./Components/CoodleFreeBusy.js";

import ApiAuthinfo from "../../../api/factory/authinfo.js";
import CoodleApi from "../../../api/factory/coodle.js";

export default {
	name: "Coodle",
	components: { CoodleSurvey, CoodleFreeBusy },
	data() {
		return {
			uid: null,
			// todo: revert to default view active when dev done
			view: "survey",
			survey: null,
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
	},
	watch: {},
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
		async getAuthUid() {
			const authUidResponse = await this.$api.call(
				ApiAuthinfo.getAuthUID(),
			);
			this.uid = authUidResponse.data.uid;
		},
		async showSurvey(surveyId) {
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
				areParticipantsAnonymized: surveyData.are_participants_anonymized,
				areSelectionsAnonymized: surveyData.are_selections_anonymized,
				selectedTimeslotId: surveyData.description,
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
				voteTallies: surveyData.vote_tallies,
			};
		},
	},
	async created() {
		await this.getAuthUid();
		this.showSurvey(26);
	},
	template: /*html*/ `
	<div class="h-100 d-flex flex-column gap-2">
		<h2 class="m-0">Coodle</h2>
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
		</div>
		<div class="flex-grow-1 mt-1">
			<span v-if="view === 'activeSurveysTable'">active surveys table placeholder</span>
			<span v-else-if="view === 'pastSurveysTable'">past surveys table placeholder</span>
			<coodle-survey
				v-else-if="view === 'survey'"
				@surveyCreationCanceled="switchToTab('activeSurveysTable')"
				@surveyCreated="showSurvey($event.surveyId)"
				@surveyUpdated="showSurvey($event.surveyId)"
				:survey="survey"
				:uid="uid"
			/>
			<coodle-free-busy v-else-if="view === 'freeBusySettings'" :uid="uid" />
		</div>
	</div>`,
};
