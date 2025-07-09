/**
 * Copyright (C) 2025 fhcomplete.org
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
	list() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/grades/list'
		};
	},
	getCertificate(prestudent_id, all) {
		all = all ? '/all' : '';
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/grades/getCertificate/' + prestudent_id + all
		};
	},
	getTeacherProposal(prestudent_id, all) {
		all = all ? '/all' : '';
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/grades/getTeacherProposal/' + prestudent_id + all
		};
	},
	getRepeaterGrades(prestudent_id, all) {
		all = all ? '/all' : '';
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/grades/getRepeaterGrades/' + prestudent_id + all
		};
	},
	updateCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note, lehrveranstaltung_bezeichnung}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/updateCertificate',
			params: {
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz,
				note
			}
		};
	},
	deleteCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, lehrveranstaltung_bezeichnung}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/deleteCertificate',
			params: {
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz
			}
		};
	},
	copyTeacherProposalToCertificate({lehrveranstaltung_id, student_uid, studiensemester_kurzbz, lehrveranstaltung_bezeichnung}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/copyTeacherProposalToCertificate',
			params: {
				lehrveranstaltung_id,
				student_uid,
				studiensemester_kurzbz
			}
		};
	},
	copyRepeaterGradeToCertificate({studierendenantrag_lehrveranstaltung_id, lv_bezeichnung}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/copyRepeaterGradeToCertificate',
			params: {
				studierendenantrag_lehrveranstaltung_id
			}
		};
	},
	getGradeFromPoints(points, lehrveranstaltung_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/getGradeFromPoints',
			params: { points, lehrveranstaltung_id }
		};
	}
};