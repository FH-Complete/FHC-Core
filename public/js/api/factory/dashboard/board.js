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
	list() {
		return {
			method: 'get',
			url: 'api/frontend/v1/dashboard/board/list'
		};
	},
	add(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/create',
			params
		};
	},
	update(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/update',
			params
		};
	},
	delete(dashboard_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/dashboard/board/delete',
			params: { dashboard_id }
		};
	}
}