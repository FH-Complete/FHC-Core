import CoodleSurvey from "./Components/CoodleSurvey.js";

import ApiAuthinfo from "../../../api/factory/authinfo.js";

export default {
	name: "Coodle",
	components: { CoodleSurvey },
	props: {},
	data() {
		return {
			uid: null,
			view: "survey",
			// survey: null,
			// todo: delete example survey after dev
			survey: {
				id: 1,
				creator: {
					uid: "ma1434",
					name: "Adis Posko",
				},
				title: "Test Meeting",
				description:
					"To discuss many important matters.\nAnother line of the description.",
				timeslotDuration: 75,
				maxSelections: 2,
				areParticipantsAnonymized: false,
				areSelectionsAnonymized: false,
				selectedTimeslotId: null,
				endsAt: "2026-06-23",
				completedAt: null,
				canceledAt: null,
				createdAt: "2026-06-14 23:30:30",
				updatedAt: "2026-06-15 16:00:00",
				timeslots: [
					{
						id: 1,
						startsAt: "2026-06-26 12:30:00",
					},
					{
						id: 2,
						startsAt: "2026-06-26 14:00:00",
					},
					{
						id: 3,
						startsAt: "2026-06-25 07:00:00",
					},
					{
						id: 4,
						startsAt: "2026-06-24 09:00:00",
					},
					{
						id: 5,
						startsAt: "2026-06-27 12:30:00",
					},
				],
				participants: [
					{
						uid: "ma1434",
						name: "Adis Posko",
						selection: [2],
					},
					{
						uid: "ma1435",
						name: "Test User 1",
						selection: [],
					},
					{
						uid: "ma1436",
						name: "Test Userrr 2",
						selection: null,
					},
					{
						uid: "ma1437",
						name: "Test Userrrrr 3",
						selection: [2, 3],
					},
				],
				sums: {
					1: 0,
					2: 2,
					3: 1,
					4: 0,
					5: 0,
					none: 1,
				},
			},
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
			this.view = tab;
			this.survey = null;
		},
		async getAuthUid() {
			const authUidResponse = await this.$api.call(
				ApiAuthinfo.getAuthUID(),
			);
			this.uid = authUidResponse.data.uid;
		},
	},
	async created() {
		await this.getAuthUid();
	},
	template: /*html*/ `
	<div class="h-100 d-flex flex-column gap-2">
		<h2 class="m-0">Coodle</h2>
		<div class="d-flex flex-row gap-2 w-100 overflow-x-auto">
			<div
				@click="switchToTab('activeSurveysTable')"
				:class="getTabStylingClass(view === 'activeSurveysTable')"
				class="btn text-nowrap"
			>
				Active surveys
			</div>
			<div
				@click="switchToTab('pastSurveysTable')"
				:class="getTabStylingClass(view === 'pastSurveysTable')"
				class="btn text-nowrap"
			>
				Past surveys
			</div>
			<div
				@click="switchToTab('survey')"
				:class="getTabStylingClass(view === 'survey' && !survey?.id)"
				class="btn text-nowrap"
			>
				+ Create new survey
			</div>
		</div>
		<div class="flex-grow-1 mt-1">
			<span v-if="view === 'activeSurveysTable'">active surveys table placeholder</span>
			<span v-else-if="view === 'pastSurveysTable'">past surveys table placeholder</span>
			<coodle-survey
				v-else-if="view === 'survey'"
				@surveyCreationCanceled="switchToTab('activeSurveysTable')"
				:survey="survey"
				:uid="uid"
			/>
		</div>
	</div>`,
};
