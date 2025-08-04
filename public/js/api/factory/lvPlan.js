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
	getRoomInfo(ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/LvPlan/getRoomplan',
			params: { ort_kurzbz, start_date, end_date }
		};
	},
	getLvPlan(start_date, end_date, lv_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/LvPlan/getLvPlan',
			params: { start_date, end_date, lv_id }
		};
	},
	eventsPersonal(start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/lvPlan/eventsPersonal',
			params: { start_date, end_date }
		};
	},
	eventsLv(lv_id, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/lvPlan/eventsLv',
			params: { lv_id, start_date, end_date }
		};
	},
	getStunden() {
		return {
			method: 'get',
			url: '/api/frontend/v1/LvPlan/Stunden'
		};
	},
	getOrtReservierungen(ort_kurzbz, start_date, end_date) {
		return {
			method: 'post',
			url: `/api/frontend/v1/LvPlan/getReservierungen/${ort_kurzbz}`,
			params: { start_date, end_date }
		};
	},
	getLvPlanReservierungen(start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/LvPlan/getReservierungen',
			params: { start_date, end_date }
		};
	},
	getLehreinheitStudiensemester(lehreinheit_id) {
		return {
			method: 'get',
			url: `/api/frontend/v1/LvPlan/getLehreinheitStudiensemester/${lehreinheit_id}`
		};
	},
	studiensemesterDateInterval(date) {
		return {
			method: 'get',
			url: `/api/frontend/v1/LvPlan/studiensemesterDateInterval/${date}`
		};
	},
	LvPlanEvents(start_date, end_date, lv_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/LvPlan/LvPlanEvents',
			params: { 
				start_date: start_date, 
				end_date: end_date, 
				lv_id: lv_id 
			}
		};
	},
};