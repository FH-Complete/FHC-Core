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
	getDocumentsUnaccepted(params) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/getDocumentsUnaccepted/' + params.id + '/' + params.studiengang_kz
		};
	},
	getDocumentsAccepted(params) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/getDocumentsAccepted/' + params.id + '/' + params.studiengang_kz
		};
	},
	deleteZuordnung(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/dokumente/deleteZuordnung/' + params.prestudent_id + '/' + params.dokument_kurzbz
		};
	},
	createZuordnung(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/dokumente/createZuordnung/' + params.prestudent_id + '/' + params.dokument_kurzbz
		};
	},
	loadAkte(akte_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/loadAkte/' + akte_id
		};
	},
	getDoktypen(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/getDoktypen/'
		};
	},
	updateFile(akte_id, params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/dokumente/updateAkte/' + akte_id,
			params
		};
	},
	deleteFile(akte_id){
		console.log("in deleteFile " + akte_id);
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/dokumente/deleteAkte/' + akte_id,
		};
	},
	uploadFile(prestudent_id, params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/dokumente/uploadDokument/' + prestudent_id,
			params
		};
	},
	getDocumentDropdown(params){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/getDocumentDropDown/' + params.prestudent_id + '/' + params.studiensemester_kurzbz + '/' + params.studiengang_kz,
		};
	},
	getDocumentDropdownMulti(studentUids, params){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/dokumente/getDocumentDropDownMulti/' + params.studiensemester_kurzbz + '/' + params.studiengang_kz,
			params: {studentUids}
		};
	}
}