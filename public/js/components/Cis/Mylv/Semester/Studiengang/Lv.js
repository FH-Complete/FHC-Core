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
		positiv: Boolean,
		note_index: String
	},
	data: () => {
		return {
			pruefungenData: null,
			info: null,
			menu: null,
			preselectedMenuItem: null,
		}
	},
	computed: {
		gradeColor() {
			// early return if value is null or undefined
			if (this.positiv == null) return;
			// returns a suitable color for the given grade
			if (this.positiv)
			{
				return 'var(--fhc-cis-grade-positive)';
			}
			else
			{
				return 'var(--fhc-cis-grade-negative)';
			}
		},
		is_organisatorische_einheit(){
			return this.menu == "organisatorische_einheit";
		},
		emptyMenu(){
			return !this.menu || !Array.isArray(this.menu) || Array.isArray(this.menu) && this.menu.length == 0;
		},
		bodyStyle() {return {};
			const bodyStyle = {};
			if (this.farbe)
				bodyStyle['background-color'] = '#' + this.farbe;
			return bodyStyle;
		},
		grade() {
			const languageIndex = this.$p.user_language.value === 'English' ? 1 : 0
			return this.benotung ? this.znotebez[languageIndex] || this.lvnotebez[languageIndex] || null : null;
		},
		LvHasPruefungenInformation(){
			return this.pruefungenData && this.pruefungenData.length > 0;
		},
	},
	methods: {
		
		fetchMenu(lehrveranstaltung_id = this.lehrveranstaltung_id, studien_semester = this.studien_semester){
			return this.$fhcApi.factory.addons.getLvMenu(lehrveranstaltung_id, studien_semester)
				.then(res => {
					this.menu = res.data;
				})
				.catch((error) => {
					this.$fhcAlert.handleSystemError(error);
					this.menu = [];
				});
		},

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
			// early return if the pruefungenData is empty or not set
			if (!this.LvHasPruefungenInformation) return;

			LvPruefungen.popup({
				pruefungenData: this.pruefungenData, 
				bezeichnung: this.bezeichnung
			});
		},
		openInfos() {
			if (!this.info) {
				this.info = true;
				// TODO(chris): load all this params on ajax?
				LvInfo.popup({
					lehrveranstaltung_id: this.lehrveranstaltung_id, 
					bezeichnung: this.bezeichnung,
					bezeichnung_eng: this.bezeichnung_eng,
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
			this.fetchMenu(this.lehrveranstaltung_id, newValue);
		}
	},
	created(){
		this.$fhcApi.factory.lehre.getStudentPruefungen(this.lehrveranstaltung_id)
		.then(res => res.data)
		.then(pruefungen =>{
			this.pruefungenData = pruefungen;
		}); 
		
	},
	mounted() {
		this.fetchMenu(this.lehrveranstaltung_id, this.studien_semester);
	},
	template: /*html*/`<div class="mylv-semester-studiengang-lv card">
		<lv-uebersicht ref="lvUebersicht" :preselectedMenu="preselectedMenuItem" :event="{
			lehrveranstaltung_id: lehrveranstaltung_id,
			studiensemester_kurzbz:studien_semester,
			lehrfach_bez:studien_semester,
			stg_kurzbzlang:studien_semester,
		}"/>

		<div class="p-2" :class="is_organisatorische_einheit?'':'card-header'">
			<!-- {{module}} if the module of the lv is important then query the module from the api endpoint for LV-->
			<h6 class="fw-bold" v-if="is_organisatorische_einheit" >{{ $p.t('lehre/organisationseinheit') }}:</h6>
			<h6 class="mb-0">{{$p.user_language.value === 'English' ? bezeichnung_eng : bezeichnung}}</h6>
		</div>
		<div v-if="!emptyMenu" class="card-body " :style="bodyStyle">
			<template v-if="menu">
				<ul class="list-group border-top-0 border-bottom-0 rounded-0">
					<li :type="menuItem.c4_link ? 'button' : null" v-for="menuItem in menu" class="list-group-item border-0 " >
						<div class="d-flex flex-row"  :data-bs-toggle="menuItem.c4_moodle_links?.length ? 'dropdown' : null">
							<div class="mx-4">
								<i :class="[menuItem.c4_icon2 ? menuItem.c4_icon2 : 'fa-solid fa-pen-to-square', !menuItem.c4_link ? 'unavailable' : null ]"></i>
							</div>
							<a
							class="text-decoration-none text-truncate"
							:id="'moodle_links_'+lehrveranstaltung_id"
							:class="{'link-dark':menuItem.c4_link, 'unavailable':!menuItem.c4_link, 'dropdown-toggle':menuItem.c4_moodle_links?.length }"
							:target="menuItem.c4_target"
							:href="c4_link(menuItem) ? c4_link(menuItem) : null">
								{{ menuItem.phrase ? $p.t(menuItem.phrase) : menuItem.name}}
							</a>
							</div>
							<ul v-if="menuItem.c4_moodle_links?.length" class="dropdown-menu p-0" :aria-labelledby="'moodle_links_'+lehrveranstaltung_id">
								<li v-for="item in menuItem.c4_moodle_links"><a class="dropdown-item border-bottom" :href="item.url">{{item.lehrform}}</a></li>
							</ul>
					</li>
				</ul>
			</template>
			<template v-else>
				<div class="text-center d-flex justify-content-center align-items-center h-100" >
					<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
				</div>
			</template>
		</div>
		<div v-if="!emptyMenu" class="card-footer">
			<div class="row">
				<!-- template for the LV if there are multiple pruefungen -->
				<template v-if="LvHasPruefungenInformation">
					<a href="#" class="col-auto text-start text-decoration-none" @click.prevent="openPruefungen">
						<i class="fa fa-check text-success" v-if="positiv"></i>
						<span class="ps-1" :style="'color:'+gradeColor">{{ grade || $p.t('lehre/noGrades') }}</span>
					</a>
				</template>
				<!-- template for the LV with no pruefungen -->
				<template v-else>
					<span  class="col-auto text-start text-decoration-none" >
						<i class="fa fa-check text-success" v-if="positiv"></i>
						<span class="ps-1" :style="'color:'+gradeColor">{{ grade || $p.t('lehre/noGrades') }}</span>
					</span>
				</template>
			</div>
		</div>
	</div>`
};