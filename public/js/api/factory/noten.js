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
	getCisConfig(){
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getCisConfig'
		};
	},
	getStudentenNoten(lv_id, sem_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getStudentenNoten',
			params: { lv_id, sem_kurzbz }
		};
	},
	getNoten(){
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getNoten'
		};
	},
	saveStudentenNoten(password, noten, lv_id, sem_kurzbz) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveStudentenNoten',
			params: { password, noten, lv_id, sem_kurzbz }
		};
	},
	saveNotenvorschlag(lv_id, sem_kurzbz, student_uid, note) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveNotenvorschlag',
			params: { lv_id, sem_kurzbz, student_uid, note }
		};
	},
	saveStudentPruefung(student_uid, note, punkte, datum, lva_id, lehreinheit_id, sem_kurzbz, typ){
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveStudentPruefung',
			params: { student_uid, note, punkte, datum, lva_id, lehreinheit_id, sem_kurzbz, typ }
		};
	},
	createPruefungen(uids, datum, lva_id, sem_kurzbz){
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/createPruefungen',
			params: { uids, datum, lva_id, sem_kurzbz }
		};
	},
	saveNotenvorschlagBulk(lv_id, sem_kurzbz, noten) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveNotenvorschlagBulk',
			params: { lv_id, sem_kurzbz, noten }
		};
	},
	saveStudentPruefungBulk(lv_id, sem_kurzbz, pruefungen) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/savePruefungenBulk',
			params: { lv_id, sem_kurzbz, pruefungen }
		};
	},
	getNoteByPunkte(punkte, lv_id, sem_kurzbz) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/getNoteByPunkte',
			params: { punkte, lv_id, sem_kurzbz }
		};
	}
}