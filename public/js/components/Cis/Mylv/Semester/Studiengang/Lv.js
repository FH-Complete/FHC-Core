import LvPruefungen from "./Lv/Pruefungen.js";
import LvInfo from "./Lv/Info.js";
import Phrasen from "../../../../../mixins/Phrasen.js";
import LvUebersicht from "../../LvUebersicht.js";

// TODO(chris): L10n

export default {
	components:{
		LvUebersicht,
	},
	mixins: [
		Phrasen
	],
	inject: ['studien_semester'],
	props: {
		lehrveranstaltung_id: Number,
		bezeichnung: String,
		module: String,
		farbe: String,
		lvinfo: Boolean,
		benotung: Boolean,
		lvnote: String,
		znote: String,
		studiengang_kuerzel: String,
		semester: [String, Number],
		orgform_kurzbz: String,
		sprache: String,
		ects: String,
		incoming: Number,
		positiv: Boolean
	},
	data: () => {
		return {
			pruefungen: null,
			info: null,
			menu: null,
			preselectedMenuItem: null,
		}
	},
	computed: {
		bodyStyle() {return {};
			const bodyStyle = {};
			if (this.farbe)
				bodyStyle['background-color'] = '#' + this.farbe;
			return bodyStyle;
		},
		grade() {
			return this.benotung ? this.znote || this.lvnote || null : null;
		}
	},
	methods: {
		c4_link(menuItem) {
			if (!menuItem) return null;
			if (Array.isArray(menuItem.c4_moodle_links) && menuItem.c4_moodle_links.length) {
				return '#';
			}
			else {
				return menuItem.c4_link ?? null;
			}
		},
		openLvOption(menuItem){
			if (menuItem.id == "core_menu_mailanstudierende"){
				window.location.href = menuItem.c4_link;
			} else if (menuItem.id == "core_menu_digitale_anwesenheitslisten") {
				window.location.href = menuItem.c4_link;
			} else{
				this.preselectedMenuItem = menuItem;
				Vue.nextTick(() => {
					this.$refs.lvUebersicht.show();
				});
			}
		},
		openPruefungen() {
			if (!this.pruefungen) {
				this.pruefungen = true;
				LvPruefungen.popup({
					lehrveranstaltung_id: this.lehrveranstaltung_id, 
					bezeichnung: this.bezeichnung
				}).then(() => this.pruefungen = false).catch(() => this.pruefungen = false);
			}
		},
		openInfos() {
			if (!this.info) {
				this.info = true;
				// TODO(chris): load all this params on ajax?
				LvInfo.popup({
					lehrveranstaltung_id: this.lehrveranstaltung_id, 
					bezeichnung: this.bezeichnung,
					studiengang_kuerzel: this.studiengang_kuerzel,
					semester: this.semester,
					studien_semester: this.studien_semester,
					orgform_kurzbz: this.orgform_kurzbz,
					sprache: this.sprache,
					ects: this.ects,
					incoming: this.incoming
				}).then(() => this.info = false).catch(() => this.info = false);
			}
		}
	},
	watch:{
		studien_semester(newValue){
			this.$fhcApi.factory.addons.getLvMenu(this.lehrveranstaltung_id, newValue)
				.then(res => {
					this.menu = res.data;
				})
				.catch((error) => this.$fhcAlert.handleSystemError);	
		}
	},
	mounted() {
		this.$fhcApi.factory.addons.getLvMenu(this.lehrveranstaltung_id, this.studien_semester)
			.then(res => {
				this.menu = res.data;
			})
			.catch((error) => {
				this.$fhcAlert.handleSystemError(error);
				this.menu = [];
			});
	},
	template: /*html*/`<div class="mylv-semester-studiengang-lv card">
		<lv-uebersicht ref="lvUebersicht" :preselectedMenu="preselectedMenuItem" :event="{
			lehrveranstaltung_id: lehrveranstaltung_id,
			studiensemester_kurzbz:studien_semester,
			lehrfach_bez:studien_semester,
			stg_kurzbzlang:studien_semester,
		}"/>

		<div class="card-header">
			<!-- {{module}} if the module of the lv is important then query the module from the api endpoint for LV-->
			<h6 class="card-title">{{bezeichnung}}</h6>
		</div>
		<div class="card-body " :style="bodyStyle">
			<ul class="list-group border-top-0 border-bottom-0 rounded-0">
				<template v-if="menu">
					<li :type="menuItem.c4_link ? 'button' : null" v-for="menuItem in menu" class="list-group-item border-0 " >
						<div class="d-flex flex-row"  :data-bs-toggle="menuItem.c4_moodle_links?.length ? 'dropdown' : null">
							<div class="mx-4">
								<i :class="[menuItem.c4_icon2 ? menuItem.c4_icon2 : 'fa-solid fa-pen-to-square', !menuItem.c4_link ? 'unavailable' : null ]"></i>
							</div>
							<a class="text-decoration-none text-truncate" :id="'moodle_links_'+lehrveranstaltung_id"  :class="{'link-dark':menuItem.c4_link, 'unavailable':!menuItem.c4_link, 'dropdown-toggle':menuItem.c4_moodle_links?.length }" :target="menuItem.c4_target" :href="c4_link(menuItem) ? c4_link(menuItem) : null">
							{{menuItem.name}}
							</a>
							</div>
							<ul v-if="menuItem.c4_moodle_links?.length" class="dropdown-menu p-0" :aria-labelledby="'moodle_links_'+lehrveranstaltung_id">
								<li v-for="item in menuItem.c4_moodle_links"><a class="dropdown-item border-bottom" :href="item.url">{{item.lehrform}}</a></li>
							</ul>
					</li>
				</template>
				<template v-else>
					<li class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></li>
				</template>
			</ul>
		</div>
		<div class="card-footer">
			<div class="row">
				<a href="#" class="col-auto text-start text-decoration-none" @click.prevent="openPruefungen">
					<i class="fa fa-check text-success" v-if="positiv"></i>
					{{ grade || p.t('lehre/noGrades') }}
				</a>
				<!--
				Not used anymore because the lehrveranstaltungs informationen is available as a menu point in the lehrveranstaltungs optionen
				<div v-if="lvinfo" class="col text-end">
					<a class="card-link" href="#" @click.prevent="openInfos">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
					</a>
				</div>-->
			</div>
		</div>
	</div>`
};