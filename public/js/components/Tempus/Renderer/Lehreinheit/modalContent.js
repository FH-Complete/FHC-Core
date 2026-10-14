import ApiKalender from '../../../../api/factory/tempus/kalender.js';
import FormInput from "../../../Form/Input.js";
import RaumauswahlModal from '../../RaumauswahlModal.js';
import { parseJsonField, buildLektorenLinks } from '../../../../helpers/Tempus/modalHelper.js';
import ZeitRow from '../../Modal/ZeitRow.js'
import RaumRow from '../../Modal/RaumRow.js'
import KollisionRow from '../../Modal/KollisionRow.js'
import TagRow from '../../Modal/TagRow.js'

export default {
	components:{
		FormInput,
		RaumauswahlModal,
		ZeitRow,
		RaumRow,
		KollisionRow,
		TagRow
	},
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	inject: {
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
			anzahl: '',
			anzahlSchwund: '',
			mailto: '',
			zeitwunschMap: {},
		};
	},
	watch: {
	},
	computed: {
		lektorenLinks() {
			return buildLektorenLinks(this.event.lektor);
		},
		resourcesValue() {
			const resources = parseJsonField(this.event.resources, null);
			return resources ? resources.map(r => r.beschreibung).join(', ') : null;
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
	},
	methods: {
		formatDate(d){
			if (!d)
				return '';
			return luxon.DateTime.fromSQL(d).toFormat('dd.MM.yyyy HH:mm');
		},
		addLecturerFilter(lektor) {
			this.modalActions.addToFilter({
				uid: lektor.mitarbeiter_uid,
				name: `${lektor.vorname} ${lektor.nachname}`,
			}, 'mitarbeiter');
		},
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
	template: /*html*/`
	<div>
		<h5>
			{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}
		</h5>
		<table class="table table-hover mb-4">
				<tbody>
					<kollision-row :event="event" />
					<zeit-row ref="zeit" :event="event" />
					<raum-row :event="event" />
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
								<a v-if="lvLinks.lvverwaltung[lehrveranstaltung.lehrveranstaltung_id]" target='_blank' :aria-label="$p.t('lehre','lehrveranstaltung')" :title="$p.t('lehre','lehrveranstaltung')" :href="lvLinks.lvverwaltung[lehrveranstaltung.lehrveranstaltung_id]"><i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i></a>
								<a v-if="lvLinks.vilesci[lehrveranstaltung.lehrveranstaltung_id]" target='_blank' :aria-label="$p.t('lehre','lvInfoBearbeiten')" :title="$p.t('lehre','lehrveranstaltung')" :href="lvLinks.vilesci[lehrveranstaltung.lehrveranstaltung_id]"><i class="fa fa-pen-to-square me-1" aria-hidden="true"></i></a>
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
								<a v-if="leLinks[lehreinheit.lehreinheit_id]" target='_blank' :aria-label="$p.t('lehre','lehreinheit')" :title="$p.t('lehre','lehreinheit')" :href="leLinks[lehreinheit.lehreinheit_id]"><i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i></a>
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
									<a v-if="lektorenLinks[lektor.kurzbz]" target='_blank' :aria-label="$p.t('lehre','lektor')" :title="$p.t('lehre','lektor')" :href="lektorenLinks[lektor.kurzbz]"><i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i></a>
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
					<tag-row :event="event" />
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
		<button type="button" class="btn btn-primary" @click.stop="$refs.zeit.save()">{{$p.t('ui', 'speichern')}}</button>
	</div>`,
}
