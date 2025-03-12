import TableMessages from "./Details/TableMessages.js";
//import NewMessage from "./Details/NewMessage.js";
import FormOnly from "./Details/NewMessage/NewDiv.js";
import FhcApi from "../../../../public/js/plugin/FhcApi.js";
import Phrasen from "../../../../public/js/plugin/Phrasen.js";

//TODO(Manu) Only if openMode == modal
import MessageModal from "../Messages/Details/NewMessage/Modal.js";
export default {
	components: {
		TableMessages,
	//	NewMessage,
		FormOnly,
		FhcApi,
		Phrasen,
		MessageModal
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
		typeId: {
			type: String,
			required: true,
			validator(value) {
				return [
					'prestudent_id',
					'uid',
					'person_id',
					'mitarbeiter_uid'
				].includes(value)
			}
		},
		id: {
			type: [Number, String],
			required: true
		},
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
			default: 'modal',
			validator(value) {
				return [
					'window',
					'newTab',
					'modal',
					'inSamePage'
				].includes(value)
			}
		}
	},
	data() {
		return {
			isVisibleDiv: false,
			messageId: null
		}
	},
	methods: {
		reloadTable(){
			this.$refs.templateTableMessage.reload();
		},
		handleMessage(id, typeId, messageId){
			this.messageId = messageId;
			if (this.openMode == "window") {
				this.openInNewWindow(id, typeId, messageId);
			}
			else if (this.openMode == "newTab"){
				this.openInNewTab(id, typeId, messageId);
			}
			else if (this.openMode == "modal"){
				this.$refs.modalMsg.show();
			}
			else if (this.openMode == "inSamePage"){
				this.isVisibleDiv = true;
			}
			else
				console.log("no valid openMode");
		},
		openInNewTab(id, typeId, messageId= null){

			let path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

			if (messageId){
				path += "/NeueNachricht/" + id + "/" + typeId + "/" + messageId;
			}

			else {
				path += "/NeueNachricht/" + id + "/" + typeId;
			}

			const newTab = window.open(path, "_blank");
		},
		openInNewWindow(id, typeId, messageId){
			let path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

			if (messageId){
				path += "/NeueNachricht/" + id + "/" + typeId + "/" + messageId;
			}

			else {
				path += "/NeueNachricht/" + id + "/" + typeId;
			}

			const width = Math.round(window.innerWidth * 0.75);
			const height = Math.round(window.innerHeight * 0.75);
			const left = Math.round((window.innerWidth - width) / 2);
			const top = Math.round((window.innerHeight - height) / 2);

			const newWindow = window.open(path, "_blank", `width=${width},height=${height},left=${left},top=${top}`);
		},
		resetMessageId(){
			this.messageId = null;
		}

	},
	template: `
	<div class="core-messages h-100 pb-3">

		<message-modal
			ref="modalMsg"
			:type-id="typeId"
			:id="id"
			:message-id="messageId"
			:endpoint="endpoint"
			:openMode="openMode"
			@reloadTable="reloadTable"
			@resetMessageId="resetMessageId"
			>
		</message-modal>
		
		<!--in same page-->
		<div v-if="isVisibleDiv">
			<form-only
				ref="templateNewMessage"
				:temp-type-id="typeId"
				:temp-id="id"
				:temp-message-id="messageId"
				:endpoint="endpoint"
				:openMode="openMode"
				@reloadTable="reloadTable"
			>
			</form-only>
		</div>

		
		<div v-if="showTable">
			<table-messages
				ref="templateTableMessage"
				:type-id="typeId"
				:id="id"
				:endpoint="endpoint"
				:messageLayout="messageLayout"
				:openMode="openMode"
				@newMessage="handleMessage"		
				@replyToMessage="handleMessage"
			>		
			</table-messages>
		</div>
		<div v-else>
			<div class="col-md-2 mt-4">
				<br>
				<button type="button" class="btn btn-primary" @click="handleMessage(id, typeId)">{{ $p.t('messages', 'neueNachricht') }}</button>
			</div>
		</div>
	</div>
	`

}