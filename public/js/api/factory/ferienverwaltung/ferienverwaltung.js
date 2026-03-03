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
	getFerien(filterVonDatum, filterBisDatum) {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getFerien',
			params: {
				filterVonDatum,
				filterBisDatum
			}
		};
	},
	getOe() {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getOe'
		};
	},
	getStudienplaene(oe_kurzbz, vondatum, bisdatum) {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getStudienplaene',
			params: {
				oe_kurzbz,
				vondatum,
				bisdatum
			}
		};
	},
	getFerientypen() {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getFerientypen'
		};
	},
	getStg() {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getStg'
		};
	},
	getDefaultVonBis() {
		return {
			method: 'get',
			url: 'api/frontend/v1/education/ferien/getDefaultVonBis'
		};
	},
	insert(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/education/ferien/insert',
			params
		};
	},
	update(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/education/ferien/update',
			params
		};
	},
	delete(ferien_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/education/ferien/delete',
			params: { ferien_id }
		};
	}
};