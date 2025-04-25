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
	getAufnahmetermine(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/aufnahmetermine/getAufnahmetermine/' + person_id,
		};
	},
	getListPlacementTests(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/aufnahmetermine/getListPlacementTests/',
		};
	},
	getListStudyPlans(person_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/aufnahmetermine/getListStudyPlans/' + person_id,
		};
	},
	addNewPlacementTest(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/aufnahmetermine/insertAufnahmetermin/',
			params
		};
	},
	loadPlacementTest(rt_person_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/aufnahmetermine/loadAufnahmetermin/' + rt_person_id,
		};
	},
	updatePlacementTest(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/aufnahmetermine/updateAufnahmetermin/',
			params
		};
	},
	deletePlacementTest(rt_person_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/aufnahmetermine/deleteAufnahmetermin/' + rt_person_id
		};
	}

}