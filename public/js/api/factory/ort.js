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
                "filter[oe_kurzbz]" : params?.organizationalUnitShortCode,
                "filter[standort_id]" : params?.locationId,
                "filter[gebteil]" : params?.buildingComponent,
                "filter[lehre]" : params?.isForTrainingProgram,
                "filter[reservieren]" : params?.isReservationNeeded,
                "filter[aktiv]" : params?.isActive,
				"filter[ort_kurzbz]" : params?.shortCode,
				"filter[bezeichnung]" : params?.description,
				"filter[planbezeichnung]" : params?.planDescription,
				"filter[max_person]" : params?.maxPersons,
				"filter[arbeitsplaetze]" : params?.workplace,
				"filter[m2]" : params?.squareMeters,
				"filter[org_organisationseinheittyp_kurzbz_org_bezeichnung_concat]" : params?.orgUnitConcatDescription,
				"filter[kosten]" : params?.costs,
				"filter[stockwerk]" : params?.floor,
				"filter[parent_ort_kurzbz]" : params?.parentRoomShortCode,
				"filter[ort_kurzbz_bezeichnung_concat]" : params?.ort_kurzbz_bezeichnung_concat,
				"sort[ort_kurzbz]" : params?.sort?.ort_kurzbz,
				"sort[bezeichnung]" : params?.sort?.bezeichnung,
				"sort[planbezeichnung]" : params?.sort?.planbezeichnung,
				"sort[max_person]" : params?.sort?.max_person,
				"sort[arbeitsplaetze]" : params?.sort?.arbeitsplaetze,
				"sort[m2]" : params?.sort?.m2,
				"sort[org_organisationseinheittyp_kurzbz_org_bezeichnung_concat]" : params?.sort?.org_organisationseinheittyp_kurzbz_org_bezeichnung_concat,
				"sort[lehre]" : params?.sort?.lehre,
				"sort[reservieren]" : params?.sort?.reservieren,
				"sort[aktiv]" : params?.sort?.aktiv,
				"sort[kosten]" : params?.sort?.kosten,
				"sort[stockwerk]" : params?.sort?.stockwerk,
				"sort[parent_ort_kurzbz]" : params?.sort?.parent_ort_kurzbz,
				"pagination[page]" : params?.pagination?.page,
				"pagination[size]" : params?.pagination?.size,
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