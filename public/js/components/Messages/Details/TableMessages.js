import {CoreFilterCmpt} from "../../filter/Filter.js";

export default {
	components: {
		CoreFilterCmpt,
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
					{title: "datum", field: "format_insertamum"},
					{title: "sender", field: "sender"},
					{title: "recipient", field: "recipient"},
					{title: "sepersonid", field: "sepersonid"},
					{title: "repersonid", field: "repersonid"},
					{title: "status", field: "status"},
					{
						title: 'Aktionen', field: 'actions',
						width: 100,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('ui', 'notiz_edit');
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.title = this.$p.t('notiz', 'notiz_delete');
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteNotiz(cell.getData().notiz_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '250',
				selectableRangeMode: 'click',
				selectable: true,
				index: 'message_id',
				persistenceID: 'core-message'
			},
		}
	},
/*	computed: {
		statusText(){
			0: this.$p.t('messsages', 'unread'),
			1: this.$p.t('messsages', 'read'),
			2: this.$p.t('messsages', 'archived'),
			0: this.$p.t('messsages', 'unread'),
			3: this.$p.t('person', 'deleted'),
		}
	},*/
	template: `
	<div class="messages-detail-table">
		<h4>Table Messages</h4>
		<p>type_id: {{typeId}}</p>
		<p>id: {{id}}</p>
		<p>endpoint: {{endpoint}}</p>
		
<!--		{{statusText}}-->
		
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('global', 'nachricht')"
			@click:new="actionNewNotiz"
			>
		</core-filter-cmpt>
	</div>
	`

}