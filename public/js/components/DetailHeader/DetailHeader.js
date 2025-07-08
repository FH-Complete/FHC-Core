import ApiDetailHeader from "../../api/factory/detailHeader.js";

export default {
	name: 'DetailHeader',
	inject: {
		domain: {
			from: 'configDomain',
			default: 'technikum-wien.at'
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
			this.getHeader(this.person_id);

			this.loadDepartmentData(this.mitarbeiter_uid)
				.then(() => {
					// Call getLeitungOrg only after departmentData is loaded
					this.getLeitungOrg(this.departmentData.oe_kurzbz);
				})
				.catch((error) => {
					console.error("Error loading department data:", error);
				});
		}
	},
	watch: {
		person_id: {
			handler(newVal) {
				if (newVal) {
					this.getHeader(this.person_id);
					this.loadDepartmentData(this.person_id).
					then(() => {
						this.getLeitungOrg(this.departmentData.oe_kurzbz);
					});
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
		};
	},
	methods: {
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
				person_id: this.leitungData.person_id});
		}
	},
	template: `
		<div class="core-header d-flex justify-content-start align-items-center w-100 overflow-auto pb-3 gap-3" style="max-height:9rem; min-width: 37.5rem;">

			<template v-if="typeHeader==='student'">
			{{headerData}}
					<div
						v-for="person in headerData"
						:key="person.person_id"
						class="d-flex flex-column align-items-center h-100"
						class="position-relative d-inline-block"
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
					<small class="text-muted">{{person.uid}}</small>
					</div>

					<div v-if="headerData.length == 1">
						<h2 class="h4">
							{{headerData[0].titelpre}}
							{{headerData[0].vorname}}
							{{headerData[0].nachname}},
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
						<strong class="text-muted"> | Status </strong>
						 {{headerData[0].status}}
						<strong class="text-muted"> | {{$p.t('person', 'matrikelnummer')}} </strong>
						  {{headerData[0].matr_nr}}
					  </h5>

					</div>
				</div>
		</template>

		<template v-if="typeHeader==='mitarbeiter'">
			<div class="row">
				<div class="col-md-2 d-flex justify-content-start align-items-center w-30 pb-3 gap-3 position-relative"
						style="max-height: 8rem; max-width: 6rem; overflow: hidden;">
					<img
					  class="d-block h-100 rounded"
					  alt="Profilbild"
					  :src="'data:image/jpeg;base64,' + headerDataMa.foto"
					/>
					<template v-if="headerDataMa.foto_sperre">
						<i
						  class=" fa fa-lock text-secondary bg-light rounded d-flex justify-content-center align-items-center position-absolute top-0 end-0"
						  style="z-index: 1; font-size: 1rem; width: 1.25rem; height: 1.25rem;"
						></i>
					</template>
				</div>

				<!--show Ma-Details-->
				<div class="col-md-10">
					<h5>{{headerDataMa.titelpre}} {{headerDataMa.vorname}} {{headerDataMa.nachname}}<span v-if="headerDataMa?.titelpost">, </span> {{headerDataMa.titelpost}}
					</h5>
					<strong class="text-muted">{{departmentData.organisationseinheittyp_kurzbz}}</strong>
						{{departmentData.bezeichnung}}
					<span v-if="leitungData.uid"> | </span>
					<strong v-if="leitungData.uid" class="text-muted">Vorgesetzte*r </strong>
					<a href="#" @click.prevent="redirectToLeitung">
						{{leitungData.titelpre}} {{leitungData.vorname}} {{leitungData.nachname}}
					</a>
					<p>
						<strong class="text-muted">Email </strong>
						 <span v-if="!headerDataMa?.alias">
							<a :href="'mailto:'+headerDataMa?.uid+'@'+domain">{{headerDataMa.uid}}@{{domain}}</a>
						</span>
						<span v-if="headerDataMa?.alias">
							<a :href="'mailto:'+headerDataMa?.alias+'@'+domain">{{headerDataMa.alias}}@{{domain}}</a>
						</span>
						<span v-if="headerDataMa?.telefonklappe" class="mb-2"> | <strong class="text-muted">DW </strong>{{headerDataMa?.telefonklappe}}</span>
					</p>
				</div>
			</div>
		</template>
	</div>
	`
}
