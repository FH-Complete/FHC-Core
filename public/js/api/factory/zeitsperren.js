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
	loadAllActiveZeitsperren(days) {
		return {
		 method: 'get',
		 url:'api/frontend/v1/MaZeitsperren/getAllActiveZeitsperren/' + days
		};
	},
	loadAllZeitsperrenFixeMa(days) {
		 return {
			 method: 'get',
			 url:'api/frontend/v1/MaZeitsperren/getAllZeitsperrenFixeMa/' + days
		 };
	 },
	loadAllZeitsperrenLector(days) {
		 return {
			 method: 'get',
			 url:'api/frontend/v1/MaZeitsperren/getAllZeitsperrenLector/' + days
		 };
	 },
	getAllOes(){
		return {
			method: 'get',
			url:'api/frontend/v1/funktionen/Funktionen/getAllOrgUnits'
		};
	},
	loadAllZeitsperrenOE(days, oe) {
		return {
			method: 'get',
			url:'api/frontend/v1/MaZeitsperren/getAllZeitsperrenOes/' + days + '/' + oe,
		};
	},
	loadZeitsperrenAss(days) {
		return {
			method: 'get',
			url:'api/frontend/v1/MaZeitsperren/getZeitsperrenAss/' + days
		};
	},
	getAllStg(){
		return {
			method: 'get',
			url:'api/frontend/v1/MaZeitsperren/getStgLectors'
		};
	},
	loadZeitsperrenLectorStg(days,stg) {
		return {
			method: 'get',
			url:'api/frontend/v1/MaZeitsperren/loadZeitsperrenLectorStg/' + days + '/' + stg,
		};
	},
}