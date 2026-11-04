import { CoreFilterCmpt } from "../../../components/filter/Filter.js";

import ApiBetreuungen from "../../../api/factory/betreuungen.js";

export default {
	name: "Supervisor",
	components: {
		CoreFilterCmpt,
	},
	props: {
		semester: null | String,
	},
	data() {
		return {
			students: [],
			isFetchingStudents: false,
			supervisorTableOptions: {
				layout: "fitColumns",
				columns: [
					{
						titlePhrase: "betreuungen/studiengang",
						title: "placeholder",
						field: "studiengang",
						widthGrow: 1,
						formatter: "link",
						formatterParams: {
							target: "_blank",
							url: (cell) => {
								return "mailto:" + cell._cell.row.data.email;
							},
						},
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
						bottomCalc: "sum",
						bottomCalcParams: { precision: 2 },
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
							target: "_blank",
							url: (cell) => {
								return this.$router.resolve({
									name: "ProfilView",
									params: { uid: cell._cell.row.data.uid },
								}).href;
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
				this.students = studentsResponse.data.map((studentData) => {
					studentData.stunden = parseFloat(
						studentData.stunden,
					).toFixed(2);
					return { ...studentData };
				});
				this.setTableData();
			}

			this.isFetchingStudents = false;
		},
		handleTableBuilt() {
			this.setTableData();
		},
		setTableData() {
			if (this.$refs.supervisorTable && this.students?.length) {
				this.$refs.supervisorTable.tabulator.setData(this.students);
			}
		},
	},
	mounted() {
		this.getStudents();
	},
	template: /*html*/ `
	<div>
		<div v-if="isFetchingStudents" class="d-flex flex-row justify-content-center pt-3">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
		<div v-else-if="students?.length">
			<h5>{{ students.length + " " + $p.t("mylv/supervised_students") }}</h5>
			<core-filter-cmpt
				@tableBuilt="handleTableBuilt()"
				ref="supervisorTable"
				tableOnly
				:tabulator-options="supervisorTableOptions"
				:sideMenu="false"
			/>
		</div>
		<div v-else class="d-flex flex-row justify-content-center pt-3">
			<h4>{{ $p.t("mylv/no_supervised") }}</h4>
		</div>
	</div>
	`,
};
