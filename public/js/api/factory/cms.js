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
	content(content_id, version=null, sprache=null, sichtbar=null) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Cms/content',
			params: {
				content_id,
				...(version ? { version } : {}),
				...(sprache ? { sprache } : {}),
				...(sichtbar ? { sichtbar } : {})
			}
		};
	},
	//api function used for the news View that renders the html
	getNews(page = 1, page_size = 10, sprache) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Cms/getNews',
			params: {
				page,
				page_size,
				sprache
			},
		};
	},
	//api function used for the widget component
	news(limit) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Cms/news',
			params: { limit }
		};
	},
	getNewsRowCount() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Cms/getNewsRowCount'
		};
	},
	getNewsExtra() {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'get',
			url: '/api/frontend/v1/Cms/getStudiengangInfoForNews'
		};
	}
};