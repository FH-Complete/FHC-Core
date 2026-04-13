import FhcSearchbar from "../components/searchbar/searchbar.js";
import CisMenu from "../components/Cis/Menu.js";
import PluginsPhrasen from "../plugins/Phrasen.js";
import Theme from "../plugins/Theme.js";

import ApiSearchbar from "../api/factory/searchbar.js";
import ApiLvPlan from "../api/factory/lvPlan.js";

const app = Vue.createApp({
	name: "CisApp",
	components: {
		FhcSearchbar,
		CisMenu,
	},
	data: function () {
		return {
			searchbaroptions: {
				origin: "cis",
				cssclass: "",
				calcheightonly: true,
				types: {
					employee: Vue.computed(() =>
						this.$p.t("search/type_employee"),
					),
					student: Vue.computed(() =>
						this.$p.t("search/type_student"),
					),
					room: Vue.computed(() => this.$p.t("search/type_room")),
					organisationunit: Vue.computed(() =>
						this.$p.t("search/type_organisationunit"),
					),
					cms: Vue.computed(() => this.$p.t("search/type_cms")),
					dms: Vue.computed(() => this.$p.t("search/type_dms")),
				},
				actions: {
					employee: {
						defaultaction: {
							type: "link",
							action: function (data) {
								return (
									FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Profil/View/" +
									data.uid
								);
							},
						},
						childactions: [],
					},
					student: {
						defaultaction: {
							type: "link",
							action: function (data) {
								return (
									FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Profil/View/" +
									data.uid
								);
							},
						},
						childactions: [],
					},
					room: {
						defaultaction: {
							type: "link",
							renderif: function (data) {
								if (data.content_id === null) {
									return false;
								}
								return true;
							},
							action: function (data) {
								const link =
									FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/CisVue/Cms/content/" +
									data.content_id;
								return link;
							},
						},
						childactions: [
							{
								label: "LV-Plan",
								icon: "fas fa-bookmark",
								type: "link",
								action: function (data) {
									const link =
										FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										"/CisVue/Cms/getRoomInformation/" +
										data.ort_kurzbz;
									return link;
								},
							},
							{
								label: "Rauminformation",
								icon: "fas fa-info-circle",
								type: "link",
								renderif: function (data) {
									if (data.content_id === null) {
										return false;
									}
									return true;
								},
								action: function (data) {
									const link =
										FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										"/CisVue/Cms/content/" +
										data.content_id;
									return link;
								},
							},
						],
					},
					organisationunit: {
						defaultaction: {
							type: "link",
							renderif: function (data) {
								if (data.mailgroup) {
									return true;
								}
								return false;
							},
							action: function (data) {
								const link = "mailto:" + data.mailgroup;
								return link;
							},
						},
						childactions: [],
					},
					cms: {
						defaultaction: {
							type: "link",
							action: function (data) {
								const link =
									FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/CisVue/Cms/content/" +
									data.content_id;
								return link;
							},
						},
						childactions: [],
					},
					dms: {
						defaultaction: {
							type: "link",
							action: function (data) {
								const link =
									FHC_JS_DATA_STORAGE_OBJECT.app_root +
									"cms/dms.php?id=" +
									data.dms_id;
								return link;
							},
						},
						childactions: [],
					},
				},
			},
		};
	},
	methods: {
		searchfunction: function (searchsettings) {
			return this.$api.call(ApiSearchbar.searchCis(searchsettings));
		},
	},
	async mounted() {
		const openOtherLvPlanAction = {
			label: Vue.computed(() => this.$p.t("lehre/stundenplan")),
			icon: "fas fa-calendar-days",
			type: "link",
			action: function (data) {
				const uid = JSON.parse(data.data).uid;
				const link =
					FHC_JS_DATA_STORAGE_OBJECT.app_root +
					FHC_JS_DATA_STORAGE_OBJECT.ci_router +
					"/Cis/OtherLvPlan/" +
					uid;
				return link;
			},
		};
		let checkPermissionOtherLvPlanResult = await this.$api.call(
			ApiLvPlan.checkPermissionOtherLvPlan(),
		);
		if (
			checkPermissionOtherLvPlanResult.meta.status === "success" &&
			checkPermissionOtherLvPlanResult.data
		) {
			this.searchbaroptions.actions.employee.childactions.push(
				openOtherLvPlanAction,
			);
			this.searchbaroptions.actions.student.childactions.push(
				openOtherLvPlanAction,
			);
		}
	},
});

FhcApps.makeExtendable(app);

app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000,
	},
});
app.use(PluginsPhrasen);
app.use(Theme);
app.mount("#cis-header");
