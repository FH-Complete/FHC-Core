import TableLvList from "../../Stv/Studentenverwaltung/Details/Lehrveranstaltungstermine/ListLehrveranstaltungstermine.js";
import ApiLETermine from "../../../api/lehrveranstaltung/letermine.js";

export default {
	name: "LETabTermine",
	components: {
		TableLvList
	},
	props: {
		modelValue: Object,
	},
	data() {
		return {
			endpoint: ApiLETermine
		};
	},
	template: `
	<div class="le-details-course-list h-100 d-flex flex-column">
		<table-lv-list ref="tbl_course_list" :id="modelValue.lehreinheit_id" :endpoint="endpoint"></table-lv-list>
	</div>`
};