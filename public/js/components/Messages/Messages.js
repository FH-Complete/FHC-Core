import TableMessages from "./Details/TableMessages.js";
import FormOnly from "./Details/NewMessage/NewDiv.js";
import MessageModal from "../Messages/Details/NewMessage/Modal.js";
export default {
	name: "MessagesComponent",
	components: {
		TableMessages,
		FormOnly,
		MessageModal
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		}
	},
	props: {
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
			type: Array,
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
		getControllerUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/NeueNachricht';
		},
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
				if(!messageId)
					this.$refs.modalMsg.resetForm();
				this.$refs.modalMsg.show();
			}
			else if (this.openMode == "inSamePage"){
				console.log("in same Page");
				this.isVisibleDiv = true;
				if(messageId)
					this.$refs.templateNewDivMessage.loadReplyData(messageId);
				else
					this.$refs.templateNewDivMessage.resetForm();

				this.$refs.templateNewDivMessage.showTemplate();
			}
			else
				console.log("no valid openMode");
		},
		openInNewTab(id, typeId, messageId=null){
			if(id.length > 1)
			{
				this.$refs['newMsgForm'].submit();
				return;
			}

			let path = this.getControllerUrl();

			if (messageId){
				path += "/" + encodeURIComponent(id) + "/" + encodeURIComponent(typeId) + "/" + encodeURIComponent(messageId);
			}

			else {
				path += "/" + encodeURIComponent(id) + "/" + encodeURIComponent(typeId);
			}

			const newTab = window.open(path, "_blank");
		},
		openInNewWindow(id, typeId, messageId){
			const width = Math.round(window.innerWidth * 0.75);
			const height = Math.round(window.innerHeight * 0.75);
			const left = Math.round((window.innerWidth - width) / 2);
			const top = Math.round((window.innerHeight - height) / 2);

				if(id.length > 1)
			{
				const newWindow = window.open('', "NewMsgWindow", `width=${width},height=${height},left=${left},top=${top}`);
				this.$refs['newMsgForm'].submit();
				return;
			}

			let path = this.getControllerUrl();

			if (messageId){
				path += "/" + encodeURIComponent(id) + "/" + encodeURIComponent(typeId) + "/" + encodeURIComponent(messageId);
			}

			else {
				path += "/" + encodeURIComponent(id) + "/" + encodeURIComponent(typeId);
			}

			const newWindow = window.open(path, "_blank", `width=${width},height=${height},left=${left},top=${top}`);
		},
		resetMessageId(){
			this.messageId = null;
		}

	},
	template: `
	<div class="core-messages h-100 pb-3">
		<!-- TODO(bh) set target _self for debugging post but _blank for newTab -->
		<form ref="newMsgForm"
			method="post"
			:action="getControllerUrl()"
			:target="(openMode === 'window') ? 'NewMsgWindow' : '_blank'"
		>
			<input type="hidden" name="typeid" :value="typeId">
			<input type="hidden" name="ids" :value="id">
		</form>

		<message-modal
			ref="modalMsg"
			:type-id="typeId"
			:id="id"
			:message-id="messageId"
			:openMode="openMode"
			@reloadTable="reloadTable"
			@resetMessageId="resetMessageId"
			>
		</message-modal>
		
		<!--in same page-->
		<div v-show="isVisibleDiv" class="overflow-auto m-3" style="max-height: 500px; border: 1px solid #ccc;">
			<form-only
				ref="templateNewDivMessage"
				:type-id="typeId"
				:id="id"
				:message-id="messageId"
				:openMode="openMode"
				@reloadTable="reloadTable"
			>
			</form-only>
		</div>
	
		<div v-if="showTable && id.length==1">
			<table-messages
				ref="templateTableMessage"
				:type-id="typeId"
				:id="id"
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