import Upload from '../../../components/Form/Upload/Dms.js';
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'

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
		VueDatePicker
	},
	inject: ['notenOptions', 'isMobile', 'isViewMode'],
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
	data() {
		return {
			loading: false,
			eidAkzeptiert: false,
			enduploadTermin: null,
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
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
		async validate(termin, endupload = false) {
			if(!termin.file.length) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('global/warningChooseFile')));
				return false
			}
			
			// TODO: define these
			if(endupload) {
				// check these input fields for length of entry
				if(this.form['abstract'].length < 100 && await this.$fhcAlert.confirm({
					message: this.$p.t('abgabetool/warningShortAbstract'),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}

				if(this.form['abstract_en'].length < 100 && await this.$fhcAlert.confirm({
					message: this.$capitalize(this.$p.t('abgabetool/warningShortAbstractEn')),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}

				if(this.form['schlagwoerter'].length < 50 && await this.$fhcAlert.confirm({
					message: this.$capitalize(this.$p.t('abgabetool/warningShortSchlagwoerter')),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}

				if(this.form['schlagwoerter_en'].length < 50 && await this.$fhcAlert.confirm({
					message: this.$capitalize(this.$p.t('abgabetool/warningShortSchlagwoerterEn')),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}

				if(this.form['seitenanzahl'] <= 5 && await this.$fhcAlert.confirm({
					message: this.$capitalize(this.$p.t('abgabetool/warningSmallSeitenanzahl')),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
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
			const url = `/api/frontend/v1/Abgabe/getStudentProjektarbeitAbgabeFile?paabgabe_id=${termin.paabgabe_id}&student_uid=${this.projektarbeit.student_uid}`;

			window.open(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url)
			// this.$api.call(ApiAbgabe.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid))
		},
		formatDate(dateParam, showTime = true) {
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();

			return `${day}.${month}.${year}` + (showTime ? ' 23:59' : '');
		},
		async upload(termin) {

			// only do this on endupload
			if (! await this.validate(termin))
			{
				return false;
			}
			
			if(termin.bezeichnung === 'Endupload') {
				// open endupload form modal for further inputs
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

				// update 'abgabedatum' for successful upload -> shows the pdf icon and date once set
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
		getAccTabHeaderForTermin(termin) {
			let tabTitle = ''

			const datumFormatted = this.formatDate(termin.datum, false)
			tabTitle += termin.bezeichnung + ' ' + datumFormatted
			
			return tabTitle
		}
	},
	watch: {
		projektarbeit(newVal) {
			// default select german if projektarbeit sprache was null
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
		getMessagePtStyle() {
			// adjust outer spacing and internal padding to appear similar to doenload button in size
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
		getActiveIndexTabArray() {
			// here we try to do mind reading logic by assuming which abgabetermine are the most relevant to the current user

			// lets try to take the termin with nearest date and watch who complains and why
			let closestIndex = -1;
			let minDiff = Infinity;
			const today = new Date();

			this.projektarbeit.abgabetermine.forEach((obj, i) => {
				const diff = Math.abs(new Date(obj.datum) - today);
				if (diff < minDiff) {
					minDiff = diff;
					closestIndex = i;
				}
			});

			return [closestIndex]
		},
		getEid() {
			return this.$capitalize(this.$p.t('abgabetool/c4eidesstattlicheErklaerung'))
		},
		getAllowedToSendEndupload() {
			return !this.eidAkzeptiert
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
		getTooltipVerspaetet() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipVerspaetet')),
				class: "custom-tooltip"
			}
		},
		getTooltipVerpasst() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipVerpasst')),
				class: "custom-tooltip"
			}
		},
		getTooltipAbzugeben() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbzugeben')),
				class: "custom-tooltip"
			}
		},
		getTooltipStandard() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipStandard')),
				class: "custom-tooltip"
			}
		},
		getTooltipAbgegeben() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbgegeben')),
				class: "custom-tooltip"
			}
		},
		getTooltipFixtermin() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipFixtermin')),
				class: "custom-tooltip"
			}
		},
		getTooltipAbgabeDetected() {
			return {
				value: this.$capitalize(this.$p.t('abgabetool/c4tooltipAbgabeDetected')),
				class: "custom-tooltip"
			}
		},
		getTooltipNotAllowedToUpload() {
			if(this.isViewMode) {
				return {
					value: this.$capitalize(this.$p.t('abgabetool/c4studentAbgabeNotAllowedInViewMode')),
					class: "custom-tooltip"
				}
			} else {
				return {
					value: this.$capitalize(this.$p.t('abgabetool/c4studentAbgabeNotAllowedRegular')),
					class: "custom-tooltip"
				}
			}
		}
	},
	created() {
		
	},
	mounted() {

	},
	template: `
		<div id="loadingOverlay" v-show="loading" style="position: absolute; width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.5); z-index: 99999999999;">
			<i class="fa-solid fa-spinner fa-pulse fa-5x"></i>
		</div>

		<div v-if="projektarbeit">
		
			<h5>{{$capitalize( $p.t('abgabetool/c4abgabeStudentenbereich') )}}</h5>
			<div class="row">
				<p> {{projektarbeit?.betreuer}}</p>
				<p> {{projektarbeit?.titel}}</p>
			</div>
			
			<Accordion :multiple="true" :activeIndex="getActiveIndexTabArray">
				<template v-for="termin in this.projektarbeit?.abgabetermine">
					<AccordionTab :headerClass="termin.dateStyle + '-header'">
						<template #header>
							<div class="d-flex row w-100 flex-nowrap">
								<div class="col-auto" style="transform: translateX(-62px)">
									<i v-if="termin.dateStyle == 'verspaetet'" v-tooltip.right="getTooltipVerspaetet" class="fa-solid fa-triangle-exclamation"></i>
									<i v-else-if="termin.dateStyle == 'verpasst'" v-tooltip.right="getTooltipVerpasst" class="fa-solid fa-calendar-xmark"></i>
									<i v-else-if="termin.dateStyle == 'abzugeben'" v-tooltip.right="getTooltipAbzugeben" class="fa-solid fa-hourglass-half"></i>
									<i v-else-if="termin.dateStyle == 'standard'" v-tooltip.right="getTooltipStandard" class="fa-solid fa-clock"></i>
									<i v-else-if="termin.dateStyle == 'abgegeben'" v-tooltip.right="getTooltipAbgegeben" class="fa-solid fa-check"></i>
								</div>
								<div class="col-auto text-start pl-2 pr-0 pt-0 pb-0" style="min-width: max(120px, 20%); max-width: min(300px, 30%); transform: translateX(-30px)">
									<span>{{ termin?.bezeichnung }}</span>
								</div>
								<div class="col-auto text-start p-0" style="min-width: max(80px, 15%); transform: translateX(-30px)">
									<span>{{ formatDate(termin.datum, false) }}</span>
								</div>
								<div class="col-auto" style="transform: translateX(-30px); min-width: 42px;">
									<i v-if="termin?.fixtermin" v-tooltip.right="getTooltipFixtermin" class="fa-solid fa-lock"></i>
									<i v-if="termin?.abgabedatum && isMobile" v-tooltip.right="getTooltipAbgabeDetected" class="fa-solid fa-file"></i>
								</div>
								<div v-if="termin?.abgabedatum && !isMobile" class="col-auto" style="transform: translateX(-30px); min-width: 42px;">
									<i  v-tooltip.right="getTooltipAbgabeDetected" class="fa-solid fa-file"></i>
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
											<div class="col-12 col-md-3 fw-bold">{{ $capitalize($p.t('abgabetool/c4abgabeZeitstatus')) }}</div>
											<div class="col-12 col-md-9">{{$p.t('abgabetool/c4tooltip' + $capitalize(termin?.dateStyle) )}}</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-3 fw-bold">{{ $capitalize($p.t('abgabetool/c4fixterminv4')) }}</div>
											<div class="col-12 col-md-9">{{!termin?.fixtermin}}</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-3 fw-bold">{{ $capitalize($p.t('abgabetool/c4fileUploaded')) }}</div>
											<div class="col-12 col-md-9">{{termin?.abgabedatum !== null}}</div>
										</div>
									</div>
								</template>
							</Inplace>

						
						</div>
						
						<div class="row mt-2">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4zieldatum') )}}</div>
							<div class="col-12 col-md-9">
								<VueDatePicker
									v-model="termin.datum"
									:clearable="false"
									:disabled="true"
									:enable-time-picker="false"
									:format="formatDate"
									:text-input="true"
									auto-apply>
								</VueDatePicker>
							</div>
						</div>
						
						<div class="row mt-2">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4abgabetypv2') )}}</div>
							<div class="col-12 col-md-9">
								{{ termin.bezeichnung }}
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.note">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4note') )}}</div>
							<div class="col-12 col-md-9">
								<div class="col-1 d-flex justify-content-start align-items-start">
									{{ getTerminNoteBezeichnung(termin) }}
								</div>
							</div>
						</div>
						
						<div class="row mt-2" v-if="termin.paabgabetyp_kurzbz === 'qualgate1' || termin.paabgabetyp_kurzbz === 'qualgate2'">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4notizQualGatev2') )}}</div>
							<div class="col-12 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.beurteilungsnotiz" rows="1" class="w-100" disabled></Textarea>
							</div>
						</div>
						
						<div v-if="termin.kurzbz && termin.kurzbz.length > 0" class="row mt-2">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4abgabekurzbz') )}}</div>
							<div class="col-12 col-md-9">
								<Textarea style="margin-bottom: 4px;" v-model="termin.kurzbz" rows="1" class="w-100" :disabled="true"></Textarea>
							</div>
						</div>
						
						<div class="row mt-2">
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4abgabedatum') )}}</div>
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
										<div v-if="termin?.signatur !== undefined" class="col-auto">
											<Message v-if="termin?.signatur == true" severity="success" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4signaturGefunden') }} </Message>
											<Message v-else-if="termin?.signatur == false" severity="error" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4keineSignatur') }} </Message>
											<Message v-else-if="termin?.signatur == null" severity="warn" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4signaturServerError') }} </Message>
										</div>
										<div v-else class="col-auto">
											<Message severity="info" :closable="false" :pt="getMessagePtStyle"> {{ $p.t('abgabetool/c4noFileFound') }} </Message>
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
							<div class="col-12 col-md-3 fw-bold">{{$capitalize( $p.t('abgabetool/c4fileupload') )}}</div>
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
		 </div>
	 	
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
				
<!--				 lektor fills these out-->
<!--				<div class="row mb-3 align-items-start">-->
<!--					<div class="row">Kontrollierte Schlagwörter</div>-->
<!--					<div class="row">-->
<!--						<Textarea v-model="form.kontrollschlagwoerter"></Textarea>-->
<!--					</div>-->
<!--					-->
<!--				-->
<!--				</div>-->
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
				<button class="btn btn-primary" :disabled="getAllowedToSendEndupload" @click="triggerEndupload">{{$capitalize( $p.t('ui/hochladen') )}}</button>
			</template>
		</bs-modal>
	 	
    `,
};

export default AbgabeStudentDetail;
