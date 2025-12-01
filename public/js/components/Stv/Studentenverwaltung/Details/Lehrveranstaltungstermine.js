import TableLvList from "./Lehrveranstaltungstermine/ListLehrveranstaltungstermine.js";
import ApiStvCoursedates from "../../../../api/factory/stv/coursedates.js";


export default {
	name: "TabCourseList",
	components: {
		TableLvList
	},
	props: {
		modelValue: Object,
	},
	data() {
		return {
			endpoint: ApiStvCoursedates
		};
	},
	template: `
	<div class="stv-details-course-list h-100 d-flex flex-column">	
		<table-lv-list ref="tbl_course_list" :id="modelValue.uid" :endpoint="endpoint"></table-lv-list>
	</div>`
};