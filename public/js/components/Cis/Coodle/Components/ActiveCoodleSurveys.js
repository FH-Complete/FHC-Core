import { CoreFilterCmpt } from "../../../filter/Filter.js";

import { dateFilter } from "../../../../tabulator/filters/Dates.js";
import CoodleApi from "../../../../api/factory/coodle.js";

export default {
	name: "ActiveCoodleSurveys",
	components: { CoreFilterCmpt },
	emits: ["showSurveyDetails"],
	data() {
		return {
			activeSurveys: [],
			activeSurveysTableConfig: {
				persistenceID: "activeSurveysTable",
				minHeight: 300,
				movableColumns: false,
				layout: "fitColumns",
				initialSort: [{ column: "createdAt", dir: "desc" }],
				locale: true,
				pagination: true,
				paginationSize: 25,
				paginationCounter: "rows",
				columns: [
					{
						title: "placeholder",
						titlePhrase: "coodle/title",
						field: "title",
						headerFilter: true,
						minWidth: 150,
					},
					{
						title: "placeholder",
						titlePhrase: "coodle/creator",
						field: "creatorName",
						headerFilter: true,
						minWidth: 100,
					},
					{
						title: "placeholder",
						titlePhrase: "coodle/started_on",
						field: "createdAt",
						headerFilter: true,
						minWidth: 100,
						formatter: "datetime",
						formatterParams: this.datetimeFormatterParams(),
						headerFilterFunc: "dates",
						headerFilter: dateFilter,
					},
					{
						title: "placeholder",
						titlePhrase: "coodle/ends_on",
						field: "endsAt",
						headerFilter: true,
						minWidth: 100,
						formatter: "datetime",
						formatterParams: this.datetimeFormatterParams(),
						headerFilterFunc: "dates",
						headerFilter: dateFilter,
					},
				],
			},
			isFetchingSurveys: false,
		};
	},
	methods: {
		async fetchActiveSurveys() {
			this.isFetchingSurveys = true;
			const surveysResponse = await this.$api.call(
				CoodleApi.getActiveSurveys(),
			);
			this.isFetchingSurveys = false;

			if (surveysResponse.meta.status !== "success") return;

			this.activeSurveys = surveysResponse.data.map((surveyData) => {
				return {
					id: surveyData.id,
					title: surveyData.title,
					endsAt: surveyData.ends_at.split(".")[0],
					createdAt: surveyData.created_at.split(".")[0],
					creator: {
						uid: surveyData.creator.uid,
						name: surveyData.creator.name,
					},
				};
			});

			this.activeSurveysTableConfig.data = this.activeSurveys.map(
				(survey) => {
					return {
						id: survey.id,
						title: survey.title,
						creatorName: survey.creator.name,
						createdAt: survey.createdAt,
						endsAt: survey.endsAt,
					};
				},
			);
		},
		afterActiveSurveysTableBuilt() {
			this.$refs.activeSurveysTable.tabulator.on(
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
		await this.fetchActiveSurveys();
	},
	template: /*html*/ `
	<div class="card mb-4" style="height:100%">
		<div class="card-body">
			<div
				v-if="isFetchingSurveys"
				class="d-flex justify-content-center align-items-center mt-5"
			>
				<div class="spinner-border" role="status">
					<span class="visually-hidden">{{ $p.t("coodle/loading") }}</span>
				</div>
			</div>
			<div
				v-else-if="!activeSurveys?.length"
				class="d-flex justify-content-center align-items-center mt-5"
			>
				<h3 class="fw-bold">{{ $p.t("coodle/no_active_surveys_found") }}</h3>
			</div>
			<div v-else class="d-flex flex-row overflow-x-auto">
				<div class="flex-shrink">
					<core-filter-cmpt
						@tableBuilt="afterActiveSurveysTableBuilt()"
						:noColumnFilter="true"
						:title="'Active surveys'"
						:ref="'activeSurveysTable'"
						:tabulatorOptions="activeSurveysTableConfig"
						:sideMenu="false"
						tableOnly
					/>
				</div>
			</div>
		</div>
	</div>
	`,
};
