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
	},
	data() {
		return {}
	},
	template: `
	<div class="core-messages h-100 pb-3">
	<p>endpoint Messages.js: {{endpoint}}</p>
		<div v-if="showNew">
			<NewMessage
				
			>
			</NewMessage>
		</div>
		
		<div v-if="showTable">
			<TableMessages
				:type-id="typeId"
				:id="id"
				:endpoint="endpoint"
				:messageLayout="messageLayout"
			>
			
			</TableMessages>
		</div>
		
	</div>
	`

}