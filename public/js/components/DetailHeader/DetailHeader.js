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
		<div class="core-header d-flex justify-content-start align-items-center w-100 pb-3 gap-3" style="max-height:8rem">

			<div
				v-for="person in headerData"
				:key="person.person_id"
				class="d-flex flex-column align-items-center h-100"
			>
				<img
				  class="d-block h-100 rounded"
				  :alt="'Profilbild ' + person.uid"
				  :src="appRoot + 'cis/public/bild.php?src=person&person_id=' + person.person_id"
				/>
				<small>{{person.uid}}</small>
			</div>

			<div v-if="headerData.length == 1">
				<h2 class="h4">
					{{headerData[0].titelpre}}
					{{headerData[0].vorname}}
					{{headerData[0].nachname}}
					{{headerData[0].titelpost}}
				</h2>

				<h5 v-if="typeHeader==='student'" class="h6">
					<strong class="text-muted">Studiengang </strong>
					 {{headerData[0].studiengang}}
					<strong v-if="headerData[0].semester" class="text-muted"> | Semester </strong>
					  {{headerData[0].semester}}
					<strong v-if="headerData[0].verband" class="text-muted"> | Verband </strong>
					{{headerData[0].verband}}
					<strong v-if="headerData[0].gruppe" class="text-muted"> | Gruppe </strong>
					{{headerData[0].gruppe}}
				 </h5>
				<h5 v-if="typeHeader==='mitarbeiter'" class="h6">
				<strong class="text-muted">Team </strong>
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
				<strong class="text-muted"> | MatrNr </strong>
				  {{headerData[0].matr_nr}}
				<strong class="text-muted"> | UID </strong>
				{{headerData[0].uid}}
				<strong class="text-muted"> | Person ID </strong>
				{{headerData[0].person_id}}
			  </h5>
			  <h5 v-if="typeHeader==='mitarbeiter'" class="h6">
				<strong class="text-muted">Email </strong>
				<span>
					<a :href="'mailto:'+headerData[0]?.mail_intern">{{headerData[0].mail_intern}}</a>
				</span>
				<strong class="text-muted"> | Durchwahl </strong>
				 {{headerData[0].status}}
			  </h5>

			</div>

		</div>

	`
}