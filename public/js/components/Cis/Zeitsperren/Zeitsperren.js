import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import PvAutoComplete from '../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js';

import ApiAuthinfo from '../../../api/factory/authinfo.js';
import ApiTimelocks from "../../../api/factory/cis/zeitsperren.js";
import ApiStvAbschlusspruefung from "../../../api/factory/stv/abschlusspruefung";

export default {
	name: 'ZeitsperrenComponent',
	components: {
		CoreFilterCmpt,
		FormForm,
		FormInput,
		PvAutoComplete
	},
	data(){
		return {
			uid: null,
			statusNew: true,
			timeRecordingLockedUntil: '2015-08-31', //TODO(Manu) check if needed
			typesTimeLocks: ["Urlaub", "PflegeU", "ZA", "Krank", "DienstF", "DienstV", "CovidSB", "CovidKS"],
			typesHideStunden: ["Urlaub", "ZA", "Krank", "DienstF", "DienstV", "CovidSB", "CovidKS"],
			listTypenZeitsperren: [],
			listTypenErreichbarkeit: [],
			listStunden: [],
			tabulatorOptions: null,
			tabulatorEvents: [],
			originalData: {},
			zeitsperreData: {
				vondatum : new Date(),
				bisdatum: new Date(),
				vonISO : "00:00:00", //later
				bisISO: "23:59:59", //later
				erreichbarkeit_kurzbz: 'n',
				zeitsperretyp_kurzbz: 'Arzt',
				vonstunde: null,
				bisstunde: null
			},
			changedData: {},
			selectedVertretung: null,
			filteredMitarbeiter: [],
			abortController: {
				mitarbeiter: null
			},
		};
	},
	computed: {
		dienstverhinderungen() {
			return {
				"Eheschließung": "a) " + this.$p.t('zeitsperren', 'eheschliessung'),
				"Geburt eigenes Kind": "b) " + this.$p.t('zeitsperren', 'geburt'),
				"Heirat Kind/Geschwister": "c) " + this.$p.t('zeitsperren', 'heirat'),
				"Eigene Sponsion/Promotion": "d) " + this.$p.t('zeitsperren', 'sponsion'),
				"Lebensbedr. Erkrankung P/K/E": "e) " + this.$p.t('zeitsperren', 'erkrankung_lebensbedr'),
				"Ableben P/K/E": "f) " + this.$p.t('zeitsperren', 'ableben'),
				"Bestattung G/S/G": "g) " + this.$p.t('zeitsperren', 'bestattung'),
				"Wohnungswechsel": "h) " + this.$p.t('zeitsperren', 'umzug'),
				"Bundesheer": "i) " + this.$p.t('zeitsperren', 'bundesheer'),
				"Volksschultag": "j) " + this.$p.t('zeitsperren', 'volksschultag')};
		},
		showInfo(){
			return this.zeitsperreData.zeitsperretyp_kurzbz === 'DienstF';
		},
	},
	methods: {
		actionEditZeitsperre(zeitsperre_id){
			this.statusNew = false;
			return this.$api
				.call(ApiTimelocks.loadZeitsperre(zeitsperre_id))
				.then(response => {
					this.originalData = structuredClone(response.data);
					this.zeitsperreData = structuredClone(response.data);

					this.selectedVertretung = {
						label: this.getPersonLabel(this.zeitsperreData.ma_titelpre, this.zeitsperreData.ma_nachname, this.zeitsperreData.ma_vorname, this.zeitsperreData.ma_titelpost, this.zeitsperreData.vertretung_uid),
						person_id: this.zeitsperreData.ma_person_id,
						mitarbeiter_uid: this.zeitsperreData.vertretung_uid
					};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionDeleteZeitsperre(zeitsperre_id){
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? zeitsperre_id
					: Promise.reject({ handled: true })
				)
				.then(() => this.deleteZeitsperre(zeitsperre_id))
				.catch(this.$fhcAlert.handleSystemError);
		},
		saveZeitsperre(zeitsperreId = null) {
			const isNew = !zeitsperreId;

			let payload = isNew
				? { ...this.zeitsperreData }                 // add
				: this.getChangedFields(this.originalData,   // edit
					this.zeitsperreData);

			if (!isNew && Object.keys(payload).length === 0) {
				return Promise.resolve();
			}

			const request = isNew
				? ApiTimelocks.addZeitsperre(this.uid, payload)
				: ApiTimelocks.editZeitsperre(zeitsperreId, payload);

			return this.$refs.dataZeitsperre
				.call(request)
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.reset();
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteZeitsperre(zeitsperre_id){
			return this.$api
				.call(ApiTimelocks.deleteZeitsperre(zeitsperre_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				});
		},
		searchMitarbeiter(event) {
			if (this.abortController.mitarbeiter) {
				this.abortController.mitarbeiter.abort();
			}

			this.abortController.mitarbeiter = new AbortController();

			return this.$api
				.call(ApiStvAbschlusspruefung.getMitarbeiter(event.query))
				.then(result => {
					this.filteredMitarbeiter = [];
					for (let mitarbeiter of result.data.retval) {
						this.filteredMitarbeiter.push(
							{
								label: this.getPersonLabel(
									mitarbeiter.titelpre,
									mitarbeiter.nachname,
									mitarbeiter.vorname,
									mitarbeiter.titelpost,
									mitarbeiter.mitarbeiter_uid
								),
								person_id: mitarbeiter.person_id,
								mitarbeiter_uid: mitarbeiter.mitarbeiter_uid
							}
						);
					}
				});
		},
		getPersonLabel(titelpre, nachname, vorname, titelpost, uid) {
			if(!uid)
				return '';
			return nachname + ' ' + vorname + (titelpre ? ' ' + titelpre : '') + (titelpost ? ' ' + titelpost : '') + (uid ? ' (' + uid + ')' : '');
		},
		reload() {
			if (this.$refs.table)
				this.$refs.table.reloadTable();
		},
		reset(){
			this.statusNew = true;
			this.selectedVertretung = null;
			this.zeitsperreData = {
					vondatum : new Date(),
					bisdatum: new Date(),
					vonISO : "00:00:00", //later
					bisISO: "23:59:59", //later
					erreichbarkeit_kurzbz: 'n',
					zeitsperretyp_kurzbz: 'Arzt',
					vonstunde: null,
					bisstunde: null
				};
			this.originalData = {};
		},
		handleChangeVonStunde(){
			let stunde = this.zeitsperreData.vonstunde;
			const result = this.listStunden.find(item => item.stunde === stunde);
			if (!result) {
				this.zeitsperreData.vonISO = '00:00:00';
				return;
			}
			this.zeitsperreData.vonISO = result.beginn;
		},
		handleChangeBisStunde(){
			let stunde = this.zeitsperreData.bisstunde;
			const result = this.listStunden.find(item => item.stunde === stunde);
			if (!result) {
				this.zeitsperreData.bisISO = '23:59:59';
				return;
			}
			this.zeitsperreData.bisISO = result.ende;
		},
		handleStunden(){
			if (this.typesHideStunden.includes(this.zeitsperreData.zeitsperretyp_kurzbz)){
				this.zeitsperreData.vonstunde = null;
				this.zeitsperreData.bisstunde = null;
				this.zeitsperreData.vonISO = '00:00:00';
				this.zeitsperreData.bisISO = '23:59:59';
			}
		},
		copyDateForBis(){
			this.zeitsperreData.bisdatum = this.zeitsperreData.vondatum;
		},
		getChangedFields(original, current) {
			const diff = {};

			Object.keys(current).forEach((key) => {
				if (current[key] !== original[key]) {
					diff[key] = current[key];
				}
			});
			return diff;
		},
	},
	watch: {
		selectedVertretung(newVal) {
			this.zeitsperreData.vertretung_uid = newVal?.mitarbeiter_uid || null;
		},
		'zeitsperreData.zeitsperretyp_kurzbz'(newVal) {
			if (newVal === 'DienstV') {
				// set first key as default
				if (!this.zeitsperreData.bezeichnung) {
					const firstKey = Object.keys(this.dienstverhinderungen)[0];
					this.zeitsperreData.bezeichnung = firstKey;
				}
			} else {
				this.zeitsperreData.bezeichnung = '';
			}
		}
	},
	created() {
		this.$api.call(ApiAuthinfo.getAuthUID()).then(res => {

			//check if there is a user, passed via route
			const urlUid = this.$route?.query?.uid;
			this.uid = urlUid ? urlUid : res.data.uid;

			this.tabulatorOptions = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () =>
					this.$api.call(ApiTimelocks.getTimelocksUser(this.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title:"bezeichnung", field:"bezeichnung"},
					{title:"Grund", field:"beschreibung"},
					{title:"Von", field:"vondatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title:"Bis", field:"bisdatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title:"vonstunde", field:"vonstunde", visible: false},
					{title:"bisstunde", field:"bisstunde", visible: false},
					{title:"Vertretung", field:"vertretung"},
					{title:"Erreichbarkeit", field:"erreichbarkeit_beschreibung"},
					{title:"zeitsperre_id", field:"zeitsperre_id", visible: false},
					{title:"mitarbeiter_uid", field:"mitarbeiter_uid", visible: false},
					{title:"freigabeamum", field:"freigabeamum", visible: false,
						formatter: function (cell) {
							const value = cell.getValue();
							return value === null
								? ''
								: '<i class="fa fa-check text-success"></i>';
						}
						},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'bearbeiten');
							button.addEventListener('click', (event) =>
								this.actionEditZeitsperre(cell.getData().zeitsperre_id)
							);
							if(cell.getData().zeitsperretyp_kurzbz == 'DienstV' || cell.getData().zeitsperretyp_kurzbz == 'ZVerfueg'){
								button.disabled = true;
							}
							//TODO(Manu) check if needed
							if(this.typesTimeLocks.includes(cell.getData().zeitsperretyp_kurzbz) && (cell.getData().vondatum < this.timeRecordingLockedUntil)){
								button.disabled = true;
							}
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								//this.deleteZeitsperre(cell.getData().zeitsperre_id)
								this.actionDeleteZeitsperre(cell.getData().zeitsperre_id)
							);
							if(cell.getData().zeitsperretyp_kurzbz == 'Urlaub' || cell.getData().zeitsperretyp_kurzbz == 'ZVerfueg'){
								button.disabled = true;
							}
							//TODO(Manu) check if needed
							if(this.typesTimeLocks.includes(cell.getData().zeitsperretyp_kurzbz) && (cell.getData().vondatum < this.timeRecordingLockedUntil)){
								button.disabled = true;
							}
							container.append(button);

							return container;
						},
						frozen: true
					},
				]
			};
				this.tabulatorEvents = [
					{
						event: 'tableBuilt',
						handler: async() => {
							await this.$p.loadCategory(['global', 'person', 'zeitsperren', 'ui', 'abschlusspruefung']);

							let cm = this.$refs.table.tabulator.columnManager;

							cm.getColumnByField('bezeichnung').component.updateDefinition({
								title: this.$p.t('person', 'grund')
							});
							cm.getColumnByField('beschreibung').component.updateDefinition({
								title: this.$p.t('global', 'bezeichnung')
							});
							cm.getColumnByField('vondatum').component.updateDefinition({
								title: this.$p.t('ui', 'von')
							});
							cm.getColumnByField('bisdatum').component.updateDefinition({
								title: this.$p.t('global', 'bis')
							});
							cm.getColumnByField('vonstunde').component.updateDefinition({
								title: this.$p.t('zeitsperren', 'stunde_von')
							});
							cm.getColumnByField('bisstunde').component.updateDefinition({
								title: this.$p.t('zeitsperren', 'stunde_bis')
							});
							cm.getColumnByField('vertretung').component.updateDefinition({
								title: this.$p.t('person', 'vertretung')
							});

							cm.getColumnByField('erreichbarkeit_beschreibung').component.updateDefinition({
								title: this.$p.t('person', 'erreichbarkeit')
							});
							cm.getColumnByField('freigabeamum').component.updateDefinition({
								title: this.$p.t('abschlusspruefung', 'freigabe')
							});

	/*						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
							});*/

						}
					}
				];
		});

		this.$api
			.call(ApiTimelocks.getTypenZeitsperren())
			.then(result => {
				this.listTypenZeitsperren = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiTimelocks.getTypenErreichbarkeit())
			.then(result => {
				this.listTypenErreichbarkeit = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiTimelocks.getStunden())
			.then(result => {
				this.listStunden = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: /* html */`
	<div class="zeitsperre">
		<h4>{{$p.t('zeitsperren', 'header_zeitsperren')}} ({{uid}}) </h4>

			<form-form class="row g-3 mt-3" ref="dataZeitsperre">
				<div class= "w-50">
					<div class="row mb-3">
						<form-input
							type="select"
							:class="showInfo ? 'is-info' : ''"
							name="zeitsperretyp_kurzbz"
							:label="$p.t('person/grund')"
							v-model="zeitsperreData.zeitsperretyp_kurzbz"
							@change="handleStunden"
						>
							<option
								v-for="typ in listTypenZeitsperren"
								:key="typ.zeitsperretyp_kurzbz"
								:value="typ.zeitsperretyp_kurzbz"
								:disabled="typ.zeitsperretyp_kurzbz == 'Urlaub'"
								>
								 {{typ.beschreibung}}
							</option>
						</form-input>
						<div v-if="showInfo" class="info-feedback">
							<strong> Dienstfreistellungen</strong> nur in Absprache mit HR Service eintragen!
						 </div>
					</div>

					<div v-if="zeitsperreData.zeitsperretyp_kurzbz == 'DienstV'" class="row mb-3">
						<form-input
							type="select"
							name="beschreibung"
							:label="$p.t('ui/bezeichnung')"
							v-model="zeitsperreData.bezeichnung"
						>
							<option v-for="(beschreibung, key) in dienstverhinderungen"
								:key="key"
								:value="key">{{beschreibung}}
							</option>

						</form-input>
					</div>
					<div v-else class="row mb-3">
						<form-input
							type="text"
							name="beschreibung"
							:label="$p.t('ui/bezeichnung')"
							v-model="zeitsperreData.bezeichnung"
						>
						</form-input>
					</div>
				</div>

				<div>
					<div class="row">
						<div class="mb-3 col-2">
							<form-input
								type="DatePicker"
								name="vondatum"
								:label="$p.t('ui/from')"
								v-model="zeitsperreData.vondatum"
								auto-apply
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								text-input
								preview-format="dd.MM.yyyy"
								:teleport="true"
								required
							>
							</form-input>
						</div>
						<div class="mb-3 col-1 d-flex align-items-end">
							<button
							class="btn btn-outline-secondary"
							title="Für Bis-Datum übernehmen"
							@click.prevent="copyDateForBis"
							>
								<i class="fa-solid fa-arrow-down"></i>
							</button>
						</div>

						<div class="mb-3 col-3">
							<form-input
								v-if="!typesHideStunden.includes(zeitsperreData.zeitsperretyp_kurzbz)"
								type="select"
								name="vonstunde"
								:label="$p.t('zeitsperren/stunde')"
								v-model="zeitsperreData.vonstunde"
								@change="handleChangeVonStunde"
							>
								<option value='null'>*</option>
								<option
									v-for="std in listStunden"
									:key="std.stunde"
									:value="std.stunde"
									>
									 {{std.stunde}} ({{std.beginn}} - {{std.ende}})
								</option>
							</form-input>

						</div>

						<!-- Uncomment to use timestamp VON
							<div class="mb-3 col-3">
								<form-input
									type="text"
									name="vonISO"
									label="vonISO"
									v-model="zeitsperreData.vonISO"
									auto-apply
									:enable-time-picker="true"
									format="dd.MM.yyyy HH:mm"
									text-input
									preview-format="dd.MM.yyyy HH:mm"
									:teleport="true"
								>
								{{timestampHoursVon}}
								</form-input>
							</div>
						-->

					</div>

					<div class="row">
						<div class="mb-3 col-2">
							<form-input
								type="DatePicker"
								name="bisdatum"
								:label="$p.t('global/bis')"
								v-model="zeitsperreData.bisdatum"
								auto-apply
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								text-input
								preview-format="dd.MM.yyyy"
								:teleport="true"
								required
							>
							</form-input>
						</div>
						<div class="mb-3 col-1"></div>

						<div class="mb-3 col-3">
							<form-input
								v-if="!typesHideStunden.includes(zeitsperreData.zeitsperretyp_kurzbz)"
								type="select"
								name="bisstunde"
								:label="$p.t('zeitsperren/stunde')"
								v-model="zeitsperreData.bisstunde"
								@change="handleChangeBisStunde"
							>
								<option value='null'>*</option>
								<option
									v-for="std in listStunden"
									:key="std.stunde"
									:value="std.stunde"
									>
									 {{std.stunde}} ({{std.beginn}} - {{std.ende}})
								</option>
							</form-input>
						</div>

						<!-- Uncomment to use timestamp BIS
							<div class="mb-3 col-3">
								<form-input
									type="text"
									name="bisISO"
									label="bisISO"
									v-model="zeitsperreData.bisISO"
									auto-apply
									:enable-time-picker="true"
									format="dd.MM.yyyy HH:mm"
									text-input
									preview-format="dd.MM.yyyy HH:mm"
									:teleport="true"
								>
								</form-input>
							</div>
						-->

					</div>

					<div class= "w-50">
						<div class="row mb-3">
							<form-input
								type="autocomplete"
								name="vertretung_uid"
								:label="$p.t('person/vertretung')"
								v-model="selectedVertretung"
								optionLabel="label"
								optionValue="mitarbeiter_uid"
								dropdown
								forceSelection
								:suggestions="filteredMitarbeiter"
								@complete="searchMitarbeiter"
								:min-length="3"
								>
							</form-input>
						</div>
					</div>

					<div class="row align-items-end">
						<div class="mb-3 col-4">
							<form-input
								type="select"
								name="erreichbarkeit"
								:label="$p.t('person/erreichbarkeit')"
								v-model="zeitsperreData.erreichbarkeit_kurzbz"
								>
								<option
									v-for="typ in listTypenErreichbarkeit"
									:key="typ.erreichbarkeit_kurzbz"
									:value="typ.erreichbarkeit_kurzbz"
									>
									 {{typ.beschreibung}}
								</option>
							</form-input>
						</div>

						<div class="mb-3 col-3">
							<button
							  v-if="statusNew"
							  type="button"
							  class="btn btn-primary"
							  @click="saveZeitsperre()">
							  {{$p.t('zeitsperren', 'addZeitsperre')}}
							</button>							
							<button
							  v-else
							  type="button"
							  class="btn btn-warning"
							  @click="saveZeitsperre(zeitsperreData.zeitsperre_id)">
							  {{$p.t('zeitsperren', 'saveZeitsperre')}}
							</button>
						  </div>
					</div>

				</div>

			</form-form>
					
			<div class="d-flex align-items-start text-muted small">
			  <i class="fa fa-circle-info me-2 mt-1"></i>
			  <div>
				<strong>{{$p.t('alert', 'attention')}}</strong><br>
				{{$p.t('zeitsperren', 'info_zeitsperrenMoreDays')}}
			  </div>
			</div>


			<hr>

			<core-filter-cmpt
				v-if="tabulatorOptions"
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				:reload-btn-infotext="this.$p.t('table', 'reload')"
			>
		</core-filter-cmpt>
	</div>
	`
};