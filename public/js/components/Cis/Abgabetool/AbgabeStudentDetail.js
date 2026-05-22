import Upload from '../../../components/Form/Upload/Dms.js';
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import FhcOverlay from "../../Overlay/FhcOverlay.js";

export const AbgabeStudentDetail = {
	name: "AbgabeStudentDetail",
	components: {
		Upload,
		BsModal,
		InputNumber: primevue.inputnumber,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		Accordion: primevue.accordion,
		AccordionTab: primevue.accordiontab,
		Message: primevue.message,
		Inplace: primevue.inplace,
		VueDatePicker,
		FhcOverlay
	},
	inject: ['notenOptions', 'isMobile', 'isViewMode', 'moodle_link'],
	props: {
		projektarbeit: {
			type: Object,
			default: null
		},
		viewMode: {
			type: Boolean,
			default: false
		}
	},
	emits: ['titel-updated'],
	data() {
		return {
			loading: false,
			eidAkzeptiert: false,
			enduploadTermin: null,
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
			editingTitel: '',
			form: Vue.reactive({
				sprache: '',
				abstract: '',
				abstract_en: '',
				schlagwoerter: '',
				schlagwoerter_en: '',
				kontrollschlagwoerter: '',
				seitenanzahl: 1,
			})
		}
	},
	methods: {
		openTitelEdit() {
			this.editingTitel = this.projektarbeit.titel ?? '';
			this.$refs.modalTitelEdit.show();
		},
		async saveTitel() {
			const trimmed = this.editingTitel.trim();
			if (!trimmed) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('global/warningEmptyField')));
				return;
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
					this.projektarbeit.projektarbeit_id,
					trimmed
				)
			).then(res => {
				if (res.meta.status === 'success') {
					this.projektarbeit.titel = trimmed;
					this.$emit('titel-updated', {
						projektarbeit_id: this.projektarbeit.projektarbeit_id,
						titel: trimmed
					});
					this.$fhcAlert.alertSuccess(this.$capitalize(this.$p.t('abgabetool/c4titelSavedSuccess')));
					this.$refs.modalTitelEdit.hide();
				} else {
					this.$fhcAlert.alertError(this.$capitalize(this.$p.t('abgabetool/c4titelSaveError')));
				}
			}).finally(() => {
				this.loading = false;
			});
		},
		getNoteBezeichnung(termin){
			const noteOpt = this.notenOptions.find(opt => opt.note == termin.note)

			if(noteOpt?.bezeichnung) {
				return noteOpt?.positiv ? this.$capitalize(this.$p.t('abgabetool/c4positivBenotet')) + ' ✅' : this.$capitalize(this.$p.t('abgabetool/c4negativBenotet')) + ' ❌'
			} else if(noteOpt?.benotbar === true && !termin.note) {
				return this.$capitalize(this.$p.t('abgabetool/c4notYetGraded'));
			} else {
				return ''
			}
		},
		async validate(termin, endupload = false) {
			if(!termin.file.length) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('global/warningChooseFile')));
				return false
			}

			if(endupload) {
				if(await this.$fhcAlert.confirm({
					message: this.$p.t('abgabetool/confirmEnduploadSpeichern'),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'p-button-primary',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'p-button-secondary'
				}) === false) {
					return false
				}
			}

			return true;
		},
		async triggerEndupload() {

			if (!await this.validate(this.enduploadTermin, true))
			{
				return false;
			}

			// post endabgabe
			const formData = new FormData();
			formData.append('paabgabetyp_kurzbz', this.enduploadTermin.paabgabetyp_kurzbz)
			formData.append('projektarbeit_id', this.enduploadTermin.projektarbeit_id);
			formData.append('paabgabe_id', this.enduploadTermin.paabgabe_id)
			formData.append('student_uid', this.projektarbeit.student_uid)
			formData.append('bperson_id', this.projektarbeit.bperson_id)

			formData.append('sprache', this.form['sprache'].sprache)
			formData.append('abstract', this.form['abstract'])
			formData.append('abstract_en', this.form['abstract_en'])
			formData.append('schlagwoerter', this.form['schlagwoerter'])
			formData.append('schlagwoerter_en', this.form['schlagwoerter_en'])
			formData.append('seitenanzahl', this.form['seitenanzahl'])

			for (let i = 0; i < this.enduploadTermin.file.length; i++) {
				formData.append('file', this.enduploadTermin.file[i]);
			}
			this.loading = true
			this.$api.call(ApiAbgabe.postStudentProjektarbeitEndupload(formData))
				.then(res => {
					this.handleUploadRes(res, this.enduploadTermin)
				}).finally(()=> {
				this.loading = false
			})

			this.$refs.modalContainerEnduploadZusatzdaten.hide()
		},
		downloadAbgabe(termin) {
			const url = `/api/frontend/v1/Abgabe/getStudentProjektarbeitAbgabeFile?paabgabe_id=${termin.paabgabe_id}&student_uid=${this.projektarbeit.student_uid}&projektarbeit_id=${this.projektarbeit.projektarbeit_id}`;

			window.open(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url)
		},
		formatDate(dateParam) {
			const date = new Date(dateParam)
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getUTCMonth() + 1);
			const day = padZero(date.getUTCDate());
			const year = date.getUTCFullYear();

			return `${day}.${month}.${year}`
		},
		async upload(termin) {

			if (! await this.validate(termin))
			{
				return false;
			}

			if(termin.bezeichnung?.paabgabetyp_kurzbz === 'end') {
				this.enduploadTermin = termin
				this.$refs.modalContainerEnduploadZusatzdaten.show()
			} else {
				const formData = new FormData();
				formData.append('paabgabetyp_kurzbz', termin.paabgabetyp_kurzbz)
				formData.append('projektarbeit_id', this.projektarbeit.projektarbeit_id)
				formData.append('paabgabe_id', termin.paabgabe_id)
				formData.append('student_uid', this.projektarbeit.student_uid)
				formData.append('bperson_id', this.projektarbeit.bperson_id)

				for (let i = 0; i < termin.file.length; i++) {
					formData.append('file', termin.file[i]);
				}

				this.loading = true
				this.$api.call(ApiAbgabe.postStudentProjektarbeitZwischenabgabe(formData))
					.then(res => {
						this.handleUploadRes(res, termin)
					}).finally(()=> {
					this.loading = false
				})
			}
		},
		handleUploadRes(res, termin) {
			if(res.meta.status == "success") {
				this.$fhcAlert.alertSuccess(this.$capitalize(this.$p.t('abgabetool/c4fileUploadSuccessv3')))

				termin.abgabedatum = new Date().toISOString().split('T')[0];
				if(res?.data?.signatur !== undefined) {
					termin.signatur = res.data.signatur
				}

			} else {
				this.$fhcAlert.alertError(this.$capitalize(this.$p.t('abgabetool/c4fileUploadErrorv3')))
			}

			if(res.meta.signaturInfo) {
				this.$fhcAlert.alertInfo(res.meta.signaturInfo)
			}
		},
		getOptionLabel(option) {
			return option.sprache
		},
		getTerminNoteBezeichnung(termin) {
			const noteOpt = this.notenOptions.find(opt => opt.note == termin.note)
			return noteOpt ? noteOpt.bezeichnung : ''
		},
	},
	watch: {
		projektarbeit(newVal) {
			this.form.sprache = newVal.sprache ? this.allActiveLanguages.find(lang => lang.sprache == newVal.sprache) : this.allActiveLanguages.find(lang => lang.sprache == 'German')
			this.form.abstract = newVal.abstract ?? ''
			this.form.abstract_en = newVal.abstract_en ?? ''
			this.form.schlagwoerter = newVal.schlagwoerter ?? ''
			this.form.schlagwoerter_en = newVal.schlagwoerter_en ?? ''
			this.form.kontrollschlagwoerter = newVal.kontrollschlagwoerter ?? ''
			this.form.seitenanzahl = newVal.seitenanzahl ?? 1
		}
	},
	computed: {
		getMoodleLink() {
			return this.moodle_link + this.projektarbeit.studiengang_kz
		},
		getMessagePtStyle() {
			return {
				root: {
					style: {
						margin: '0px'
					}
				},
				wrapper: {
					style: {
						padding: '6px'
					}
				}
			}
		},
		getEid() {
			return this.$capitalize(this.$p.t('abgabetool/c4eidesstattlicheErklaerung'))
		},
		allowedToSaveZusatzdaten() {
			return this.form.schlagwoerter.length > 0 && this.form.schlagwoerter_en.length > 0 && this.form.abstract.length > 0 && this.form.abstract_en.length > 0 && this.form.seitenanzahl > 0
		},
		getAllowedToSendEndupload() {
			return this.eidAkzeptiert && this.allowedToSaveZusatzdaten
		},
		qualityGateTerminAvailable() {
			let qgatefound = false
			this.projektarbeit?.abgabetermine.forEach(abgabe => {
				if(abgabe.paabgabetyp_kurzbz == 'qualgate1'
					|| abgabe.paabgabetyp_kurzbz == 'qualgate2') {
					qgatefound = true
				}
			})
			return qgatefound
		},
		isTitelEditAllowed() {
			// blocked once the projektarbeit has a note (finished) - mirrors backend guard
			return !this.isViewMode && !this.projektarbeit?.note;
		},
		getTooltipVerspaetet() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipVerspaetet')), class: "custom-tooltip" }
		},
		getTooltipVerpasst() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipVerpasst')), class: "custom-tooltip" }
		},
		getTooltipAbzugeben() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbzugeben')), class: "custom-tooltip" }
		},
		getTooltipStandard() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipStandardv2')), class: "custom-tooltip" }
		},
		getTooltipAbgegeben() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbgegeben')), class: "custom-tooltip" }
		},
		getTooltipFixtermin() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipFixtermin')), class: "custom-tooltip" }
		},
		getTooltipAbgabeDetected() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbgabeDetected')), class: "custom-tooltip" }
		},
		getTooltipNotAllowedToUpload() {
			if(this.isViewMode) {
				return { value: this.$capitalize(this.$p.t('abgabetool/c4studentAbgabeNotAllowedInViewMode')), class: "custom-tooltip" }
			} else {
				return { value: this.$capitalize(this.$p.t('abgabetool/c4studentAbgabeNotAllowedRegular')), class: "custom-tooltip" }
			}
		},
		getTooltipBeurteilungerforderlich() {
			return { value: this.$capitalize(this.$p.t('abgabetool/c4tooltipBeurteilungerforderlich')), class: "custom-tooltip" }
		},
		getTooltipBestanden() {
			return { value: this.$p.t('abgabetool/c4tooltipBestanden'), class: "custom-tooltip" }
		},
		getTooltipNichtBestanden() {
			return { value: this.$p.t('abgabetool/c4tooltipNichtBestanden'), class: "custom-tooltip" }
		},
	},
	template: `
		<FhcOverlay :active="loading"></FhcOverlay>

		<div v-if="projektarbeit">
		
			<h5>{{$capitalize( $p.t('abgabetool/c4abgabeStudentenbereich') )}}</h5>
			<div class="row">
				<div class="col-8">
					<p>{{$capitalize( $p.t('person/student') ) }}: {{projektarbeit?.student}}</p>

					<p class="d-flex align-items-center gap-2 mb-2" style="min-width: 0;">
						<span
							:title="projektarbeit.titel"
							style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 480px;"
						>{{$capitalize( $p.t('abgabetool/c4titel') ) }}: {{projektarbeit?.titel}}</span>
						<button
							v-if="isTitelEditAllowed"
							class="btn btn-sm btn-outline-secondary border-0 p-1"
							v-tooltip.right="{ value: $capitalize($p.t('abgabetool/c4titelBearbeiten')), class: 'custom-tooltip' }"
							@click="openTitelEdit"
						>
							<i class="fa-solid fa-pen"></i>
						</button>
					</p>

					<p>{{$capitalize( $p.t('abgabetool/c4betreuerv2') ) }}: {{projektarbeit ? $p.t('abgabetool/c4betrart' + projektarbeit.betreuerart_kurzbz) + ' ' + projektarbeit.betreuer : ''}}</p>
				</div>
				<div class="col-4">
					<p>{{ $p.t('abgabetool/c4checkoutStgMoodleInfos') }} 
						<a :href="getMoodleLink" target="_blank">Moodle</a>
					</p>
				</div>
			</div>
			
			<Accordion :multiple="true">
				<template v-for="termin in this.projektarbeit?.abgabetermine" :key="termin.paabgabe_id">
					<AccordionTab :headerClass="termin.dateStyle + '-header'">
						<template #header>
							<div class="d-flex flex-nowrap align-items-center w-100">
								<div class="flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; margin-left: -68px;">
									<i v-if="termin.dateStyle == 'verspaetet'" v-tooltip.right="getTooltipVerspaetet" class="fa-solid fa-triangle-exclamation"></i>
									<i v-else-if="termin.dateStyle == 'verpasst'" v-tooltip.right="getTooltipVerpasst" class="fa-solid fa-calendar-xmark"></i>
									<i v-else-if="termin.dateStyle == 'abzugeben'" v-tooltip.right="getTooltipAbzugeben" class="fa-solid fa-hourglass-half"></i>
									<i v-else-if="termin.dateStyle == 'standard'" v-tooltip.right="getTooltipStandard" class="fa-solid fa-clock"></i>
									<i v-else-if="termin.dateStyle == 'abgegeben'" v-tooltip.right="getTooltipAbgegeben" class="fa-solid fa-paperclip"></i>
									<i v-else-if="termin.dateStyle == 'beurteilungerforderlich'" v-tooltip.right="getTooltipBeurteilungerforderlich" class="fa-solid fa-list-check"></i>
									<i v-else-if="termin.dateStyle == 'bestanden'" v-tooltip.right="getTooltipBestanden" class="fa-solid fa-check"></i>
									<i v-else-if="termin.dateStyle == 'nichtbestanden'" v-tooltip.right="getTooltipNichtBestanden" class="fa-solid fa-circle-exclamation"></i>
								</div>
								<div class="text-start px-2" style="min-width: 150px; max-width: 300px; margin-left: 40px">
									<span>{{ termin ? $p.t('abgabetool/c4paatyp' + termin.paabgabetyp_kurzbz) : '' }}</span>
								</div>
								<div class="text-start px-2" style="min-width: 100px;">
									<span>{{ formatDate(termin.datum) }}</span>
								</div>
								<div class="px-1">
									<i v-if="termin?.fixtermin" v-tooltip.right="getTooltipFixtermin" class="fa-solid fa-lock"></i>
									<i v-if="termin?.abgabedatum && isMobile" v-tooltip.right="getTooltipAbgabeDetected" class="fa-solid fa-file"></i>
								</div>
								<div v-if="termin?.abgabedatum && !isMobile" class="px-1">
									<i v-tooltip.right="getTooltipAbgabeDetected" class="fa-solid fa-file"></i>
								</div>
								<div class="flex-grow-1 text-end pe-2">
									<span class="fw-bold">{{getNoteBezeichnung(termin)}}</span>
								</div>
							</div>				
						</template>
						
						<div v-if="isMobile" class="row mt-2 align-items-center">						
							<Inplace
								closable
								:closeButtonProps="{
									style: {
										position: 'relative',
										bottom: '100px',
										left: '80%',
										zIndex: 1
									}
								}"
							>
								<template #display>{{ $capitalize($p.t('abgabetool/c4tapForTooltipInfo'))}}</template>
								<template #content>
									<div class="col-auto">
										<div class="row">
											<div class="col-12 col-md-3 fw-bold align-content-center">{{ $capitalize($p.t('abgabetool/c4abgabeZeitstatus')) }}</div>
											<div class="col-12 col-md-9">{{$p.t('abgabetool/c4tooltip' + $capitalize(termin?.dateStyle) )}}</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-3 fw-bold align-content-center">{{ $capitalize($p.t('abgabetool/c4fixterminv4')) }}</div>
											<div class="col-12 col-md-9">{{!termin?.fixtermin}}</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-3 fw-bold align-content-center">{{ $capitalize($p.t('abgabetool/c4fileUploaded')) }}</div>
											<div class="col-12 col-md-9">{{termin?.abgabedatum !== null}}</div>
										</div>
									</div>
								</template>
							</Inplace>
						</div>
						
						<div class="row mt-2">
							<div class="col-12 col-md-3 align-content-center">
								<div class="row fw-bold" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4zieldatumv2') )}}</div>
								<div class="row fw-light" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4abgabeuntil2359') )}}</div>
							</div>
							<div class="col-12 col-md-9">
								<VueDatePicker
									v-model="termin.datum"
									:clearable="false"
									:disabled="true"
									:enable-time-picker="false"
									locale="de"
									format="dd.MM.yyyy"
									:text-input="true"
									auto-apply>
								</VueDatePicker>
							</div>
						</div>
						
						<div class="row mt-2">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabetyp') )}}</div>
							<div class="col-12 col-md-9">
								{{ termin ? $p.t('abgabetool/c4paatyp' + termin.paabgabetyp_kurzbz) : '' }}
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.note">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4note') )}}</div>
							<div class="col-12 col-md-9">
								<div class="col-auto d-flex justify-content-start align-items-start">
									{{ getTerminNoteBezeichnung(termin) }}
								</div>
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.paabgabetyp_kurzbz === 'qualgate1' || termin.paabgabetyp_kurzbz === 'qualgate2'">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4notizQualGatev2') )}}</div>
							<div class="col-12 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.beurteilungsnotiz" rows="1" class="w-100" disabled></Textarea>
							</div>
						</div>
						
						<div v-if="termin.kurzbz && termin.kurzbz.length > 0" class="row mt-2">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabekurzbzv2') )}}</div>
							<div class="col-12 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="1" class="w-100" :disabled="true"></Textarea>
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.upload_allowed">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabedatum') )}}</div>
							<div class="col-12 col-md-9">
							<template v-if="termin?.abgabedatum">
								<div class="row">
									<div style="width:100px; align-content: center;">
										<h6>{{ termin.abgabedatum?.split("-").reverse().join(".") }}</h6>
									</div>
									
									<div class="col-auto">
										<button v-if="termin?.abgabedatum" @click="downloadAbgabe(termin)" class="btn btn-primary">
											<a> {{$capitalize($p.t('abgabetool/c4downloadAbgabe') )}} <i class="fa fa-file-pdf" style="margin-left:4px; cursor: pointer;"></i></a>
										</button>
									</div>	
									<template v-if="termin.paabgabetyp_kurzbz == 'end'">	
										<div v-if="termin?.signatur !== undefined && termin?.signatur !== null" class="col-auto">
											<Message v-if="termin?.signatur == true" severity="success" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4signaturGefunden') }} </Message>
											<Message v-else-if="termin?.signatur == false" severity="error" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4keineSignatur') }} </Message>
											<Message v-else-if="termin?.signatur == 'error'" severity="warn" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4signaturServerError') }} </Message>
										</div>
									</template>
								</div>					
							</template>
							<template v-else>
								{{ $capitalize( $p.t('abgabetool/c4nochNichtsAbgegeben') )}}
							</template>
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.upload_allowed">
							<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4fileupload') )}}</div>
							<div class="col-12 col-md-9">
								<div class="row" v-if="termin?.allowedToUpload">
									<div class="col-12 col-sm-6 mb-2">
										<Upload 
											accept=".pdf" 
											v-model="termin.file"
										></Upload>
									</div>
									<div class="col-12 col-sm-6">
										<button 
											class="btn btn-primary border-0 w-100" 
											@click="upload(termin)" 
										>
											{{$capitalize( $p.t('abgabetool/c4upload') )}}
											<i class="fa-solid fa-upload"></i>
										</button>
									</div>
								</div>
								<div class="row" v-else-if="!termin?.allowedToUpload || isViewMode" v-tooltip.right="getTooltipNotAllowedToUpload">
									<div class="col-12 col-sm-6 mb-2">
										<Upload 
											disabled
											accept=".pdf" 
											v-model="termin.file"
										></Upload>
									</div>
									<div class="col-12 col-sm-6">
										<button 
											class="btn btn-primary border-0 w-100" 
											@click="upload(termin)" 
											disabled
										>
											{{$capitalize( $p.t('abgabetool/c4upload') )}}
											<i class="fa-solid fa-upload"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</AccordionTab>
				</template>
			</Accordion>
			
			<div v-if="projektarbeit?.abgabetermine.length == 0" style="display:flex; justify-content: center; align-content: center;">
				<h5>{{ $capitalize( $p.t('abgabetool/c4keineAbgabetermineGefunden') )}}</h5>
			</div>
			
		</div>

		<bs-modal
			ref="modalTitelEdit"
			class="bootstrap-prompt"
			dialogClass="bordered-modal"
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
						rows="10" 
						maxlength="1024" 
						class="form-control w-100"
						@keyup.enter="saveTitel"
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

	 	<bs-modal 
	 		ref="modalContainerEnduploadZusatzdaten"
	 		class="bootstrap-prompt"
	 		dialogClass="bordered-modal modal-lg">
			<template v-slot:title>
				<div>
					{{$capitalize( $p.t('abgabetool/c4enduploadZusatzdaten') )}}
				</div>
				<div class="row mb-3 align-items-start">
					<p class="ml-4 mr-4">Student UID: {{ projektarbeit?.student_uid}}</p>
				</div>
				<div class="row mb-3 align-items-start">
					<p class="ml-4 mr-4">{{$capitalize( $p.t('abgabetool/c4titel') )}}: {{ projektarbeit?.titel }}</p>
				</div>
			</template>
			<template v-slot:default>
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4Sprache') )}}</div>
					<div class="row">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="form.sprache"
							:options="allActiveLanguages"
							:optionLabel="getOptionLabel">
						</Dropdown>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4schlagwoerterGer') )}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter" class="w-100"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4schlagwoerterEng') )}}</div>
					<div class="row">
						<Textarea v-model="form.schlagwoerter_en" class="w-100"></Textarea>
					</div>
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4abstractGer') )}}</div>
					<div class="row">
						<Textarea v-model="form.abstract" rows="10" maxlength="5000" class="w-100"></Textarea>
						<p>{{ form.abstract?.length ? form.abstract.length : 0 }} / 5000 characters</p>
					</div>
				</div>

				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4abstractEng') )}}</div>
					<div class="row">
						<Textarea v-model="form.abstract_en" rows="10" maxlength="5000" class="w-100"></Textarea>
						<p>{{ form.abstract_en?.length ? form.abstract_en.length : 0 }} / 5000 characters</p>
					</div>				
				</div>
				
				<div class="row mb-3 align-items-start">
					<div class="row">{{$capitalize( $p.t('abgabetool/c4seitenanzahl') )}}</div>
					<div class="row">
						<InputNumber 
							v-model="form.seitenanzahl"
							inputId="seitenanzahlInput" :min="1" :max="100000">
						</InputNumber>
					</div>		
				</div>
				
				<div v-if="projektarbeit">
					<div v-html="getEid"></div>
					<div class="row">
						<div class="col-9"></div>
						<div class="col-2"><p>{{$capitalize( $p.t('abgabetool/c4gelesenUndAkzeptiert') )}}</p></div>
						<div class="col-1">
							<Checkbox 
								v-model="eidAkzeptiert" 
								:binary="true" 
								:pt="{ root: { class: 'ml-auto' }}"
							>
							</Checkbox>
						</div>
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<div v-show="!allowedToSaveZusatzdaten">{{ $p.t('abgabetool/c4zusatzdatenausfuellen') }}</div>
				<button class="btn btn-primary" :disabled="!getAllowedToSendEndupload" @click="triggerEndupload">{{$capitalize( $p.t('ui/hochladen') )}}</button>
			</template>
		</bs-modal>
    `,
};

export default AbgabeStudentDetail;