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
	getNotizen(id, type) {
		return {
			method: 'get',
			url: 'api/frontend/v1/notiz/notizPerson/getNotizen/' + id + '/' + type
		};
	},
	getUid() {
		return {
			method: 'get',
			url: 'api/frontend/v1/notiz/notizPerson/getUid/'
		};
	},
	addNewNotiz(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/notiz/notizPerson/addNewNotiz/' + id,
			params
		};
	},
	loadNotiz(notiz_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/notiz/notizPerson/loadNotiz/',
			params: {
				notiz_id
			}
		};
	},
	loadDokumente(notiz_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/notiz/notizPerson/loadDokumente/',
			params: {
				notiz_id
			}
		};
	},
	deleteNotiz(notiz_id, type_id, id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/notiz/notizPerson/deleteNotiz/',
			params: {
				notiz_id,
				type_id,
				id
			}
		};
	},
	updateNotiz(notiz_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/notiz/notizPerson/updateNotiz/' + notiz_id,
			params
		};
	},
	getMitarbeiter(event) {
		return {
			method: 'get',
			url: 'api/frontend/v1/notiz/notizPerson/getMitarbeiter/' + event
		};
	},
	isBerechtigt(id, type_id) {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'get',
			url: 'api/frontend/v1/notiz/notizPerson/isBerechtigt/'
		};
	}
};
