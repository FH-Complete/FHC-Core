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
	getMessageVarsPerson(userParams){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getMessageVarsPerson/' + userParams.id + '/' + userParams.type_id
		};
	},
	//TODO(Manu) enable for id_array
/*	getMsgVarsPrestudent(userParams){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getMsgVarsPrestudent/' + userParams.id + '/' + userParams.type_id
		};
	},	*/
	getMsgVarsPrestudent(ids, type_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getMsgVarsPrestudent/' + type_id,
			params: {ids}
		};
	},
	//TODO(Manu) enable for id_array
	getPersonId(params){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getPersonId/' + params.id + '/' + params.type_id
		};
	},
	//TODO(Manu) enable for id_array
/*	getUid(userParams){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getUid/' + userParams.id + '/' + userParams.type_id
		};
	},	*/
	getUid(ids, type_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/getUids/' + type_id,
			params: {ids}
		};
	},
	getDataVorlage(vorlage_kurzbz){
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getDataVorlage/' + vorlage_kurzbz
		};
	},
/*	getNameOfDefaultRecipient(params){
		console.log(params.id);
		return {
			method: 'get',
			url: 'api/frontend/v1/messages/messages/getNameOfDefaultRecipient/' + params.id + '/' + params.type_id
		};
	},*/
	//TODO(Manu) enable for id_array
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
/*	sendMessageFromModalContext(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/sendMessage/' + id,
			params
		};
	},	*/
	sendMessageFromModalContext(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/sendMessages/',
			params
		};
	},
	sendMessage(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/messages/messages/sendMessage/' + id,
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