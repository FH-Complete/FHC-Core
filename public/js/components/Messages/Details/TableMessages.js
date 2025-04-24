import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';

import ApiMessages from '../../../api/factory/messages/messages.js';

export default {
	name: "TableMessages",
	components: {
		CoreFilterCmpt,
		FormForm,
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: {
		endpoint: {
			type: String,
			required: true
		},
		typeId: String,
		id: {
			type: [Number, String],
			required: true
		},
		messageLayout: String,
		openMode: String
	},
	data(){
		return {
			pageNo: 1,
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.loadAjaxCall,
				ajaxParams: () => {
					return {
						id: this.id,
						type: this.typeId
					};
				},
				ajaxResponse: (url, params, response) => this.buildTreemap(response),
				columns: [
					{title: "subject", field: "subject"},
					{title: "body", field: "body", visible: false},
					{title: "message_id", field: "message_id", visible: false},
					{
						title: "Datum",
						field: "insertamum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr); // Convert to Date object
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								hour12: false
							});
						}
					},
					{title: "sender", field: "sender"},
					{title: "recipient", field: "recipient"},
					{title: "senderId", field: "sender_id"},
					{title: "recipientId", field: "recipient_id"},
					{title: "relationmessage_id", field: "relationmessage_id"},
					{
						title: "status",
						field: "status",
						formatterParams: [
							"unread",
							"read",
							"archived",
							"deleted"
						],
						formatter: (cell, formatterParams) => {
							return formatterParams[cell.getValue()];
						}
					},
					{
						title: "letzte Änderung",
						field: "statusdatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr); // Convert to Date object
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								hour12: false
							});
						}
					},
					{
						title: 'Aktionen', field: 'actions',
						width: 100,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							if (this.personId != cell.getData().sender_id)
								button.disabled = true;
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('global', 'reply');
							button.innerHTML = '<i class="fa fa-reply"></i>';
							button.addEventListener(
								'click',
								(event) =>
									this.actionReplyToMessage(cell.getData().message_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('ui', 'loeschen');
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteMessage(cell.getData().message_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}
					],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height: '400',
				selectableRangeMode: 'click',
				index: 'message_id',
				pagination: true,
				paginationMode: "remote",
				paginationSize: 15,
				paginationInitialPage: 1,
				dataTree: true,
				headerSort: true,
				dataTreeChildField: "children",
				dataTreeCollapseElement:"<i class='fas fa-minus-square'></i>",
				dataTreeChildIndent: 15,
				dataTreeStartExpanded: false,
				persistenceID: 'core-message'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'messages', 'ui', 'notiz']);


						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('subject').component.updateDefinition({
							title: this.$p.t('global', 'betreff')
						});
						cm.getColumnByField('body').component.updateDefinition({
							title: this.$p.t('messages', 'body')
						});
						cm.getColumnByField('message_id').component.updateDefinition({
							title: this.$p.t('messages', 'message_id')
						});
						cm.getColumnByField('insertamum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('sender').component.updateDefinition({
							title: this.$p.t('messages', 'sender')
						});
						cm.getColumnByField('recipient').component.updateDefinition({
							title: this.$p.t('messages', 'recipient')
						});
						cm.getColumnByField('sender_id').component.updateDefinition({
							title: this.$p.t('messages', 'senderId')
						});
						cm.getColumnByField('recipient_id').component.updateDefinition({
							title: this.$p.t('messages', 'recipientId')
						});
						cm.getColumnByField('statusdatum').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
						cm.getColumnByField('status').component.updateDefinition({
							formatterParams: [
								this.$p.t('messages/unread'),
								this.$p.t('messages/read'),
								this.$p.t('messages/archived'),
								this.$p.t('messages/deleted')
							]
						});
						this.$refs.table.tabulator.rowManager.getDisplayRows();
						/*
						cm.getColumnByField('actions').component.updateDefinition({
						title: this.$p.t('global', 'aktionen')
												});
						*/
					}
				},
				{
					event: 'rowClick',
					handler: (e, row) => {
							const selectedMessage = row.getData().message_id;
							const body = row.getData().body;
							this.previewBody = body;
					}
				},
				{
					event: 'pageLoaded',
					handler: (pageno) => {
						this.pageNo = pageno+1;
					}
				}
			],
			previewBody: "",
			open: false,
			personId: null,
		}
	},
	methods: {
		actionDeleteMessage(message_id){
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? message_id
					: Promise.reject({handled: true}))
				.then(this.deleteMessage)
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteMessage(message_id){
			return this.$api
				.call(ApiMessages.deleteMessage(message_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		actionNewMessage(){
			this.$emit('newMessage', this.id, this.typeId);
		},
		actionReplyToMessage(message_id){
			this.$emit('replyToMessage', this.id, this.typeId, message_id);
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		buildTreemap(messages) {
			const last_page = messages.meta.count;
			messages = messages.data;
			const messageMap = new Map();
			const messageNested = [];
			const remainingMessages = new Set(messages);

			//save all Data in Map
			messages.forEach(msg => messageMap.set(msg.message_id, msg));

			let iteration = 0;
			let changes = true;

			// do until each relationmessage_id finds message_id (not sensitive to order)
			while (changes) {
				changes = false;
				iteration++;

				remainingMessages.forEach(msg => {
					if (msg.relationmessage_id === null) {
						messageNested.push(messageMap.get(msg.message_id));
						remainingMessages.delete(msg);
						changes = true;
					} else if (messageMap.has(msg.relationmessage_id)) {

						const parent = messageMap.get(msg.relationmessage_id);

						if (!parent.children) {
							parent.children = [];
						}
						parent.children.push(messageMap.get(msg.message_id));
						remainingMessages.delete(msg);
						changes = true;
					}
				});

			// to avoid endless loop
			if (iteration > messages.length) break;
			}
		return {data: messageNested, last_page};
		},
		loadAjaxCall(params){
			return this.$api.call(
				ApiMessages.getMessages({
					id: this.id,
					type: this.typeId,
					size: this.tabulatorOptions.paginationSize,
					page: this.pageNo
				})
			);
		}
	},
	computed: {
		statusText(){
			return {
				0: this.$p.t('messsages', 'unread'),
				1: this.$p.t('messsages', 'read'),
				2: this.$p.t('messsages', 'archived'),
				3: this.$p.t('messsages', 'deleted')
			}
		},
	},
	mounted() {
		// change to target="_blank"
/*		this.$nextTick(() => {
			const links = document.querySelectorAll('.preview a');
			links.forEach(link => {
				link.setAttribute('target', '_blank');
				link.setAttribute('rel', 'noopener noreferrer'); // Sicherheitsmaßnahme
			});
		});*/
	},
	created(){
		if(this.typeId != 'person_id') {
			const params = {
				id: this.id,
				type_id: this.typeId
			};
			this.$api
				.call(ApiMessages.getPersonId(params))
				.then(result => {
					this.personId = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template: `
	<div class="messages-detail-table">
	
		<!--View Studierendenverwaltung-->
		<div v-if="messageLayout=='twoColumnsTableLeft'">

			<div class="row">
				<!--table-->
				<div class="col-sm-6 pt-6">
					<core-filter-cmpt
						ref="table"
						:tabulator-options="tabulatorOptions"
						:tabulator-events="tabulatorEvents"
						table-only
						:side-menu="false"
						reload
						new-btn-show
						:new-btn-label="this.$p.t('global', 'nachricht')"
						@click:new="actionNewMessage"
						>
					</core-filter-cmpt>
				</div>

				<!--preview wysiwyg-window-->
				<div class="col-sm-6 pt-6">
				<br><br><br><br>
					<div ref="preview">
						<div v-html="previewBody" class="p-3 border rounded overflow-scroll twoColumns"></div>
					</div>

				</div>

			</div>
		</div>

		<!--View Infocenter-->
		<div v-if="messageLayout=='listTableTop'">

				<!--table-->
				<div class="col-sm-12 pt-6">
					<core-filter-cmpt
						ref="table"
						:tabulator-options="tabulatorOptions"
						:tabulator-events="tabulatorEvents"
						table-only
						:side-menu="false"
						reload
						new-btn-show
						:new-btn-label="this.$p.t('global', 'nachricht')"
						@click:new="actionNewMessage"
						>
					</core-filter-cmpt>
				</div>

				<!--preview wysiwyg-window-->
				<div class="col-sm-12">

					<div ref="preview">
						<div v-html="previewBody" class="p-3 border rounded overflow-scroll twoColumns"></div>
					</div>

				</div>
		</div>

	</div>
	`

}