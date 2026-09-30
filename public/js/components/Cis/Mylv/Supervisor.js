import {CoreFilterCmpt} from "../../../components/filter/Filter.js";

import ApiBetreuungen from "../../../api/factory/betreuungen.js";

export default {
	name: "Supervisor",
	components: {
		CoreFilterCmpt
	},
	props: {
		semester: null | String,
	},
	data() {
		return {
			students: [],
			isFetchingStudents: false,
			supervisorTableOptions: {
				index: 'lehrveranstaltung_id',
				layout: 'fitColumns',
				columns: [
					{
						titlePhrase: "betreuungen/studiengang",
						title: "placeholder",
						field: "studiengang",
						widthGrow: 1,
					},
					{
						titlePhrase: "betreuungen/semester",
						title: "placeholder",
						field: "semester",
						widthGrow: 1,
					},
					{
						titlePhrase: "betreuungen/stunden",
						title: "placeholder",
						field: "stunden",
						widthGrow: 1,
					},
					{
						titlePhrase: "betreuungen/lvBezeichnung",
						title: "placeholder",
						field: "bezeichnung",
						widthGrow: 3,
					},
					{
						titlePhrase: "betreuungen/student",
						title: "placeholder",
						field: "name",
						widthGrow: 3,
						formatter: "link",
						formatterParams: {
							target:"_blank",
							url: (cell) => {
								const uid = cell._cell.row.data.uid;
								// todo: get full profilview url (or just use cell click instead of link formatter)
								return this.$router.resolve("ProfilView").href;
							},
						},
					},
					{
						titlePhrase: "betreuungen/betreuungsart",
						title: "placeholder",
						field: "beutreuerart_beschreibung",
						widthGrow: 3,
					},
					{
						titlePhrase: "betreuungen/titelProjektarbeit",
						title: "placeholder",
						field: "titel",
						widthGrow: 6,
					},
				],
				locale: true,
				persistence: false,
				persistenceID: "supervisorTable",
			},
		};
	},
	watch: {
		semester() {
			this.getStudents();
		},
	},
	methods: {
		async getStudents() {
			if (!this.$props.semester?.length) return;

			this.students = [];
			this.isFetchingStudents = true;

			const studentsResponse = await this.$api.call(
				ApiBetreuungen.getBetreuungen(this.$props.semester),
			);
			if (studentsResponse.meta.status === "success") {
				this.students = studentsResponse.data;
				this.setTableData();
			}

			this.isFetchingStudents = false;
		},
		handleTableBuilt() {
			this.setTableData();
		},
		setTableData() {
			if (this.$refs.supervisorTable && this.students?.length) {
				this.$refs.supervisorTable.tabulator.setData(
					this.students
				);
			}
		},
	},
	mounted() {
		this.getStudents();
		console.log(this.$router);
	},
	template: /*html*/ `
	<div>
		<div v-if="isFetchingStudents" class="d-flex flex-row justify-content-center pt-3">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
		<div v-else-if="students?.length">
			<span>{{ students.length + " supervised students" }}</span>
			<core-filter-cmpt
				@tableBuilt="handleTableBuilt()"
				ref="supervisorTable"
				tableOnly
				:tabulator-options="supervisorTableOptions"
				:sideMenu="false"
			/>
		</div>
		<div v-else class="d-flex flex-row justify-content-center pt-3">
			<h4>No supervised students found!</h4>
		</div>
	</div>
	`,
};
