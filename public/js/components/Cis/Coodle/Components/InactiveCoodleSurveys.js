import { CoreFilterCmpt } from "../../../filter/Filter.js";

import { dateFilter } from "../../../../tabulator/filters/Dates.js";
import CoodleApi from "../../../../api/factory/coodle.js";

export default {
	name: "InactiveCoodleSurveys",
	components: { CoreFilterCmpt },
	emits: ["showSurveyDetails"],
	data() {
		return {
			inactiveSurveys: [],
			inactiveSurveysTableConfig: {
				persistenceID: "inactiveSurveysTable",
				minHeight: 300,
				layout: "fitColumns",
				columns: [
					{
						title: "Title",
						field: "title",
						headerFilter: true,
						minWidth: 150,
					},
					{
						title: "Creator",
						field: "creatorName",
						headerFilter: true,
						minWidth: 100,
					},
					{
						title: "Started on",
						field: "createdAt",
						headerFilter: true,
						minWidth: 100,
						formatter: "datetime",
						formatterParams: this.datetimeFormatterParams(),
						headerFilterFunc: "dates",
						headerFilter: dateFilter,
					},
					{
						title: "Completed on",
						field: "completedAt",
						headerFilter: true,
						minWidth: 100,
						formatter: "datetime",
						formatterParams: this.datetimeFormatterParams(),
						headerFilterFunc: "dates",
						headerFilter: dateFilter,
					},
					{
						title: "Canceled on",
						field: "canceledAt",
						headerFilter: true,
						minWidth: 100,
						formatter: "datetime",
						formatterParams: this.datetimeFormatterParams(),
						headerFilterFunc: "dates",
						headerFilter: dateFilter,
					},
				],
				data: [],
			},
			isFetchingSurveys: false,
		};
	},
	methods: {
		async fetchInactiveSurveys() {
			this.isFetchingSurveys = true;
			const surveysResponse = await this.$api.call(
				CoodleApi.getInactiveSurveys(),
			);
			this.isFetchingSurveys = false;

			if (surveysResponse.meta.status !== "success") return;

			this.inactiveSurveys = surveysResponse.data.map((surveyData) => {
				return {
					id: surveyData.id,
					title: surveyData.title,
					createdAt: surveyData.created_at.split(".")[0],
					completedAt: surveyData.completed_at?.split(".")[0],
					canceledAt: surveyData.canceled_at?.split(".")[0],
					creator: {
						uid: surveyData.creator.uid,
						name: surveyData.creator.name,
					},
				};
			});

			this.inactiveSurveysTableConfig.data = this.inactiveSurveys.map(
				(survey) => {
					return {
						id: survey.id,
						title: survey.title,
						creatorName: survey.creator.name,
						createdAt: survey.createdAt,
						completedAt: survey.completedAt,
						canceledAt: survey.canceledAt,
					};
				},
			);
		},
		afterInactiveSurveysTableBuilt() {
			this.$refs.inactiveSurveysTable.tabulator.on(
				"rowClick",
				(event, row) => {
					this.$emit("showSurveyDetails", {
						surveyId: row._row.data.id,
					});
				},
			);
		},
		datetimeFormatterParams: function () {
			const params = {
				inputFormat: "yyyy-MM-dd hh:mm:ss",
				outputFormat: "dd.MM.yyyy",
				invalidPlaceholder: "(invalid date)",
				timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			};
			return params;
		},
	},
	async created() {
		await this.fetchInactiveSurveys();
	},
	template: /*html*/ `
	<div class="card mb-4" style="height:100%">
		<div class="card-body">
			<div
				v-if="isFetchingSurveys"
				class="d-flex justify-content-center align-items-center mt-5"
			>
				<div class="spinner-border" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>
			<div
				v-else-if="!inactiveSurveys?.length"
				class="d-flex justify-content-center align-items-center mt-5"
			>
				<h3 class="fw-bold">{{ "No active surveys found!" }}</h3>
			</div>
			<div v-else class="d-flex flex-row overflow-x-auto">
				<div class="flex-shrink">
					<core-filter-cmpt
						@tableBuilt="afterInactiveSurveysTableBuilt()"
						:noColumnFilter="true"
						:title="'Past Surveys'"
						:ref="'inactiveSurveysTable'"
						:tabulatorOptions="inactiveSurveysTableConfig"
						:sideMenu="false"
						tableOnly
					/>
				</div>
			</div>
		</div>
	</div>
	`,
};
