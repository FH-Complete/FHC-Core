import TableLvList from "../../Stv/Studentenverwaltung/Details/Lehrveranstaltungstermine/ListLehrveranstaltungstermine.js";
import ApiLVTermine from "../../../api/lehrveranstaltung/lvtermine.js";

export default {
	name: "LVTabTermine",
	components: {
		TableLvList
	},
	props: {
		modelValue: Object,
	},
	data() {
		return {
			endpoint: ApiLVTermine
		};
	},
	template: `
	<div class="lv-details-course-list h-100 d-flex flex-column">
		<table-lv-list ref="tbl_course_list" :id="modelValue.lehrveranstaltung_id" :endpoint="endpoint"></table-lv-list>
	</div>`
};