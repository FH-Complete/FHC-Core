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
	getNews(
		page = 1,
		page_size = 10,
		sprache,
		publishedFilter = true,
		isActive = true,
		degreeProgramShortCode = null,
		semester = null,
	) {
		return {
			method: 'get',
			url: '/api/frontend/v1/NewsAdministrationAPI/getNews',
			params: {
				page,
				page_size,
				sprache,
				published: publishedFilter,
				isActive,
				degreeProgramShortCode,
				semester,
			},
		};
	},
	getNewsItem(newsId) {
		return {
			method: 'get',
			url: `/api/frontend/v1/NewsAdministrationAPI/getNewsItem/${newsId}`,
		};
	},
	storeNewsItem(data) {
		return {
			method: 'post',
			url: '/api/frontend/v1/NewsAdministrationAPI/storeNewsItem',
			params: data,
		};
	},
	updateNewsItem(newsId, data) {
		return {
			method: 'post',
			url: `/api/frontend/v1/NewsAdministrationAPI/updateNewsItem/${newsId}`,
			params: data,
		};
	},
	deleteNewsItem(newsId) {
		return {
			method: 'post',
			url: `/api/frontend/v1/NewsAdministrationAPI/deleteNewsItem/${newsId}`,
		};
	},
};
