/*
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
	getAllDashboards() {
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/board/list'
		};
	},
	addDashboard(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/create',
			params
		};
	},
	updateDashboard(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/update',
			params
		};
	},
	deleteDashboard(dashboard_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/delete',
			params: { dashboard_id }
		};
	},
	loadFunktionen(dashboard_kurzbz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/preset/list/'
				+ encodeURIComponent(dashboard_kurzbz)
		};
	},
	presetBatch(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/preset/getBatch',
			params
		};
	},
	addWidgetsToPreset(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/preset/addWidget',
			params
		};
	},
	removeWidgetFromPreset(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/preset/removeWidget',
			params
		};
	}
}