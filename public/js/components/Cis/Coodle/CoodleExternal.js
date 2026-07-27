import CoodleSurvey from "./Components/CoodleSurvey.js";

import CoodleApi from "../../../api/factory/coodle.js";

export default {
	name: "CoodleExternal",
	components: { CoodleSurvey },
	data() {
		return {
			survey: null,
			authExternalParticipantId: null,
		};
	},
	methods: {
		async fetchSurvey() {
			const surveyResponse = await this.$api.call(
				CoodleApi.getSurveyForExternalParticipant(
					this.$route.params.key,
				),
			);

			if (surveyResponse.meta.status === "success") {
				this.survey = this.parseIncomingSurveyData(surveyResponse.data);
				this.authExternalParticipantId = this.survey.externalParticipants.find(
					(externalParticipant) => !!externalParticipant.id,
				).id;
			}
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
		await this.fetchSurvey();
	},
	template: /*html*/ `
	<div>
		<coodle-survey
			@surveyUpdated="fetchSurvey()"
			:survey="survey"
			:authInfo="null"
			:authExternalParticipantId="authExternalParticipantId"
			:isExternal="true"
		/>
	</div>
`,
};
