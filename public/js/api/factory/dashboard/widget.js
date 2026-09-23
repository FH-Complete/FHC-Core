/**
 * Copyright (C) 2026 fhcomplete.org
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
	get(widget) {
		return {
			method: 'get',
			url: '/api/frontend/v1/dashboard/widget/get/' + widget
		};
	},
	list(dashboard) {
		return {
			method: 'get',
			url: '/api/frontend/v1/dashboard/widget/list/' + dashboard
		};
	},
	listAllowed(dashboard) {
		return {
			method: 'get',
			url: '/api/frontend/v1/dashboard/widget/listAllowed/' + dashboard
		};
	},
	setAllowed(dashboard_id, widget_id, allowed) {
		return {
			method: 'post',
			url: '/api/frontend/v1/dashboard/widget/setAllowed',
			params: {
				dashboard_id, widget_id, allowed
			}
		};
	}
};