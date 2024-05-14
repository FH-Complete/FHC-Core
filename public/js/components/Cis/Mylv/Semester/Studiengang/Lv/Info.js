import BsModal from '../../../../../Bootstrap/Modal.js';
import Phrasen from '../../../../../../mixins/Phrasen.js';

const infos = {};

export default {
	components: {
		BsModal
	},
	mixins: [
		BsModal,
		Phrasen
	],
	props: {
		lehrveranstaltung_id: Number,
		bezeichnung: String,
		studiengang_kuerzel: String,
		semester: Number,
		studien_semester: String,
		orgform_kurzbz: String,
		sprache: String,
		ects: Number,
		incoming: Number,
		/*
		 * NOTE(chris): 
		 * Hack to expose in "emits" declared events to $props which we use 
		 * in the v-bind directive to forward all events.
		 * @see: https://github.com/vuejs/core/issues/3432
		*/
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function
	},
	data: () => ({
		result: true,
		info: null
	}),
	computed: {
		lektorNames() {
			return this.info.lektoren.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		lvLeitung() {
			return this.info.lvLeitung && this.info.lvLeitung.length ? this.info.lvLeitung.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim()) : null;
		},
		oe() {
			return this.info.oe.organisationseinheittyp ? (this.info.oe.organisationseinheittyp + ' ' + this.info.oe.bezeichnung) : '';
		},
		oeLeitung() {
			if (!this.info.oeLeitung || !this.info.oeLeitung.length)
				return ['-'];
			return this.info.oeLeitung.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		koordinator() {
			if (!this.info.koordinator || !this.info.koordinator.length)
				return null;
			return this.info.koordinator.map(e => ((e.titelpre || '') + ' ' + (e.vorname || '') + ' ' + (e.nachname || '') + ' ' + (e.titelpost || '')).trim());
		},
		currentLang() {
			if (!this.info)
				return null;
			if (this.info.lastLang)
				return this.info.lastLang;
			if (!this.info.lvinfo)
				return null;
			return this.info.lvinfoDefaultLang && this.info.lvinfo[this.info.lvinfoDefaultLang] ? this.info.lvinfoDefaultLang : Object.keys(this.info.lvinfo).shift();
		}
	},
	created() {
		if (infos[this.lehrveranstaltung_id]) {
			this.info = infos[this.lehrveranstaltung_id];
		} else {
			axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Info/' + this.studien_semester + '/' + this.lehrveranstaltung_id).then(res => {
				this.info = infos[this.lehrveranstaltung_id] = res.data.retval || [];
			}).catch(() => this.info = {});
		}
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		return BsModal.popup.bind(this)(null, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-alert" v-bind="$props" body-class="" dialog-class="modal-lg">
		<template v-slot:title>
			{{p.t('lvinfo/lehrveranstaltungsinformationen')}}
		</template>
		<template v-slot:default>
			<div v-if="!info" class="text-center">
				<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
			</div>
			<table v-else class="table table-hover">
				<tbody>
					<tr>
						<th>{{p.t('lehre/lehrveranstaltung')}}</th>
						<td>{{bezeichnung}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/studiengang')}}</th>
						<td>{{studiengang_kuerzel}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/semester')}}</th>
						<td>{{semester}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/studiensemester')}}</th>
						<td>{{studien_semester}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/organisationsform')}}</th>
						<td>{{orgform_kurzbz}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/lehrbeauftragter')}}</th>
						<td>
							<ul v-if="lektorNames.length" class="list-unstyled mb-0">
								<li v-for="name in lektorNames" :key="name">
									<!-- TODO(chris): link? -->
									{{name}}
								</li>
							</ul>
							<template v-else>
								{{p.t('lehre/keinLektorZugeordnet')}}
							</template>
						</td>
					</tr>
					<tr v-if="lvLeitung">
						<th>{{p.t('lehre/lvleitung')}}</th>
						<td>
							<ul class="list-unstyled mb-0">
								<li v-for="name in lvLeitung" :key="name">
									<!-- TODO(chris): link? -->
									{{name}}
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th>{{p.t('global/sprache')}}</th>
						<td>{{sprache}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/ects')}}</th>
						<td>{{ects}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/incomingplaetze')}}</th>
						<td>{{incoming}}</td>
					</tr>
					<tr>
						<th>{{p.t('lehre/organisationseinheit')}}</th>
						<td>
							{{oe}} <br>
							(
							<i>{{p.t('global/leitung')}}: </i>{{oeLeitung.join(', ')}}
							<template v-if="koordinator">
								<i>{{p.t('global/koordination')}}: </i>{{koordinator.join(', ')}}
							</template>
							)
						</td>
					</tr>
				</tbody>
			</table>
			<div v-if="info && info.lvinfo">
				<div v-if="Object.keys(info.lvinfo).length > 1" class="text-end">
					<div class="btn-group" role="group" :title="p.t('global/verfuegbareSprachen')" :aria-label="p.t('global/verfuegbareSprachen')">
						<template v-for="lang in info.sprachen" :key="lang.index">
							<button v-if="info.lvinfo[lang.sprache]" type="button" class="btn btn-outline-primary" :class="lang.sprache == currentLang ? 'active' : ''" @click.prevent="info.lastLang = lang.sprache">{{lang.bezeichnung[lang.index-1]}}</button>
						</template>
					</div>
				</div>
				<template v-for="i in info.lvinfo[currentLang]" :key="info">
					<h4>{{i.header}}</h4>
					<h6 v-if="i.subheader">{{i.subheader}}</h6>
					<ul v-if="Array.isArray(i.body)">
						<li v-for="e in i.body" :key="e">{{e}}</li>
					</ul>
					<p v-else>
						{{i.body}}
					</p>
				</template>
			</div>
		</template>
	</bs-modal>`
}
