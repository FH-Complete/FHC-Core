import TableLvList from "./Lehrveranstaltungstermine/ListLehrveranstaltungstermine.js";

export default {
	name: "TabCourseList",
	components: {
		TableLvList
	},
	props: {
		modelValue: Object,
	},
	data(){
		return {}
	},
	template: `
	<div class="stv-details-course-list h-100 d-flex flex-column">	
		<table-lv-list ref="tbl_course_list" :student="modelValue"></table-lv-list>
	</div>`
};