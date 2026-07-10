/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

export default {
	searchParticipants(searchString) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/searchParticipants",
			params: {
				searchString,
			},
		};
	},
	createSurvey(surveyData, shouldInformParticipants) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/createSurvey",
			params: {
				surveyData,
				shouldInformParticipants,
			},
		};
	},
	updateSurvey(surveyId, surveyData, shouldInformParticipants) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/updateSurvey",
			params: {
				surveyId,
				surveyData,
				shouldInformParticipants,
			},
		};
	},
	getSurvey(surveyId) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/getSurvey",
			params: {
				surveyId,
			},
		};
	},
	getActiveSurveys() {
		return {
			method: "get",
			url: "/api/frontend/v1/coodle/CoodleSurvey/getActiveSurveys",
		};
	},
	getInactiveSurveys() {
		return {
			method: "get",
			url: "/api/frontend/v1/coodle/CoodleSurvey/getInactiveSurveys",
		};
	},
	submitParticipantSelection(surveyId, selection) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/submitParticipantSelection",
			params: {
				surveyId,
				selection,
			},
		};
	},
	cancelSurvey(surveyId) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/cancelSurvey",
			params: {
				surveyId,
			},
		};
	},
	completeSurvey(surveyId, selectedTimeslotId, selectedRoomId, shouldInformParticipants) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/completeSurvey",
			params: {
				surveyId,
				selectedTimeslotId,
				selectedRoomId,
				shouldInformParticipants
			},
		};
	},
	sendReminders(surveyId) {
		return {
			method: "post",
			url: "/api/frontend/v1/coodle/CoodleSurvey/sendVotingReminders",
			params: {
				surveyId,
			},
		};
	}
};
