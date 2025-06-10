import CoreNotiz from "../../Notiz/Notiz.js";
import ApiNotizLehreinheit from '../../../api/factory/notiz/lehreinheit.js';
export default {
	name: "LVTabNotiz",
	components: {
		CoreNotiz
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			endpoint: ApiNotizLehreinheit
		};
	},
	template: `
	<div class="stv-details-notizen h-100 pb-3">
		<core-notiz
			class="overflow-hidden"
			:endpoint="endpoint"
			ref="formc"
			notiz-layout="twoColumnsFormLeft"
			type-id="lehreinheit_id"
			:id="modelValue.lehreinheit_id"
			show-document
			show-tiny-mce
			showErweitert
			:visible-columns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</core-notiz>
	</div>
	`
};