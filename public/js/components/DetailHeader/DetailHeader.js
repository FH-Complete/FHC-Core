export default {
	name: 'DetailHeader',
	props: {
		headerData: {
			type: Object,
			required: true
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
	},
	data(){
		return{};
	},
	methods: {
		//TODO(Manu) finish for Vertragsverwaltung
		getVorgesetzer(){},
	},
	template: `
		<div class="core-header d-flex justify-content-start align-items-center w-100 overflow-auto pb-3 gap-3" style="max-height:9rem; min-width: 37.5rem;">

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
					{{headerData[0].nachname}}
					{{headerData[0].titelpost}}
				</h2>

				<h5 v-if="typeHeader==='student'" class="h6">
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
				<h5 v-if="typeHeader==='mitarbeiter'" class="h6">
				<strong class="text-muted">Team / {{$p.t('lehre', 'kompetenzfeld')}}</strong>
				 {{headerData[0].studiengang}}
				<strong v-if="headerData[0].semester" class="text-muted"> | Vorgesetzte*r </strong>
				  {{headerData[0].semester}}
			  </h5>

			  <h5 v-if="typeHeader==='student'" class="h6">
				<strong class="text-muted">Email </strong>
				<span>
					<a :href="'mailto:'+headerData[0]?.mail_intern">{{headerData[0].mail_intern}}</a>
				</span>
				<strong class="text-muted"> | Status </strong>
				 {{headerData[0].status}}
				<strong class="text-muted"> | {{$p.t('person', 'matrikelnummer')}} </strong>
				  {{headerData[0].matr_nr}}
			  </h5>
			  <h5 v-if="typeHeader==='mitarbeiter'" class="h6">
				<strong class="text-muted">Email </strong>
				<span>
					<a :href="'mailto:'+headerData[0]?.mail_intern">{{headerData[0].mail_intern}}</a>
				</span>
				<strong class="text-muted"> | {{$p.t('kvp', 'op.label.phone')}} </strong>
				 {{headerData[0].status}}
			  </h5>

			</div>

		</div>

	`
}