import ApiKalender from '../../../../api/factory/tempus/kalender.js';
import FormInput from "../../../Form/Input.js";
import RaumauswahlModal from '../../RaumauswahlModal.js';
import CoreTag from '../../../../components/Tag/Tag.js';
import ApiTempusTag from "../../../../api/factory/tempus/tag.js";
import { idTagFormatter } from "../../../Tag/tagFormatter.js";

export default {
	components:{
		FormInput,
		RaumauswahlModal,
		CoreTag
	},
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	inject: {
		showRaster: {
			from: 'showRaster', default: false
		},
		teachingunits : {
			from: 'teachingunits',
			default: () => []
		},
		hoursPlan : {
			from: 'hoursPlan',
			default: null
		},
		reload: {
			from: 'reload',
			default: () => {}
		},
		modalActions: {
			from: 'modalActions', default: () => ({})
		},
	},
	data() {
		return {
			lvMenu: [],
			editDatum: this.normalizeDate(this.event.datum),
			editBeginn: this.normalizeTime(this.event.beginn),
			editEnde: this.normalizeTime(this.event.ende),
			editStundeVon: this.teachingunits.find(unit => this.normalizeTime(unit.start) === this.normalizeTime(this.event.beginn))?.id ?? null,
			editStundeBis: this.teachingunits.find(unit => this.normalizeTime(unit.end) === this.normalizeTime(this.event.ende))?.id ?? null,
			anzahl: '',
			anzahlSchwund: '',
			mailto: '',
			zeitwunschMap: {},
			tagEndpoint: ApiTempusTag,
		};
	},
	watch: {
		editStundeVon(newVal) {
			if (this.editStundeBis !== null && this.editStundeBis < newVal)
				this.editStundeBis = newVal;
		},
		tags: {
			handler() {
				this.renderTags();
			},
			deep: true
		}
	},
	computed: {
		tagValues() {
			return this.event.eindeutige_kalender_gruppen_id ? [this.event.eindeutige_kalender_gruppen_id] : [];
		},
		lektorenLinks: function () {
			if (!this.event || !Array.isArray(this.event.lektor) || !this.event.lektor.length) return "a";

			let lektorenLinks = {};
			this.event.lektor.forEach((lektor) => {
				lektorenLinks[lektor.kurzbz] = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + `/Cis/Profil/View/${lektor.mitarbeiter_uid}`;
			})
			return lektorenLinks;
		},
		lvLinks()
		{
			let semester = this.event.le_studiensemester_kurzbz.toLowerCase();
			let base_url = FHC_JS_DATA_STORAGE_OBJECT.app_root;
			let ci_url = base_url + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

			let lvLinks = {
				lvverwaltung: {},
				vilesci: {},
			}
			this.event.lehrveranstaltung_infos.forEach(lehrveranstaltung => {
				lvLinks.lvverwaltung[lehrveranstaltung.lehrveranstaltung_id] = `${ci_url}/LVVerwaltung/stdsem/${semester}/lv/${lehrveranstaltung.lehrveranstaltung_id}`;
				lvLinks.vilesci[lehrveranstaltung.lehrveranstaltung_id] = `${base_url}vilesci/lehre/lehrveranstaltung_details.php?lv_id=${lehrveranstaltung.lehrveranstaltung_id}`;
			})
			return lvLinks;
		},
		leLinks()
		{
			let base_url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

			let leLinks = {};
			this.event.lehreinheit_infos.forEach(lehreinheit => {
				leLinks[lehreinheit.lehreinheit_id] = `${base_url}/LVVerwaltung/stdsem/${lehreinheit.studiensemester_kurzbz}/le/${lehreinheit.lehreinheit_id}`;
			})
			return leLinks;
		},
		tags() {
			if (typeof this.event.tags === 'string') {
				try {
					return JSON.parse(this.event.tags);
				} catch (e) {
					console.error('Failed to parse tags:', e);
					return [];
				}
			}

			return this.event.tags || [];
		},
		resourcesValue() {
			let resources = this.event.resources;
			if (typeof this.event.resources === 'string') {
				try {
					resources = JSON.parse(this.event.resources);
				} catch (e) {
					console.error('Failed to parse resources:', e);
					return null;
				}
			}
			return resources.map(resource => resource.beschreibung).join(', ');
		},

	},
	methods: {
		formatDate(d){
			if (!d)
				return '';
			return luxon.DateTime.fromSQL(d).toFormat('dd.MM.yyyy HH:mm');
		},
		normalizeTime(value) {
			if (!value)
				return null;
			if (value instanceof Date)
				return luxon.DateTime.fromJSDate(value).toFormat('HH:mm');
			return luxon.DateTime.fromFormat(value, 'HH:mm:ss').isValid
				? luxon.DateTime.fromFormat(value, 'HH:mm:ss').toFormat('HH:mm')
				: value;
		},
		normalizeDate(value) {
			if (!value)
				return null;
			if (value instanceof Date)
				return luxon.DateTime.fromJSDate(value).toFormat('yyyy-MM-dd');
			return value.slice(0, 10);
		},
		onSave()
		{
			if (!this.editDatum)
			{
				this.$refs.editDatumInput.setFeedback(false, this.$p.t('ui','error_fieldRequired', { field:  this.$p.t('global','datum') }));
				return;
			}

			if (this.editBeginn > this.editEnde)
			{
				return;
			}

			let start_time;
			let end_time;

			if (this.showRaster && this.editStundeVon !== null && this.editStundeBis !== null)
			{
				let vonUnit = this.teachingunits.find(unit => unit.id === this.editStundeVon);
				let bisUnit = this.teachingunits.find(unit => unit.id === this.editStundeBis);
				start_time = this.editDatum + ' ' + vonUnit.start;
				end_time = this.editDatum + ' ' + bisUnit.end;
			}
			else
			{
				start_time = this.editDatum + ' ' + this.editBeginn;
				end_time = this.editDatum + ' ' + this.editEnde;
			}

			this.modalActions.saveEventTime({kalender_id: this.event.kalender_id, start_time, end_time});
		},
		async fetchAssignedTagsByCalender(calendarGroupId) {
			let result = await this.$api.call(
				ApiTempusTag.getTagsByCalendar(calendarGroupId),
			);

			if (result.meta.status === "success")
				return result.data.filter(tag => !!tag);

			this.$fhcAlert.alertError(
				this.$p.t("ui", "failed_assigned_tags_fetch_error_message"),
			);
			return [];
		},
		async onTagsChanged() {
			let id = this.event.eindeutige_kalender_gruppen_id;
			if (!id) return;

			this.event.tags = await this.fetchAssignedTagsByCalender(id);
		},
		editTag(tag) {
			this.$refs.tagComponent?.editTag(tag.id);
		},
		openRaumvorschlag()
		{
			this.$refs.raumModal.show(this.event);
		},
		addLecturerFilter(lektor) {
			this.modalActions.addToFilter({
				uid: lektor.mitarbeiter_uid,
				name: `${lektor.vorname} ${lektor.nachname}`,
			}, 'mitarbeiter');
		},
		addToRoomFilter(room) {
			this.modalActions.addToFilter({
				ort_kurzbz: room,
			}, 'ort');
		},
		renderTags()
		{
			let el = this.$refs.tagWrapper;
			if (!el) return;

			const tagData = this.tags.map(t => ({
				...t,
				notiz_id: t.id,
				bezeichnung: t.beschreibung,
			}));

			el.replaceChildren(
				idTagFormatter(
					this.event.eindeutige_kalender_gruppen_id,
					tagData,
					this.$refs.tagComponent,
					'eindeutige_kalender_gruppen_id'
				) ?? ''
			);
		}
	},
	created() {
		if (this.event.type == 'lehreinheit') {
			this.$api
				.call(ApiKalender.getModalContent(this.event.kalender_id))
				.then(res => res.data)
				.then(content => {
					this.anzahl = content.anzahl;
					this.anzahlSchwund = content.anzahlSchwund;
					this.zeitwunschMap  = content.zeitwunschMap;
					this.mailto  = content.mails;
				})
		}
	},
	mounted()
	{
		this.renderTags();
	},
	template: /*html*/`
	<div>
		<h5>
			{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}
		</h5>
		<table class="table table-hover mb-4">
				<tbody>
				
					<tr v-if="this.event.collisions.length > 0">
						<th><i class="fa-solid fa-triangle-exclamation text-danger"></i> {{ $p.t('ui','collision') }}:</th>
						<td>
							<div v-for="collision in this.event.collisions" class="d-block">
								{{collision.message}}
							</div>
						</td>
					</tr>
					<tr>
						<th>{{$p.t('global','datum')+':'}}</th>
						<td>
							<form-input
								ref="editDatumInput"
								type="DatePicker"
								v-model="editDatum"
								model-type="yyyy-MM-dd"
								name="editDatum"
								:enable-time-picker="false"
								text-input
								auto-apply
								format="dd.MM.yyyy"
								class="form-control form-control-sm d-inline-block w-auto"
								>
							</form-input>
						</td>
					</tr>
					<tr>
						<th>{{$p.t('ui','zeitraum')+':'}}</th>
						<td>
						<template v-if="showRaster">
							<select v-model="editStundeVon" class="form-select form-select-sm w-auto d-inline-block">
								<option v-for="unit in teachingunits" :key="unit.id" :value="unit.id">
									{{ unit.id }}. Stunde ({{ unit.start }}–{{ unit.end }})
								</option>
							</select>
							–
							<select v-model="editStundeBis" class="form-select form-select-sm w-auto d-inline-block">
								<option
									v-for="unit in teachingunits.filter(tunit => editStundeVon === null || tunit.id >= editStundeVon)"
									:key="unit.id"
									:value="unit.id"
								>
									{{ unit.id }}. Stunde ({{ unit.start }}–{{ unit.end }})
								</option>
							</select>
						</template>
						<template v-else>
							 <div class="timepicker-row">
								<form-input
									type="DatePicker"
									v-model="editBeginn"
									time-picker
									model-type="HH:mm"
									name="editBeginn"
									text-input
									auto-apply
									format="HH:mm"
									class="form-control form-control-sm"
								/>
								<span>–</span>
								<form-input
									type="DatePicker"
									v-model="editEnde"
									time-picker
									model-type="HH:mm"
									name="editEnde"
									text-input
									auto-apply
									format="HH:mm"
									class="form-control form-control-sm"
								/>
							</div>
						
						</template>
					</td>
					</tr>
					<tr>
						<th>{{$p.t('global','raum')}}:
							<i 
								class="fa-solid fa-refresh"
								@click="openRaumvorschlag"
							></i>
						</th>
						<td>
							
							<div v-for="ort in event.ort_kurzbz" class="d-block">
								<i class="fa fa-filter" @click="addToRoomFilter(ort)"></i> {{ort}} 
							</div>
						</td>
					</tr>
					
					<tr>
						<th>{{$p.t('global','anzahl')}} {{$p.t('global','students')}}:
						</th>
						<td>
							{{anzahlSchwund}} ({{anzahl}})
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lehrveranstaltung')?
							$p.t('lehre','lehrveranstaltung')+':'
							:''
						}}</th>
						<td>
							<div v-for="lehrveranstaltung in event.lehrveranstaltung_infos" class="d-block">
								<a v-if="lvLinks.lvverwaltung[lehrveranstaltung.lehrveranstaltung_id]" target='_blank' :aria-label="$p.t('lehre','lehrveranstaltung')" :title="$p.t('lehre','lehrveranstaltung')" :href="lvLinks.lvverwaltung[lehrveranstaltung.lehrveranstaltung_id]"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C" aria-hidden="true"></i></a>
								<a v-if="lvLinks.vilesci[lehrveranstaltung.lehrveranstaltung_id]" target='_blank' :aria-label="$p.t('lehre','lvInfoBearbeiten')" :title="$p.t('lehre','lehrveranstaltung')" :href="lvLinks.vilesci[lehrveranstaltung.lehrveranstaltung_id]"><i class="fa fa-pen-to-square me-1" style="color:#00649C" aria-hidden="true"></i></a>
								<span v-tooltip="lehrveranstaltung.oe_bezeichnung">{{'('+ lehrveranstaltung.lehrform_kurzbz + ') ' + lehrveranstaltung.lehrfach_bezeichnung }} </span>
							</div>
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lehreinheit')?
							$p.t('lehre','lehreinheit')+':'
							:''
						}}</th>
						<td>
							<div v-for="lehreinheit in event.lehreinheit_infos" class="d-block">
								<a v-if="leLinks[lehreinheit.lehreinheit_id]" target='_blank' :aria-label="$p.t('lehre','lehreinheit')" :title="$p.t('lehre','lehreinheit')" :href="leLinks[lehreinheit.lehreinheit_id]"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C" aria-hidden="true"></i></a>
								<span v-tooltip="lehreinheit.anmerkung">{{lehreinheit.lehrfach_bezeichnung}}</span>
							</div>
						</td>
					</tr>
					<tr>
						<th>{{
							$p.t('lehre','lektor')?
							$p.t('lehre','lektor')+':'
							:''
						}}</th>
						<td>
							<div id="lektorenContainer">
								<div v-for="lektor in event.lektor" class="d-block">
									<a v-if="lektorenLinks[lektor.kurzbz]" target='_blank' :aria-label="$p.t('lehre','lektor')" :title="$p.t('lehre','lektor')" :href="lektorenLinks[lektor.kurzbz]"><i class="fa fa-arrow-up-right-from-square me-1" style="color:#00649C" aria-hidden="true"></i></a>
									<i class="fa fa-filter" @click="addLecturerFilter(lektor)"></i>
									{{lektor.vorname}} {{lektor.nachname}} ({{lektor.kurzbz}})
									<small v-if="zeitwunschMap[lektor.mitarbeiter_uid]" class="text-muted">
										<i class="fa-regular fa-clock"></i> {{ formatDate(zeitwunschMap[lektor.mitarbeiter_uid]) }}
									</small>
								</div>
							</div>
						</td>
					</tr>
					<tr v-if="resourcesValue">
						<th>{{$p.t('ui','betriebsmittel') + ':'}}</th>
						<td>
							{{ resourcesValue }}
						</td>
					</tr>
					<tr>
						<th>
							<div class="d-flex align-items-center flex-wrap">
								{{$p.t('ui','tags')}}:
								<div class="fw-normal">
									<core-tag
										v-if="tagValues.length"
										ref="tagComponent"
										:endpoint="tagEndpoint"
										:values="tagValues"
										zuordnung_typ="eindeutige_kalender_gruppen_id"
										show-hover
										@added="onTagsChanged"
										@deleted="onTagsChanged"
										@updated="onTagsChanged"
									></core-tag>
								</div>
							</div>
						</th>
						<td>
							<div ref="tagWrapper"></div>
						</td>
					</tr>
					<tr>
						<th>{{$p.t('lehre','mail')}}: </th>
						<td>
							<a class="fhc-link-color" :href="'mailto:' + mailto">
								<i class="fa-solid fa-envelope"></i>
							</a>
						</td>
					</tr>
				</tbody>
		</table>
		<raumauswahl-modal ref="raumModal" @saved="reload()"/>
		<button type="button" class="btn btn-primary " @click.stop="onSave">{{$p.t('ui', 'speichern')}}</button>
	</div>`,
}
