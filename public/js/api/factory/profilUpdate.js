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
	//! API calls for profil update requests
	getStatus() {
		return {
			method: 'get',
			url: '/api/frontend/v1/ProfilUpdate/getStatus'
		};
	},
	getTopic() {
		return {
			method: 'get',
			url: '/api/frontend/v1/ProfilUpdate/getTopic'
		};
	},
	acceptProfilRequest({profil_update_id, uid, status_message, topic, requested_change}) {
		return {
			method: 'post',
			url: '/api/frontend/v1/ProfilUpdate/acceptProfilRequest',
			params: {
				profil_update_id,
				uid,
				status_message,
				topic,
				requested_change
			}
		};
	},
	denyProfilRequest({profil_update_id, uid, topic, status_message}) {
		return {
			method: 'post',
			url: '/api/frontend/v1/ProfilUpdate/denyProfilRequest',
			params: {
				profil_update_id,
				uid,
				topic,
				status_message
			}
		};
	},
	insertFile(dms, replace = null) {
		return {
			method: 'post',
			url: `/api/frontend/v1/ProfilUpdate/insertFile/${replace}`,
			params: dms
		};
	},
	updateProfilbild(dms) {
		return {
			method: 'post',
			url: `/api/frontend/v1/ProfilUpdate/updateProfilbild`,
			params: dms
		};
	},
	getProfilUpdateWithPermission(filter) {
		const url_filter = (filter !== '') ? '/' + encodeURIComponent(filter) : '';
		return {
			method: 'get',
			url: '/api/frontend/v1/ProfilUpdate/getProfilUpdateWithPermission' + url_filter
		};
	},
	getProfilRequestFiles(requestID) {
		return {
			method: 'get',
			url: `/api/frontend/v1/ProfilUpdate/getProfilRequestFiles/${requestID}`
		};
	},
	selectProfilRequest(uid = null, id = null) {
		return {
			method: 'get',
			url: '/api/frontend/v1/ProfilUpdate/selectProfilRequest',
			params: {
				...(uid ? { uid } : {}),
				...(id ? { id } : {})
			}
		};
	},
	insertProfilRequest(topic, payload, fileID = null) {
		return {
			method: 'post',
			url: '/api/frontend/v1/ProfilUpdate/insertProfilRequest',
			params: {
				topic,
				payload,
				...(fileID ? { fileID } : {})
			}
		};
	},
	updateProfilRequest(topic, payload, ID, fileID = null) {
		return {
			method: 'post',
			url: '/api/frontend/v1/ProfilUpdate/updateProfilRequest',
			params: {
				topic,
				payload,
				ID,
				...(fileID ? { fileID } : {})
			}
		};
	},
	deleteProfilRequest(requestID) {
		return {
			method: 'post',
			url: '/api/frontend/v1/ProfilUpdate/deleteProfilRequest',
			params: { requestID }
		};
	}
};