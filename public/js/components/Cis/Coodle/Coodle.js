import CoodleSurvey from "./Components/CoodleSurvey.js";

export default {
	name: "Coodle",
	components: { CoodleSurvey },
	props: {},
	data() {
		return {
			view: "activeSurveysTable",
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
			this.view = tab;
			this.survey = null;
		},
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
		<div class="flex-grow-1">
			<span v-if="view === 'activeSurveysTable'">active surveys table placeholder</span>
			<span v-else-if="view === 'pastSurveysTable'">past surveys table placeholder</span>
			<coodle-survey v-else-if="view === 'survey'" :survey="survey" />
		</div>
	</div>`,
};
