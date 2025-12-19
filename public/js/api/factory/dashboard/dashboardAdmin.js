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
	addDashboard(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/createDashboard',
			params
		};
	},
	updateDashboard(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/updateDashboard',
			params
		};
	},
	deleteDashboard(dashboard_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/deleteDashboard',
			params: { dashboard_id }
		};
	},
	getAllDashboards(){
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/getAllDashboards'
		};
	},
	getAllWidgets(dashboard_id){
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/getAllWidgets',
			params: {dashboard_id}
		};
	},
	setWidgetAllowed(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/setWidgetAllowed',
			params
		};
	},
	loadFunktionen(){
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/funktionen'
		};
	},
	addWidgetsToPreset(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/addWidgetsToPreset',
			params
		};
	},
	removeWidgetFromPreset(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/removeWidgetFromPreset',
			params
		};
	},
	presetBatch(params){
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/DashboardAdmin/presetBatch',
			params
		};
	},
}