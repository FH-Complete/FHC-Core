import AbgabeDetail from "./AbgabeStudentDetail.js";
import ApiAbgabe from '../../../api/factory/abgabe.js'
import BsModal from "../../Bootstrap/Modal.js";
import FhcOverlay from "../../Overlay/FhcOverlay.js";

const today = new Date()
export const AbgabetoolStudent = {
	name: "AbgabetoolStudent",
	components: {
		Accordion: primevue.accordion,
		AccordionTab: primevue.accordiontab,
		BsModal,
		AbgabeDetail,
		FhcOverlay
	},
	inject: ['isMobile'],
	provide() {
		return {
			notenOptions: Vue.computed(() => this.notenOptions),
			isViewMode: Vue.computed(() => this.isViewMode)
		}
	},
	props: {
		student_uid_prop: {
			default: null
		},
		viewData: {
			type: Object,
			required: true,
			default: () => ({uid: ''}),
			validator(value) {
				return value && value.uid
			}
		}
	},
	data() {
		return {
			activeTabIndex: [0],
			phrasenPromise: null,
			phrasenResolved: false,
			loading: false,
			notenOptions: null,
			detail: null,
			projektarbeiten: null,
			selectedProjektarbeit: null
		};
	},
	methods: {
		dateDiffInDays(datum){
			const dateToday = luxon.DateTime.now().startOf('day');

			const dateDatum = luxon.DateTime.fromISO(datum).startOf('day');

			const duration = dateDatum.diff(dateToday, 'days');

			return duration.values.days;
		},
		getDateStyleClass(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			termin.diffindays = this.dateDiffInDays(termin.datum)

			if(today > datum && termin.benotbar && !termin.note) return 'beurteilungerforderlich'
			if (termin.abgabedatum === null && termin.upload_allowed) {
				if(datum < today) {
					return 'verpasst' // needs upload, missed it and has not submitted anything 
				} else if (datum > today && termin.diffindays <= 12) {
					return 'abzugeben' // needs to upload soon
				} else {
					return 'standard' // upload in distant future
				}
			}
			else if(abgabedatum > datum) {
				return 'verspaetet' // needs upload, missed it and has submitted smth late
			} else if(!termin.upload_allowed) {
				if(datum > today) return termin.diffindays <= 12 ? 'abzugeben' : 'standard'
				else if (today > datum) return 'abgegeben'
			} else {
				return 'abgegeben' // nothing else to do for that termin
			}
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
			return new Date(date) < new Date(Date.now())	
		},
		setDetailComponent(details){
			this.loading = true
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.abgabetermine.forEach(termin => {
					termin.file = []
					termin.allowedToUpload = false
					
					if(termin.paabgabetyp_kurzbz == 'end') {
						// old assumed production logic when qgates are required
						// termin.allowedToUpload = !this.isPastDate(termin.datum) && this.checkQualityGatesStrict(pa.abgabetermine)
						
						// new larifari we want qgates but they are optional fhtw mode
						termin.allowedToUpload = !this.isPastDate(termin.datum) && this.checkQualityGatesOptional(pa.abgabetermine)


						// development purposes
						// termin.allowedToUpload = this.checkQualityGatesStrict(pa.abgabetermine)
						// termin.allowedToUpload = true

					} else if(termin.fixtermin) {
						termin.allowedToUpload = !this.isPastDate(termin.datum)
					} else {
						// this could confuse people since we should dont show people this flag
						termin.allowedToUpload = termin.upload_allowed 
					}

					termin.dateStyle = this.getDateStyleClass(termin)
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
			// should always be "projekt.mitarbeiter_uid +'@'+ this.domain", built in backend
			return 'mailto:' + projekt.email
		},
		buildBetreuer(abgabe) {
			return (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ? ' ' + abgabe.btitelpost : '')
		},
		async setupData(data){
			// this.projektarbeiten = data[0]
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
		}
	},
	watch: {

	},
	computed: {
		isViewMode() {
			return this.student_uid !== this.viewData.uid
		},
		student_uid() {
			return this.student_uid_prop || this.viewData?.uid || null
		}
	},
	async created() {
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
		
		this.loading = true
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
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

	},
	mounted() {
		this.setupMounted()
	},
	template: `
<template v-if="phrasenResolved">
	<FhcOverlay :active="loading || saving"></FhcOverlay>
	
	<bs-modal ref="modalContainerAbgabeDetail" class="bootstrap-prompt"
		dialogClass="modal-xl" :allowFullscreenExpand="true">
		<template v-slot:title>
			<div>
				{{$capitalize( $p.t('abgabetool/c4abgabeStudentDetailTitle') )}}
			</div>
		</template>
		<template v-slot:default>
			<AbgabeDetail :projektarbeit="selectedProjektarbeit"></AbgabeDetail>
		</template>
	</bs-modal>
	
	<h2>{{$capitalize( $p.t('abgabetool/abgabetoolTitle') )}}</h2>
	<hr>
	
	<div v-if="projektarbeiten === null">
		{{$capitalize( $p.t('abgabetool/c4abgabeStudentNoProjectsFound') )}}
	</div>
	
	<Accordion :multiple="true" :activeIndex="activeTabIndex">
		<template v-for="projektarbeit in projektarbeiten">
			<AccordionTab>
				
				<template #header>
					<div class="d-flex row w-100">
						<div class="text-start" :class="projektarbeit.note != null ? 'col-6' : 'col-12'">
							<span>{{getAccTabHeaderForProjektarbeit(projektarbeit)}}</span>
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
							<a> {{$capitalize( $p.t('abgabetool/c4downloadBeurteilungErstbetreuer') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
						</button>
						<a v-else>{{$capitalize( $p.t('abgabetool/c4nobeurteilungVorhanden') )}}</a>
						<button v-if="projektarbeit.beurteilung2" @click="handleDownloadBeurteilung2(projektarbeit)" class="btn btn-primary" style="margin-left: 4px;">
							<a> {{$capitalize( $p.t('abgabetool/c4downloadBeurteilungZweitbetreuer') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
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
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4betreuer') )}}</div>
					<div class="col-8 col-md-9">
						{{ projektarbeit.betreuerart_kurzbz ? $p.t('abgabetool/c4betrart' + projektarbeit.betreuerart_kurzbz) : '' }}
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-4 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4betreuerEmailKontakt') )}}</div>
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
					<div class="col-8 col-md-9">
						{{ projektarbeit.titel }}	
					</div>
				</div>
			</AccordionTab>
		</template>
	</Accordion>
</template>
    `,
};

export default AbgabetoolStudent;
