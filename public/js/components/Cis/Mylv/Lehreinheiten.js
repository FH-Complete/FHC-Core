import { CoreFilterCmpt } from "../../filter/Filter.js";

import ApiLehreinheit from "../../../api/factory/lehreinheit.js";

export default {
	name: "Lehreinheiten",
	components: {
		CoreFilterCmpt,
	},
	props: {
		semester: null | String,
	},
	data() {
		return {
			lehreinheiten: [],
			isFetchingLehreinheiten: false,
			lehreinheitTableOptions: {
				layout: "fitColumns",
				columns: [
					{
						titlePhrase: "lehreinheiten/inputGrades",
						title: "placeholder",
						field: "inputGrades",
						widthGrow: 2,
						formatter: () => {
							return '<i class="fa-solid fa-up-right-from-square"></i>';
						},
						hozAlign: "center",
						cellClick: (event, cell) => {
							window.open(
								FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Compat/legacy/cis/private/lehre/benotungstool/lvgesamtnoteverwalten.php?lvid=" +
									cell._cell.row.data.lvId +
									"&stsem=" +
									this.$props.semester,
								"_blank",
							);
						},
					},
					{
						title: "ID",
						field: "lehreinheitId",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/subject",
						title: "placeholder",
						field: "subject",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/lehrform",
						title: "placeholder",
						field: "lehrform",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/title",
						title: "placeholder",
						field: "title",
						widthGrow: 3,
					},
					{
						titlePhrase: "lehreinheiten/lector",
						title: "placeholder",
						field: "lector",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/dp",
						title: "placeholder",
						field: "stg",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/sem",
						title: "placeholder",
						field: "semester",
						widthGrow: 1,
					},
					{
						titlePhrase: "lehreinheiten/groups",
						title: "placeholder",
						field: "groups",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/room",
						title: "placeholder",
						field: "room",
						widthGrow: 3,
					},
					{
						titlePhrase: "lehreinheiten/alt",
						title: "placeholder",
						field: "alternativeRoom",
						widthGrow: 3,
					},
					{
						titlePhrase: "lehreinheiten/block",
						title: "placeholder",
						field: "block",
						widthGrow: 1,
					},
					{
						titlePhrase: "lehreinheiten/wr",
						title: "placeholder",
						field: "weeklyRhythm",
						widthGrow: 1,
					},
					{
						titlePhrase: "lehreinheiten/hrs",
						title: "placeholder",
						field: "hours",
						widthGrow: 1,
						bottomCalc: "sum",
						bottomCalcParams: { precision: 2 },
					},
					{
						titlePhrase: "lehreinheiten/cw",
						title: "placeholder",
						field: "cw",
						widthGrow: 1,
					},
					{
						titlePhrase: "lehreinheiten/registrationWindowStart",
						title: "placeholder",
						field: "registrationWindowStart",
						widthGrow: 2,
					},
					{
						titlePhrase: "lehreinheiten/registrationWindowEnd",
						title: "placeholder",
						field: "registrationWindowEnd",
						widthGrow: 2,
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
			this.getLehreinheiten();
		},
	},
	methods: {
		async getLehreinheiten() {
			if (!this.$props.semester?.length) return;

			this.lehreinheiten = [];
			this.isFetchingLehreinheiten = true;

			const lehreinheitenResponse = await this.$api.call(
				ApiLehreinheit.getLehreinheitenBySemester(this.$props.semester),
			);
			if (lehreinheitenResponse.meta.status === "success") {
				this.lehreinheiten = lehreinheitenResponse.data.map(
					(lehreinheitData) => {
						return {
							lehreinheitId: lehreinheitData.lehreinheit_id,
							lvId: lehreinheitData.lehrveranstaltung_id,
							subject: lehreinheitData.lehrfach,
							lehrform: lehreinheitData.lehrform,
							title: lehreinheitData.lv_bezeichnung,
							lector: lehreinheitData.lektor,
							stg: lehreinheitData.stg_kurzbz,
							semester: lehreinheitData.lv_semester,
							room: lehreinheitData.raumtyp,
							alternativeRoom: lehreinheitData.raumtypalternativ,
							block: lehreinheitData.stundenblockung,
							weeklyRhythm: lehreinheitData.wochenrythmus,
							hours: lehreinheitData.semesterstunden,
							cw: lehreinheitData.start_kw,
							registrationWindowStart:
								lehreinheitData.anmeldefenster_start,
							registrationWindowEnd:
								lehreinheitData.anmeldefenster_ende,
						};
					},
				);
			}
			this.setTableData();

			this.isFetchingLehreinheiten = false;
		},
		handleTableBuilt() {
			this.setTableData();
		},
		setTableData() {
			if (this.$refs.lehreinheitenTable && this.lehreinheiten?.length) {
				this.$refs.lehreinheitenTable.tabulator.setData(
					this.lehreinheiten,
				);
			}
		},
	},
	mounted() {
		this.getLehreinheiten();
	},
	template: /*html*/ `
	<div>
		<div v-if="isFetchingLehreinheiten" class="d-flex flex-row justify-content-center pt-3">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
		<div v-else-if="lehreinheiten?.length">
			<h5>{{ lehreinheiten.length + " teaching units" }}</h5>
			<core-filter-cmpt
				@tableBuilt="handleTableBuilt()"
				ref="lehreinheitenTable"
				tableOnly
				:tabulator-options="lehreinheitTableOptions"
				:sideMenu="false"
			/>
		</div>
		<div v-else class="d-flex flex-row justify-content-center pt-3">
			<h4>No teaching units found!</h4>
		</div>
	</div>
	`,
};
