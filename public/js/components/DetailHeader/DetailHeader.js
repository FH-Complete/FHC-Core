import ApiDetailHeader from "../../api/factory/detailHeader.js";
import ApiHandleFoto from "../../api/factory/fotoHandling.js";
import ModalUploadFoto from "./Modal/UploadFoto.js";

export default {
	name: 'DetailHeader',
	components: {
		ModalUploadFoto
	},
	inject: {
		domain: {
			from: 'configDomain',
			default: 'fhcomplete.info'
		},
	},
	props: {
		headerData: {
			type: Object,
			required: false
		},
		person_id: {
			type: Number,
			required: false
		},
		mitarbeiter_uid: {
			type: String,
			required: false
		},
		fotoEditable: {
			type: Boolean,
			required: false,
			default: false
		},
		typeHeader: {
			type: String,
			default: 'student',
			validator(value) {
				return [
					'student',
					'mitarbeiter',
				].includes(value)
			}
		}
	},
	computed: {
		appRoot() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root;
		},
		validatedHeaderData() {
			if (this.typeHeader === 'student') return this.headerData;
			if (this.typeHeader === 'mitarbeiter') return this.person_id;
			return null;
		}
	},
	created(){
		if(this.person_id) {
			this.loadHeaderData();
		}
	},
	watch: {
		person_id: {
			handler(newVal) {
				if (newVal) {
					this.loadHeaderData();
				}
			},
			deep: true,
		},
	},
	data(){
		return{
			headerDataMa: {},
			departmentData: {},
			leitungData: {},
			isFetchingIssues: false
		};
	},
	methods: {
		loadHeaderData(){
			this.getHeader(this.person_id);
			this.loadDepartmentData(this.mitarbeiter_uid)
				.then(() => {
					// Call getLeitungOrg only after departmentData is loaded
					this.getLeitungOrg(this.departmentData.oe_kurzbz);
				})
				.catch((error) => {
					console.error("Error loading header data: ", error);
				});
		},
		getHeader(person_id) {
			return this.$api
				.call(ApiDetailHeader.getHeader(person_id))
				.then(result => {
					this.headerDataMa = result.data;

				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadDepartmentData(mitarbeiter_uid) {
			return this.$api
				.call(ApiDetailHeader.getPersonAbteilung(mitarbeiter_uid))
				.then(result => {
					this.departmentData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getLeitungOrg(oekurzbz){
			return this.$api
				.call(ApiDetailHeader.getLeitungOrg(oekurzbz))
				.then(result => {
					this.leitungData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		redirectToLeitung(){
			this.$emit('redirectToLeitung', {
				person_id: this.leitungData.person_id,
				uid: this.leitungData.uid
			});
		},
		showModal(person_id){
			this.$refs.modalFoto.open(person_id);
		},
		showDeleteModal(person_id){
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? person_id
					: Promise.reject({handled: true}))
				.then(this.deleteFoto)
				.catch(this.$fhcAlert.handleSystemError);
			},
		deleteFoto(person_id){
			return this.$api
				.call(ApiHandleFoto.deleteFoto(person_id))
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				});
		},
		reload() {
			if(this.person_id) {
				this.loadHeaderData();
			}
			else {
				this.$emit('reload');
			}
		},
	},
	template: `
		<div class="core-header d-flex justify-content-start align-items-center w-100 overflow-auto pb-3 gap-3" style="max-height:9rem; min-width: 37.5rem;">

			<modal-upload-foto
				v-if="person_id"
				ref="modalFoto"
				:person_id="person_id"
				@reload="reload"
			>
			</modal-upload-foto>
			<modal-upload-foto
				v-else
				ref="modalFoto"
				:person_id="headerData[0].person_id"
				@reload="reload"
			>
			</modal-upload-foto>

			<template v-if="typeHeader==='student'">

				<div
					v-for="person in headerData"
					:key="person.person_id"
					class="foto-container d-flex flex-column align-items-center h-100 position-relative d-inline-block"
				>
					<img
					  class="d-block h-100 rounded"
					  alt="Profilbild"
					  :src="'data:image/jpeg;base64,' + person.foto"
					/>

					<template v-if="person.foto_sperre">
						<i
						  class=" fa fa-lock text-secondary bg-light rounded d-flex justify-content-center align-items-center position-absolute top-0 end-0"
						  style="z-index: 1; font-size: 1rem; width: 1.25rem; height: 1.25rem;"
						></i>
					</template>
					<template v-if="fotoEditable">
						<button
							type="button"
							class="fotoedit btn btn-outline-dark btn-sm d-flex justify-content-center align-items-center position-absolute start-0"
							style="z-index: 4; font-size: 1rem; width: 1.5rem; height: 1.5rem; opacity:0; transition: opacity 0.2s; top:20%; left:2rem;"
							@click="showDeleteModal(headerData[0].person_id)">
							<i class="fa fa-xmark"></i>
						</button>
						<button
							type="button"
							class="fotoedit btn btn-outline-dark btn-sm d-flex justify-content-center align-items-center position-absolute end-0"
							style="z-index: 4; font-size: 1rem; width: 1.5rem; height: 1.5rem; opacity:0; transition: opacity 0.2s; top:20%; right:2rem;"
							@click="showModal(headerData[0].person_id)">
							<i class="fa fa-pen"></i>
						</button>
					</template>
					<small class="text-muted">{{person.uid}}</small>
				</div>

					<div v-if="headerData.length == 1">
						<h2 class="h4">
							{{headerData[0].titelpre}}
							{{headerData[0].vorname}}
							{{headerData[0].nachname}}
							<span v-if="headerData[0].titelpost">, </span>
							{{headerData[0].titelpost}}
						</h2>

					<h5 class="h6">
					 <strong class="text-muted">Person ID </strong>
						{{headerData[0].person_id}}
						<strong class="text-muted">| {{$p.t('lehre', 'studiengang')}} </strong>
						 {{headerData[0].stg_bezeichnung}} ({{headerData[0].studiengang}})
						<strong v-if="headerData[0].semester" class="text-muted"> | {{$p.t('lehre', 'semester')}} </strong>
						  {{headerData[0].semester}}
						<strong v-if="headerData[0].verband" class="text-muted"> | {{$p.t('lehre', 'verband')}}</strong>
						{{headerData[0].verband}}
						<strong v-if="headerData[0].gruppe" class="text-muted"> | {{$p.t('lehre', 'gruppe')}} </strong>
						{{headerData[0].gruppe}}
					</h5>

					<h5 class="h6">
						<strong class="text-muted">Email </strong>
						<span>
							<a :href="'mailto:'+headerData[0]?.mail_intern">{{headerData[0].mail_intern}}</a>
						</span>
						<strong v-if="headerData[0].statusofsemester" class="text-muted"> | Status </strong>
						 {{headerData[0].statusofsemester}}
						<strong class="text-muted"> | {{$p.t('person', 'matrikelnummer')}} </strong>
						  {{headerData[0].matr_nr}}
					  </h5>

				</div>
		</template>

		<template v-if="typeHeader==='mitarbeiter'">
		
				<div class="foto-container col-md-2 d-flex justify-content-start align-items-center w-30 pb-3 gap-3 mt-3 position-relative" style="max-height: 8rem; max-width: 6rem; overflow: hidden;">
					<img
					  class="d-block w-100 h-100 rounded"
					  alt="Profilbild"
					  :src="'data:image/jpeg;base64,' + headerDataMa.foto"
					  style="object-fit: contain;"
					/>
					<template v-if="headerDataMa.foto_sperre">
						<i
						  class=" fa fa-lock text-secondary bg-light rounded d-flex justify-content-center align-items-center position-absolute top-0 end-0"
						  style="z-index: 1; font-size: 1rem; width: 1.25rem; height: 1.25rem;"
						></i>
					</template>
					<template v-if="fotoEditable">
						<button
							type="button"
							class="fotoedit btn btn-outline-dark btn-sm d-flex justify-content-center align-items-center position-absolute start-0"
							style="z-index: 104; font-size: 1rem; width: 2.5rem; height: 2.5rem; opacity:0; transition: opacity 0.2s; top:13%;"
							@click="showDeleteModal(person_id)">
							<i class="fa fa-xmark"></i>
						</button>
						<button
							type="button"
							class="fotoedit btn btn-outline-dark btn-sm d-flex justify-content-center align-items-center position-absolute end-0"
							style="z-index: 104; font-size: 1rem; width: 2.5rem; height: 2.5rem; opacity:0; transition: opacity 0.2s; top:13%;"
							@click="showModal(person_id)">
							<i class="fa fa-pen"></i>
						</button>
					</template>
				</div>

				<!--show Ma-Details-->
				<div class="col-md-9 text-nowrap mt-2">
					<h4>{{headerDataMa.titelpre}} {{headerDataMa.vorname}} {{headerDataMa.nachname}}<span v-if="headerDataMa?.titelpost">, </span> {{headerDataMa.titelpost}}</h4>
					<strong class="text-muted">{{departmentData.organisationseinheittyp_kurzbz}}</strong>
						{{departmentData.bezeichnung}}
					<span v-if="leitungData.uid"> | </span>
					<strong v-if="leitungData.uid" class="text-muted">Vorgesetzte*r </strong>
					<a href="#" @click.prevent="redirectToLeitung" >
						{{leitungData.titelpre}} {{leitungData.vorname}} {{leitungData.nachname}}
					</a>
					<p>
						<strong class="text-muted">Email </strong>
						 <span v-if="headerDataMa && (headerDataMa.alias === undefined || headerDataMa.alias === null || headerDataMa.alias === '')">
							<a :href="'mailto:' + mitarbeiter_uid + '@' + domain">
							  {{ mitarbeiter_uid }}@{{ domain }}
							</a>
						</span>
						<span v-else>
							<a :href="'mailto:'+headerDataMa?.alias+'@'+domain">{{headerDataMa.alias}}@{{domain}}</a>
						</span>
						<span v-if="headerDataMa?.telefonklappe" class="mb-2"> | <strong class="text-muted">DW </strong>{{headerDataMa?.telefonklappe}}</span>
					</p>
				</div>

				<div class="col-md-1 d-flex flex-column align-items-end justify-content-start ms-auto">
					<div class="d-flex py-1">
					<!--  TODO slots for Issue Tracking and Tags
						<class="px-2">
							<h4 class="mb-1">Issues<a class="refresh-issues" title="checkIssues Person" @click="checkPerson"><i class="fas fa-sync"></i></a></h4>
							<h6 v-if="!isFetchingIssues" class="text-muted">122</h6>
						</div> -->
						<div class="px-2">
							<h4 class="mb-1">PNr</h4>
							<h6 class="text-muted">{{ headerDataMa?.person_id }}</h6>
						</div>
						<div class="px-2" style="border-left: 1px solid #EEE">
							<h4 class="mb-1">UID</h4>
							<h6 class="text-muted">{{ mitarbeiter_uid }}</h6>
						</div>
					</div>
				</div>

		</template>
	</div>
	`
}
