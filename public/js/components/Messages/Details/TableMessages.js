import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import NewMessage from "../Details/NewMessage.js";

export default {
	components: {
		CoreFilterCmpt,
		FormForm,
		NewMessage,
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
	//TODO(Manu) endpoint macht Probleme
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.messages.person.getMessages,
				ajaxParams: () => {
					return {
						id: this.id,
						type: this.typeId
					};
				},
				ajaxResponse: (url, params, response) => response.data,
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
					{
						title: "status",
						field: "status",
						formatter: function (cell) {
							//TODO(Manu) get phrases in this context to work?

							/*							const statusMap = {
															0: this.$p.t('messsages', 'unread'),
															1: this.$p.t('messsages', 'read'),
															2: this.$p.t('messsages', 'archived'),
															3: this.$p.t('messsages', 'deleted')
														};*/
							const statusMap = {
								0: 'unread',
								1: 'read',
								2: 'archived',
								3: 'deleted'
							};
							return statusMap[cell.getValue()];
							// return this.$p.t('messsages', 'deleted');
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
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('global', 'reply');
							button.innerHTML = '<i class="fa fa-reply"></i>';
							button.addEventListener(
								'click',
								(event) =>
									this.reply(cell.getData().message_id)
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
					}],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
			//	height:	'auto',
				height: '400',
				selectable:	true,
				selectableRangeMode: 'click',
/*				layoutColumnsOnNewData: false,

				selectableRangeMode: 'click',
				selectable: true,
				index: 'message_id',
				persistenceID: 'core-message'*/
			},
			tabulatorEvents: [
				{
					event: 'dataLoaded',
					handler: data => this.tabulatorData = data.map(item => {
						return item;
					}),
				},
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
			],
			tabulatorData: [],
			previewBody: "",
			open: false
		}
	},
	methods: {
		reply(message_id){
			console.log("in reply " + message_id);
		},
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
		//	console.log("deleteMessage " + message_id);
			return this.$fhcApi.factory.messages.person.deleteMessage(message_id)
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
			//console.log("action new message");

		},
		reload() {
			this.$refs.table.reloadTable();
		},
	},
	computed: {
		statusText(){
			return {
				0: this.$p.t('messsages', 'unread'),
				1: this.$p.t('messsages', 'read'),
				2: this.$p.t('messsages', 'archived'),
				3: this.$p.t('messsages', 'deleted')
			}
		}
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