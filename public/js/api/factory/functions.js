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
	getContractFunctions(filter) {
		var url = 'api/frontend/v1/funktionen/Funktionen/getContractFunctions';
		if( typeof filter !== 'undefined' && filter !== null ) {
			url = url + '/' + filter;
		}
		return {
			method: 'get',
			url,
		};

	},
	getOrgHeads() {
		var url = 'api/frontend/v1/funktionen/Funktionen/getOrgHeads';
		return {
			method: 'get',
			url,
		};
	},
	getOrgetsForCompany(unternehmen) {
		var url = 'api/frontend/v1/funktionen/Funktionen/getOrgetsForCompany'
			+ '/' + unternehmen;
		return {
			method: 'get',
			url,
		};
	},
	loadAllOes(filterStudent) {
		var url = 'api/frontend/v1/funktionen/Funktionen/loadAllOes'
			+ '/' + filterStudent;
		return {
			method: 'get',
			url,
		};
	},
	getCompanyByOrget(orget) {
		var url = 'api/frontend/v1/funktionen/Funktionen/getCompanyByOrget'
			+ '/' + orget;
		return {
			method: 'get',
			url,
		};
	},
	getCurrentFunctions(mitarbeiter_uid, unternehmen) {
		var url = 'api/frontend/v1/funktionen/Funktionen/getCurrentFunctions'
			+ '/' + mitarbeiter_uid + '/' + unternehmen;
		return {
			method: 'get',
			url,
		};
	}  ,
	getAllUserFunctions(mitarbeiter_uid) {
		var url = 'api/frontend/v1/funktionen/Funktionen/getAllUserFunctions'
			+ '/' + mitarbeiter_uid;
		return {
			method: 'get',
			url,
		};
	},
	getAllFunctions() {
		var url = 'api/frontend/v1/funktionen/Funktionen/getAllFunctions';
		return {
			method: 'get',
			url,
		};
	},
	addFunction(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/funktionen/Funktionen/insertFunction/',
			params
		};
	},
	loadFunction(benutzerfunktion_id) {
		var url = 'api/frontend/v1/funktionen/Funktionen/loadFunction'
			+ '/' + benutzerfunktion_id;
		return {
			method: 'get',
			url,
		};
	},
	updateFunction(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/funktionen/Funktionen/updateFunction/',
			params
		};
	},
	deleteFunction(benutzerfunktion_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/funktionen/Funktionen/deleteFunction/' + benutzerfunktion_id
		};
	},
	getOes(head, searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/funktionen/Funktionen/searchOes/' + head + '/' + searchString
		};
	},
	getFunctions(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/funktionen/Funktionen/searchFunctions/' + searchString
		};
	}

};