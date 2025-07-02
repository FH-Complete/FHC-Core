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
	getAllVertraege(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Vertraege/getAllVertraege/' + person_id
		};
	},
	getAllContractsNotAssigned(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Vertraege/getAllContractsNotAssigned/' + person_id
		};
	},
	getAllContractsAssigned(person_id, vertrag_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Vertraege/getAllContractsAssigned/' + person_id + '/' + vertrag_id + ''
		};
	},
	getAllContractTypes() {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Vertraege/getAllContractTypes/'
		};
	},
	getStatiOfContract(person_id, vertrag_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Vertraege/getStatiOfContract/' + person_id + '/' + vertrag_id
		};
	},
	configPrintDocument() {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/Config/printDocument/'
		};
	},
	getAllContractStati() {
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/vertraege/getAllContractStati/'
		};
	},
	deleteContract(vertrag_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/deleteContract/' + vertrag_id
		};
	},
	addNewContract(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/addNewContract/',
			params
		};
	},
	loadContract(vertrag_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/vertraege/loadContract/' + vertrag_id
		};
	},
	updateContract(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/updateContract/',
			params
		};
	},
	loadContractStatus(params){
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/vertraege/loadContractStatus/',
			params
		};
	},
	insertContractStatus(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/insertContractStatus/',
			params
		};
	},
	updateContractStatus(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/updateContractStatus/',
			params
		};
	},
	deleteContractStatus(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/deleteContractStatus/',
			params
		};
	},
	deleteLehrauftrag(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/deleteLehrauftrag/',
			params
		};
	},
	deleteBetreuung(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/vertraege/vertraege/deleteBetreuung/',
			params
		};
	},
	getMitarbeiter(){
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/vertraege/getMitarbeiter/',
		};
	},
	getMitarbeiterUid(person_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/vertraege/vertraege/getMitarbeiterUid/' + person_id
		};
	},
};