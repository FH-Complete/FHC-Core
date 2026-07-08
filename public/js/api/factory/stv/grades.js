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
	getCertificate(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getCertificate/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return {
			method: 'get',
			url: url
		};
	},
	getTeacherProposal(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getTeacherProposal/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return {
			method: 'get',
			url: url
		};
	},
	getRepeaterGrades(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/grades/getRepeaterGrades/' + encodeURIComponent(prestudent_id);
		if (!!studiensemester_kurzbz) {
			url = url + '/' + encodeURIComponent(studiensemester_kurzbz);
		}
		return {
			method: 'get',
			url: url
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
	getGradeFromPoints(points, lehrveranstaltung_id, studiensemester_kurzbz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/grades/getGradeFromPoints',
			params: {
				"points": points,
				"lehrveranstaltung_id": lehrveranstaltung_id,
				"studiensemester_kurzbz": studiensemester_kurzbz
			}
		};
	}
};