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
	getAllRooms(params) {
        return {
            method: 'get',
            url: 'api/frontend/v1/Ort/getAllRooms',
            params: {
                "filter[organizationalUnitShortCode]" : params?.organizationalUnitShortCode,
                "filter[locationId]" : params?.locationId,
                "filter[buildingComponent]" : params?.buildingComponent,
                "filter[isForTrainingProgram]" : params?.isForTrainingProgram,
                "filter[isReservationNeeded]" : params?.isReservationNeeded,
                "filter[isActive]" : params?.isActive,
            }
        }
    },
	getContentID(ort_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Ort/ContentID',
			params: { ort_kurzbz: ort_kurzbz }
		};
	},
	getRooms(datum, von, bis, typ, personenanzahl = 0) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Ort/getRooms',
			params: { datum, von, bis, typ, personenanzahl }
		};
	},
	getRoom(ort_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Ort/getRoom/' + ort_kurzbz,
		};
	},
	getRoomTypes() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Ort/getTypes',
			params: { }
		};
	},
	createRoom(roomData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Ort/createRoom',
			params: roomData
		}
	},
	updateRoom(roomId, roomData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Ort/updateRoom/' + roomId,
			params: roomData
		}
	},
	deleteRoom(ort_kurzbz) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Ort/deleteRoom/' + ort_kurzbz,
		}
	}
};