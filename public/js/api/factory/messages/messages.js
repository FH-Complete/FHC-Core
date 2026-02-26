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
	getMessages(params) {
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getMessages/'
				+ params.id + '/'
				+ params.type + '/'
				+ params.size + '/'
				+ params.page
		};
	},
	getVorlagen(){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getVorlagen/'
		};
	},
	getMsgVarsLoggedInUser(){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getMsgVarsLoggedInUser/'
		};
	},
	getMessageVarsPerson(ids, type_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getMessageVarsPerson/' + type_id,
			params: {ids}
		};
	},
	getMsgVarsPrestudent(ids, type_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getMsgVarsPrestudent/' + type_id,
			params: {ids}
		};
	},
	getPersonId(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getPersonId/' + params.id + '/' + params.type_id
		};
	},
	getDataVorlage(vorlage_kurzbz){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getDataVorlage/' + vorlage_kurzbz
		};
	},
	getNameOfDefaultRecipients(ids, type_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getNameOfDefaultRecipients/' + type_id,
			params: {ids}
		};
	},
	getPreviewText(type_id, params){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getPreviewText/' + type_id,
			params
		};
	},
	getReplyData(messageId){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getReplyData/' + messageId
		};
	},
	sendMessage(type_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/sendMessage/' + type_id,
			params
		};
	},
	deleteMessage(messageId){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/deleteMessage/' + messageId
		};
	}
}