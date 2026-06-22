import AbgabeDetail from "./AbgabeStudentDetail.js";
import ApiAbgabe from '../../../api/factory/abgabe.js'
import ApiAuthinfo from '../../../api/factory/authinfo.js';
import BsModal from "../../Bootstrap/Modal.js";
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import { getDateStyleClass} from "./getDateStyleClass.js";
import { validateThesisTitle } from './titleValidation.js'

export const AbgabetoolStudent = {
	name: "AbgabetoolStudent",
	components: {
		Accordion: primevue.accordion,
		AccordionTab: primevue.accordiontab,
		Textarea: primevue.textarea,
		BsModal,
		AbgabeDetail,
		FhcOverlay
	},
	provide() {
		return {
			notenOptions: Vue.computed(() => this.notenOptions),
			isViewMode: Vue.computed(() => this.isViewMode),
			moodle_link: Vue.computed(() => this.moodle_link),
			title_edit_allowed: Vue.computed(() => this.title_edit_allowed),
			confetti_on_endupload: Vue.computed(() => this.confetti_on_endupload),
			siginfolink_german: Vue.computed(() => this.siginfolink_german),
			siginfolink_english: Vue.computed(() => this.siginfolink_english)
		}
	},
	props: {
		student_uid_prop: {
			default: null
		},
	},
	data() {
		return {
			activeTabIndex: [0],
			abgabeTypeOptions: null,
			phrasenPromise: null,
			phrasenResolved: false,
			loading: false,
			notenOptions: null,
			detail: null,
			projektarbeiten: null,
			selectedProjektarbeit: null,
			moodle_link: null,
			uid: null,
			title_edit_allowed: null,
			confetti_on_endupload: null,
			siginfolink_german: null,
			siginfolink_english: null,
			editingTitel: '',
			editingProjektarbeit: null,
			uid: null
		};
	},
	computed: {
		isViewMode() {
			return this.student_uid !== this.uid
		},
		student_uid() {
			return this.student_uid_prop || this.uid || null
		}
	},
	methods: {
		openTitelEdit(projektarbeit, event) {
			// stop the click from toggling the accordion tab
			event.stopPropagation();
			this.editingProjektarbeit = projektarbeit;
			this.editingTitel = projektarbeit.titel ?? '';
			this.$refs.modalTitelEdit.show();
		},
		async saveTitel() {
			const validation = validateThesisTitle(this.editingTitel);

			if (!validation.isValid) {
				if (validation.error === 'empty') {
					this.$fhcAlert.alertWarning(this.$p.t('abgabetool/c4emptyThesisTitle'))
				} else if (validation.error === 'invalid_characters') {
					this.$fhcAlert.alertWarning(this.$p.t('abgabetool/c4invalidCharactersThesisTitle'))

				}
				return false;
			}

			const confirmed = await this.$fhcAlert.confirm({
				message: this.$p.t('abgabetool/c4confirmTitelSpeichern'),
				acceptLabel: this.$capitalize(this.$p.t('ui/speichern')),
				acceptClass: 'p-button-primary',
				rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
				rejectClass: 'p-button-secondary'
			});

			if (confirmed === false) return;

			this.loading = true;
			this.$api.call(
				ApiAbgabe.postStudentProjektarbeitTitel(
					this.editingProjektarbeit.projektarbeit_id,
					validation.cleanedTitle
				)
			).then(res => {
				if (res.meta.status === 'success') {
					// update the local list entry in-place so the accordion header reflects it immediately
					this.editingProjektarbeit.titel = res.data;
					// keep the open detail modal in sync if it happens to be showing this projektarbeit
					if (this.selectedProjektarbeit?.projektarbeit_id === this.editingProjektarbeit.projektarbeit_id) {
						this.selectedProjektarbeit.titel = res.data;
					}
					this.$fhcAlert.alertSuccess(this.$capitalize(this.$p.t('abgabetool/c4titelSavedSuccess')));
					this.$refs.modalTitelEdit.hide();
				} else {
					this.$fhcAlert.alertError(this.$capitalize(this.$p.t('abgabetool/c4titelSaveError')));
				}
			}).finally(() => {
				this.loading = false;
			});
		},
		handleTitelUpdated(projektarbeit_id, titel) {
			const pa = this.projektarbeiten?.find(p => p.projektarbeit_id === projektarbeit_id);
			if (pa) pa.titel = titel;
		},
		checkQualityGatesStrict(termine) {
			let qgate1Passed = false
			let qgate2Passed = false

			termine.forEach(t => {
				const noteOption = this.notenOptions?.find(opt => opt.note == t.note)
				if(noteOption && noteOption.positiv) {
					if(t.paabgabetyp_kurzbz == 'qualgate1') {
						qgate1Passed = true
					} else if(t.paabgabetyp_kurzbz == 'qualgate2') {
						qgate2Passed = true
					}
				}
			})

			return qgate1Passed && qgate2Passed
		},
		checkQualityGatesOptional(termine) {
			const qgate1found =  termine.find(t => t.paabgabetyp_kurzbz == 'qualgate1')
			const qgate2found =  termine.find(t => t.paabgabetyp_kurzbz == 'qualgate2')

			let qgate1positiv = true
			if(qgate1found) {
				qgate1positiv = false

				termine.forEach(t => {
					const noteOption = this.notenOptions?.find(opt => opt.note == t.note)
					if(noteOption && noteOption.positiv) {
						if (t.paabgabetyp_kurzbz == 'qualgate1') {
							qgate1positiv = true
						}
					}
				})
			}

			let qgate2positiv = true
			if(qgate2found) {
				qgate2positiv = false

				termine.forEach(t => {
					const noteOption = this.notenOptions?.find(opt => opt.note == t.note)
					if(noteOption && noteOption.positiv) {
						if (t.paabgabetyp_kurzbz == 'qualgate2') {
							qgate2positiv = true
						}
					}
				})
			}

			return qgate1positiv && qgate2positiv
		},
		isPastDate(date) {
			const deadline = luxon.DateTime.fromISO(date, { zone: 'Europe/Vienna' }).endOf('day');
			const nowInVienna = luxon.DateTime.now().setZone('Europe/Vienna');
			return nowInVienna > deadline;
		},
		setDetailComponent(details){
			this.loading = true
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval

				const paIsBenotet = pa.note !== null

				pa.abgabetermine.forEach(termin => {
					termin.file = []
					termin.allowedToUpload = false

					if(termin.paabgabetyp_kurzbz == 'end') {
						const inTime = termin.fixtermin ? !this.isPastDate(termin.datum) : true
						termin.allowedToUpload = inTime && this.checkQualityGatesOptional(pa.abgabetermine)
					} else if(termin.fixtermin) {
						termin.allowedToUpload = !this.isPastDate(termin.datum)
					} else {
						termin.allowedToUpload = termin.upload_allowed
					}

					if(paIsBenotet) termin.allowedToUpload = false

					termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)
					termin.dateStyle = getDateStyleClass(termin, this.notenOptions)
				})

				pa.betreuer = this.buildBetreuer(pa)
				pa.student_uid = this.student_uid

				this.selectedProjektarbeit = pa

				this.$refs.modalContainerAbgabeDetail.show()

			}).finally(()=>{this.loading=false})
		},
		centeredTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
		},
		detailFormatter(cell) {
			const val = cell.getValue()

			if(val.mode === 'detailTermine') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
			} else if (val.mode === 'beurteilungDownload') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			}
		},
		mailFormatter(cell) {
			const val = cell.getValue()
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
				'<a href='+val+'><i class="fa fa-envelope" style="color:#00649C"></i></a></div>'
		},
		beurteilungFormatter(cell) {
			const val = cell.getValue()
			if(val) {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			} else return '-'
		},
		buildMailToLink(projekt) {
			return 'mailto:' + projekt.email
		},
		buildBetreuer(abgabe) {
			return (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ? ' ' + abgabe.btitelpost : '')
		},
		async setupData(data){
			const projektarbeiten = data[0] ?? null
			if(!projektarbeiten) return
			this.projektarbeiten = projektarbeiten.map(projekt => {
				let mode = 'detailTermine'

				return {
					...projekt,
					details: {
						student_uid: this.student_uid,
						projektarbeit_id: projekt.projektarbeit_id,
						betreuer_person_id: projekt.bperson_id,
						betreuerart_kurzbz: projekt.betreuerart_kurzbz,
						mode
					},
					beurteilung1: projekt.downloadLink1 ?? null,
					beurteilung2: projekt.downloadLink2 ?? null,
					sem: projekt.studiensemester_kurzbz,
					stg: projekt.kurzbzlang,
					mail: this.buildMailToLink(projekt),
					betreuer: this.buildBetreuer(projekt),
					typ: projekt.projekttypbezeichnung,
					titel: projekt.titel
				}
			})

		},
		loadProjektarbeiten() {
			this.$api.call(ApiAbgabe.getStudentProjektarbeiten(this.student_uid))
				.then(res => {
					if(res?.data) this.setupData(res.data)
				})
		},
		loadAbgaben(details) {
			return new Promise((resolve) => {
				this.$api.call(ApiAbgabe.getStudentProjektabgaben(details))
					.then(res => {
						resolve(res)
					})
			})
		},
		async setupMounted() {
			this.loadProjektarbeiten()
		},
		getAccTabHeaderForProjektarbeit(projektarbeit) {
			let title = ''
			title += projektarbeit.titel ?? this.$p.t('abgabetool/keinTitel')
			return title
		},
		getMailLink(projektarbeit) {
			if(projektarbeit.email) {
				return 'mailto:'+projektarbeit.email
			} else return ''
		},
		getNoteBezeichnung(projektarbeit) {
			if(projektarbeit.note && this.notenOptions) {
				const noteOpt = this.notenOptions.find(opt => opt.note == projektarbeit.note)
				return noteOpt?.bezeichnung
			} else {
				return ''
			}
		},
		handleDownloadBeurteilung1(projektarbeit) {
			window.open(projektarbeit.beurteilung1)
		},
		handleDownloadBeurteilung2(projektarbeit) {
			window.open(projektarbeit.beurteilung2)
		},
		async fetchAuthUID() {
			const authIdResponse = await this.$api.call(ApiAuthinfo.getAuthUID());
			this.uid = authIdResponse.data.uid;
		},
	},
	watch: {},
	async created() {
		// make sure zoom media query doesnt spill ever to other CIS4 sites
		document.documentElement.classList.add('abgabetool');
		
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})

		this.loading = true
		await this.$api.call(ApiAbgabe.getNoten()).then(res => {
			if(res.meta.status == 'success') {
				this.notenOptions = res.data[0]

				this.allowedNotenOptions = this.notenOptions.filter(
					opt => res.data[1].includes(opt.note)
				)
			}
		}).finally(() => {
			this.loading = false
		})

		this.$api.call(ApiAbgabe.getPaAbgabetypen()).then(res => {
			this.abgabeTypeOptions = res.data
		}).catch(e => {
			this.loading = false
		})

		this.$api.call(ApiAbgabe.getConfigStudent()).then(res => {
			this.moodle_link = res.data?.moodle_link
			this.title_edit_allowed = res.data?.title_edit_allowed
			this.confetti_on_endupload = res.data?.confetti_on_endupload
			this.siginfolink_german = res.data?.siginfolink_german
			this.siginfolink_english = res.data?.siginfolink_english
		}).catch(e => {
			this.loading = false
		})

		await this.fetchAuthUID();
	},
	mounted() {
		this.setupMounted()
	},
	beforeUnmount() {
		document.documentElement.classList.remove('abgabetool');
	},
	template: `
<template v-if="phrasenResolved">
	<FhcOverlay :active="loading"></FhcOverlay>
	
	<bs-modal ref="modalContainerAbgabeDetail" class="bootstrap-prompt"
		dialogClass="modal-xl" :allowFullscreenExpand="true" bodyClass="px-4 py-4">
		<template v-slot:title>
			<div>
				{{$capitalize( $p.t('abgabetool/c4abgabeStudentDetailTitle') )}}
			</div>
		</template>
		<template v-slot:default>
			<AbgabeDetail
				:projektarbeit="selectedProjektarbeit"
				@titel-updated="handleTitelUpdated"
			></AbgabeDetail>
		</template>
	</bs-modal>
	<bs-modal
		ref="modalTitelEdit"
		class="bootstrap-prompt"
		dialogClass="bordered-modal"
		 bodyClass="px-4 py-4"
	>
		<template v-slot:title>
			{{$capitalize( $p.t('abgabetool/c4titelBearbeiten') )}}
		</template>
		<template v-slot:default>
			<div class="mb-2">
				<label class="form-label fw-bold">
					{{$capitalize( $p.t('abgabetool/c4titel') )}}
				</label>
				<Textarea 
					v-model="editingTitel" 
					rows="2" 
					maxlength="1024" 
					class="form-control w-100"
					@keydown.enter.prevent="saveTitel"
				/>
				<div class="form-text text-end">{{ editingTitel.length }} / 1024</div>
			</div>
		</template>
		<template v-slot:footer>
			<button
				class="btn btn-secondary"
				@click="$refs.modalTitelEdit.hide()"
			>
				{{$capitalize( $p.t('abgabetool/c4Cancel') )}}
			</button>
			<button
				class="btn btn-primary"
				:disabled="!editingTitel.trim()"
				@click="saveTitel"
			>
				<i class="fa-solid fa-floppy-disk me-1"></i>
				{{$capitalize( $p.t('ui/speichern') )}}
			</button>
		</template>
	</bs-modal>
	
	<h2>{{$capitalize( $p.t('abgabetool/abgabetoolTitle') )}}</h2>
	<hr>
	
	<div v-if="projektarbeiten === null || projektarbeiten?.length == 0">
		{{$capitalize( $p.t('abgabetool/c4abgabeStudentNoProjectsFound') )}}
	</div>
	
	<Accordion :multiple="true" :activeIndex="activeTabIndex">
		<template v-for="projektarbeit in projektarbeiten" :key="projektarbeit.projektarbeit_id">
			<AccordionTab>
				
				<template #header>
					<div class="d-flex row w-100">
						<div class="text-start" :class="projektarbeit.note != null ? 'col-6' : 'col-12'"
							style="min-width: 0;">
							<span
								:title="getAccTabHeaderForProjektarbeit(projektarbeit)"
								style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 600px;"
							>{{getAccTabHeaderForProjektarbeit(projektarbeit)}}</span>
						</div>
						<div class="col-6 text-end">
							<span>{{getNoteBezeichnung(projektarbeit)}}</span>
						</div>
					</div>
				</template>
				
				<div class="row">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4details') )}}</div>
					<div class="col-8 col-md-9">
						<button @click="setDetailComponent(projektarbeit.details)" class="btn btn-primary">
							{{$capitalize( $p.t('abgabetool/c4projektdetailsOeffnen') )}} <a><i class="fa fa-folder-open"></i></a>
						</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4beurteilung') )}}</div>
					<div class="col-8 col-md-9">
						<button v-if="projektarbeit.beurteilung1" @click="handleDownloadBeurteilung1(projektarbeit)" class="btn btn-primary">
							<a> {{$capitalize( $p.t('abgabetool/c4downloadBeurteilungErstbetreuerv2') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
						</button>
						<a v-else>{{$capitalize( $p.t('abgabetool/c4nobeurteilungVorhanden') )}}</a>
						<button v-if="projektarbeit.beurteilung2" @click="handleDownloadBeurteilung2(projektarbeit)" class="btn btn-primary" style="margin-left: 4px;">
							<a> {{$capitalize( $p.t('abgabetool/c4downloadBeurteilungZweitbetreuerv2') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
						</button>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4sem') )}}</div>
					<div class="col-8 col-md-9">
						{{ projektarbeit.sem }}
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4stg') )}}</div>
					<div class="col-8 col-md-9">
						<div class="col-1 d-flex justify-content-start align-items-start">
							{{ projektarbeit.stg }}
						</div>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{ projektarbeit?.betreuerart_kurzbz ? $capitalize( $p.t('abgabetool/c4betrart' + projektarbeit.betreuerart_kurzbz) ) : $capitalize( $p.t('abgabetool/c4betreuerv2') ) }}</div>
					<div class="col-8 col-md-9">
						{{ projektarbeit.betreuerart_kurzbz ? projektarbeit.betreuer : '' }}
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4betreuerEmailKontaktv2') )}}</div>
					<div class="col-8 col-md-9">
						<a :href="getMailLink(projektarbeit)"><i class="fa fa-envelope" style="color:#00649C"></i></a>
					</div>
				</div>
				<div v-if="projektarbeit.zweitbetreuer_person_id || projektarbeit.zweitbetreuer" class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{ projektarbeit.zweitbetreuer_betreuerart_kurzbz ? $p.t('abgabetool/c4betrart' + projektarbeit.zweitbetreuer_betreuerart_kurzbz) : '' }}</div>
					<div class="col-8 col-md-9">
						{{ projektarbeit.zweitbetreuer?.first }}
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4projekttyp') )}}</div>
					<div class="col-8 col-md-9">
						{{ projektarbeit.projekttypbezeichnung }}					
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4titel') )}}</div>
					<div class="col-8 col-md-9 d-flex align-items-center gap-2" style="min-width: 0;">
						<span
							:title="projektarbeit.titel"
							style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
						>{{ projektarbeit.titel }}</span>
						<button
							v-if="title_edit_allowed && !isViewMode && projektarbeit.note == null"
							class="btn btn-sm btn-outline-secondary border-0 p-1"
							v-tooltip.right="{ value: $capitalize($p.t('abgabetool/c4titelBearbeiten')), class: 'custom-tooltip' }"
							@click="openTitelEdit(projektarbeit, $event)"
						>
							<i class="fa-solid fa-pen"></i>
						</button>
					</div>
				</div>
				
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4note') )}}</div>

					<div class="col-8 col-md-9">
						<span>{{getNoteBezeichnung(projektarbeit)}}</span>					
					</div>
					
				</div>
			</AccordionTab>
		</template>
	</Accordion>
</template>
    `,
};

export default AbgabetoolStudent;
