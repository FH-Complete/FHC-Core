import LvPruefungen from "./Lv/Pruefungen.js";
import LvInfo from "./Lv/Info.js";
import Phrasen from "../../../../../mixins/Phrasen.js";

import ApiLehre from '../../../../../api/factory/lehre.js';
import ApiAddons from '../../../../../api/factory/addons.js';

// TODO(chris): L10n

export default {
	name: 'Lv',
	mixins: [
		Phrasen
	],
	inject: ['studien_semester', 'type'],
	props: {
		lehrveranstaltung_id: [Number, String],
		bezeichnung: String,
		bezeichnung_eng: String,
		module: String,
		farbe: String,
		lvinfo: Boolean,
		benotung: Boolean,
		lvnote: String,
		lvnotebez: Array,
		znote: String,
		znotebez: Array,
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
				return 'var(--fhc-success)';
			}
			else
			{
				return 'var(--fhc-danger)';
			}
		},
		is_organisatorische_einheit(){
			return this.menu == "organisatorische_einheit";
		},
		emptyMenu(){
			return !this.menu || !Array.isArray(this.menu) || Array.isArray(this.menu) && this.menu.length == 0;
		},
		grade() {
			const languageIndex = this.$p.user_language.value === 'English' ? 1 : 0
			if(this.benotung && this.znotebez?.length) {
				return this.znotebez[languageIndex]
			} else if(this.benotung && this.lvnotebez?.length) {
				return this.lvnotebez[languageIndex]
			} else return null
		},
		LvHasPruefungenInformation(){
			return this.pruefungenData && this.pruefungenData.length > 0;
		},
	},
	methods: {
		fetchMenu(lehrveranstaltung_id = this.lehrveranstaltung_id, studien_semester = this.studien_semester) {
			return this.$api
				.call(ApiAddons.getLvMenu(lehrveranstaltung_id, studien_semester))
				.then(res => {
					this.menu = res.data;
				})
				.catch((error) => {
					this.$fhcAlert.handleSystemError(error);
					this.menu = [];
				});
		},
		c4_target(menuItem) {
			if (menuItem.c4_moodle_links?.length > 0) return null;
			return menuItem.c4_target ?? null;
		},
		c4_link(menuItem) {
			if (!menuItem) return null;
			if (Array.isArray(menuItem.c4_moodle_links) && menuItem.c4_moodle_links.length) {
				return '#';
			} else {
				return menuItem.c4_link ?? null;
			}
		},
		openPruefungen() {
			// early return if the pruefungenData is empty or not set
			if (!this.LvHasPruefungenInformation) return;

			LvPruefungen.popup({
				pruefungenData: this.pruefungenData, 
				bezeichnung: this.bezeichnung
			});
		}
	},
	watch:{
		studien_semester(newValue){
			this.fetchMenu(this.lehrveranstaltung_id, newValue);
		}
	},
	created() {
		// TODO: check if this isnt a race condition in disguise
		if(this.type == 'student') {
			this.$api
				.call(ApiLehre.getStudentPruefungen(this.lehrveranstaltung_id))
				.then(res => res.data)
				.then(pruefungen => {
					this.pruefungenData = pruefungen;
				});
		}
	},
	mounted() {
		this.fetchMenu(this.lehrveranstaltung_id, this.studien_semester);
	},
	template: /*html*/`
	<div class="mylv-semester-studiengang-lv card">
		<div class="p-2" :class="is_organisatorische_einheit?'':'card-header'">
			<!-- {{module}} if the module of the lv is important then query the module from the api endpoint for LV-->
			<h6 class="fw-bold" v-if="is_organisatorische_einheit" >{{ $p.t('lehre/organisationseinheit') }}:</h6>
			<h6 class="mb-0">{{$p.user_language.value === 'English' ? bezeichnung_eng : bezeichnung}}</h6>
		</div>
		<div v-if="!emptyMenu" class="card-body ">
			<template v-if="menu">
				<ul class="list-group border-top-0 border-bottom-0 rounded-0">
					<li :type="menuItem.c4_link ? 'button' : null" 
						v-for="(menuItem, index) in menu" :key="index" class="list-group-item border-0 " >
						<div class="d-flex flex-row">
							<div class="mx-4">
								<i :class="[menuItem.c4_icon2 ? menuItem.c4_icon2 : 'fa-solid fa-pen-to-square', !menuItem.c4_link ? 'unavailable' : null ]"></i>
							</div>
							<a :id="menuItem.name"
							class="fhc-body text-decoration-none text-truncate"
							:class="{ 'unavailable':!menuItem.c4_link }"
							:target="menuItem.c4_target"
							:href="c4_link(menuItem) ? c4_link(menuItem) : null">
								{{ menuItem.phrase ? $p.t(menuItem.phrase) : menuItem.name}}
							</a>
							
							<div v-if="menuItem.c4_moodle_links?.length || menuItem.c4_linkList?.length" class="dropdown">
								<button 
									class="btn btn-sm dropdown-toggle dropdown-toggle-split border-0" 
									type="button" 
									data-bs-toggle="dropdown" 
									aria-expanded="false">
									<span class="visually-hidden">Toggle Dropdown</span>
								</button>
					
								<ul v-if="menuItem.c4_moodle_links?.length" class="dropdown-menu dropdown-menu p-0">
									<li v-for="item in menuItem.c4_moodle_links" :key="item.url">
									   <a class="dropdown-item border-bottom" :href="item.url" target="#">{{ item.lehrform }}</a>
									</li>
								</ul>
								
								<ul v-else class="dropdown-menu dropdown-menu p-0">
									<li v-for="([text, link], i) in menuItem.c4_linkList" :key="i">
									   <a class="dropdown-item border-bottom" :href="link" target="#">{{ text }}</a>
									</li>
								</ul>
							</div>
				
					   </div>
					</li>
				</ul>
			</template>
			<template v-else>
				<div class="text-center d-flex justify-content-center align-items-center h-100" >
					<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
				</div>
			</template>
		</div>
		<div v-if="!emptyMenu && type == 'student'" class="card-footer">
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