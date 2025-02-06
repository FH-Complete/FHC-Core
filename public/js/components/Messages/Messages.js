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
		showTable: Boolean
	},
	data() {
		return {}
	},
	template: `
	<div class="core-messages h-100 pb-3">
	<p>endpoint: {{endpoint}}</p>
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
			>
			
			</TableMessages>
		</div>
		
	</div>
	`

}