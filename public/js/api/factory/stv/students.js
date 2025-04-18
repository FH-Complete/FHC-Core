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
	uid(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/students/uid/' + uid
		};
	},
	prestudent(prestudent_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/students/prestudent/' + prestudent_id
		};
	},
	person(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/students/person/' + person_id
		};
	},
	verband(relative_path) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/students/' + relative_path
		};
	},
	check(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/student/check',
			params
		};
	}
};