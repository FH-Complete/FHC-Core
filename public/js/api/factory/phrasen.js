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
	loadCategory(category) {
		return {
			method: 'get',
			url: '/api/frontend/v1/phrasen/loadModule/' + category
		};
	},
	setLanguage(categories,language) {
		const params = {categories, language}
		return {
			method: 'post',
			url: '/api/frontend/v1/phrasen/setLanguage',
			params
		};
	},
	getLanguage() {
		return {
			method: 'get',
			url: '/api/frontend/v1/phrasen/getLanguage'
		};
	},
	getActiveDbLanguages() {
		return {
			method: 'get',
			url: '/api/frontend/v1/phrasen/getAllLanguages'
		};
	}
};