import TableMessages from "./Details/TableMessages.js";
import NewMessage from "./Details/NewMessage.js";
import FormOnly from "./Details/NewMessage/NewDiv.js";
import FhcApi from "../../../../public/js/plugin/FhcApi.js";
import Phrasen from "../../../../public/js/plugin/Phrasen.js";
export default {
	components: {
		TableMessages,
		NewMessage,
		FormOnly,
		FhcApi,
		Phrasen
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		}
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
		showNew: Boolean,
		showTable: Boolean,
		messageLayout: {
			type: String,
			default: 'twoColumnsTableLeft',
			validator(value) {
				return [
					'twoColumnsTableLeft',
					'listTableTop'
				].includes(value)
			}
		},
		openMode: {
			type: String,
			default: 'window',
			validator(value) {
				return [
					'window',
					'newTab',
					'modal',
					'showDiv'
				].includes(value)
			}
		}
	},
	data() {
		return {
			showDiv: false
		}
	},
	methods: {
		reloadTable(){
			this.$refs.templateTableMessage.reload();
		},
		newMessage(id, typeId){
			if (this.openMode == "window") {
				this.openInNewWindow(id, typeId);
			}
			else if (this.openMode == "newTab"){
				this.openInNewTab(id, typeId);
			}
			else if (this.openMode == "modal"){
				this.openInModal(id, typeId);
			}
			else if (this.openMode == "showDiv"){
				this.$refs.templateNewMessage.showTemplate(id, typeId);
			}
			else
				console.log("no valid openMode");
		},
		openInDiv(id, typeId){
			this.$refs.templateNewMessage.showTemplate(id, typeId);
			//this.showDiv = true; //local variante
			//this.$refs.templateNewMessage.showTemplate();
		},
		openInModal(id, typeId){
			//TODO(manu) define bs-modal in this component
			this.$refs.templateNewMessage.$refs.modalMsg.show();
		},
		openInNewTab(id, typeId){
			//TODO(MANU) check if array of ids...
/*			let path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
			path += "/NeueNachricht/" + this.id + "/" + this.typeId;*/

			//als param
			let path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
			path += "/NeueNachricht/" + id + "/" + typeId;

			const newTab = window.open(path, "_blank");
		},
		openInNewWindow(id, typeId){
			//TODO(MANU) check if array of ids...
			let path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
			path += "/NeueNachricht/" + id + "/" + typeId;

			const newTab = window.open(path, "_blank");

			const width = Math.round(window.innerWidth * 0.75);
			const height = Math.round(window.innerHeight * 0.75);
			const left = Math.round((window.innerWidth - width) / 2);
			const top = Math.round((window.innerHeight - height) / 2);

			const newWindow = window.open(path, "_blank", `width=${width},height=${height},left=${left},top=${top}`);
		},

	},
	template: `
	<div class="core-messages h-100 pb-3">

		<message-modal
			ref="modalMsg"
			:type-id="typeId"
			:id="id"
			:endpoint="endpoint"
			:openMode="openMode"
			@reloadTable="reloadTable"
			>
		</message-modal>

<!--		<div>
			<button class="btn btn-secondary m-1" @click="openInDiv(id,typeId)">Open in Div</button>
			<button class="btn btn-secondary" @click="openInModal(id,typeId)">Open in Modal</button>
			<button class="btn btn-secondary m-1" @click="openInNewTab(id,typeId)">Open in Tab</button>
			<button class="btn btn-secondary" @click="openInNewWindow(id,typeId)">Open in Page</button>
		</div>-->
<!--	<p>endpoint Messages.js: {{endpoint}}</p>-->
		<div v-if="showNew">
			<new-message
				ref="templateNewMessage"
				:type-id="typeId"
				:id="id"
				:endpoint="endpoint"
				:openMode="openMode"
				@reloadTable="reloadTable"
			>
			</new-message>
		</div>
		
		<div v-if="showTable">
			<table-messages
				ref="templateTableMessage"
				:type-id="typeId"
				:id="id"
				:endpoint="endpoint"
				:messageLayout="messageLayout"
				:openMode="openMode"
				@newMessage="newMessage"
			>
			
			</table-messages>

			<!--working also with form_only-->
			<div v-if="showDiv">
				<form-only
					ref="templateNewForm"
					:type-id="typeId"
					:id="id"
					:endpoint="endpoint"
					:openMode="openMode"
					@reloadTable="reloadTable"
				>
				</form-only>
			</div>

		</div>
		
	</div>
	`

}