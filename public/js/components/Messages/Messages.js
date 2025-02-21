import TableMessages from "./Details/TableMessages.js";
import NewMessage from "./Details/NewMessage.js";

export default {
	components: {
		TableMessages,
		NewMessage
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
					'modal',
					'showDiv'
				].includes(value)
			}
		}
	},
	data() {
		return {}
	},
	methods: {
		showNewMessageTemplate(){
			this.$refs.templateNewMessage.showTemplate();
		},
		reloadTable(){
			this.$refs.templateTableMessage.reload();
		}
	},
	template: `
	<div class="core-messages h-100 pb-3">
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
				@showNewMessageTemplate="showNewMessageTemplate"
			>
			
			</table-messages>
		</div>
		
	</div>
	`

}