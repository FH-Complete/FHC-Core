import FhcTabs from "../../Tabs.js";

import ApiStvApp from '../../../api/factory/stv/app.js';

// TODO(chris): alt & title
// TODO(chris): phrasen

export default {
	name: "DetailsPrestudent",
	components: {
		FhcTabs
	},
	data() {
		return {
			configStudent: {},
			configStudents: {}
		};
	},
	props: {
		students: Array
	},
	computed: {
		appRoot() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root;
		},
		config() {
			if (!this.students.length)
				return {};
			if (this.students.length == 1) {
				const student = this.students[0];
				if (student.uid)
					return Object.fromEntries(Object.entries(this.configStudent).filter(([key, value]) => !value.showOnlyWithoutUid));
				return Object.fromEntries(Object.entries(this.configStudent).filter(([key, value]) => !value.showOnlyWithUid));
			}
			return this.configStudents;
		}
	},
	methods: {
		reload() {
			if (this.$refs.tabs?.$refs?.current?.reload)
				this.$refs.tabs.$refs.current.reload();
		}
	},
	created() {
		this.$api
			.call(ApiStvApp.configStudent())
			.then(result => {
				this.configStudent = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvApp.configStudents())
			.then(result => {
				this.configStudents = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details h-100 pb-3 d-flex flex-column">
		<div v-if="!students?.length" class="justify-content-center d-flex h-100 align-items-center">
			Bitte StudentIn auswählen!
		</div>
		<div v-else-if="configStudent && configStudents" class="d-flex flex-column h-100 pb-3">
			<div class="d-flex justify-content-start align-items-center w-100 pb-3 gap-3" style="max-height:8rem">
				<img v-for="student in students" :key="student.person_id" class="d-block h-100 rounded" alt="profilbild" :src="appRoot + 'cis/public/bild.php?src=person&person_id=' + student.person_id">
	

					
				<div v-if="students.length == 1">
					<h2 class="h4">
						{{students[0].titelpre}}
						{{students[0].vorname}}
						{{students[0].nachname}}
						{{students[0].titelpost}}
					</h2>
					<h5 class="h6">
						<strong class="text-muted">Studiengang </strong>
						 {{students[0].studiengang}}
						<strong v-if="students[0].semester" class="text-muted"> | Semester </strong>
						  {{students[0].semester}}
						<strong v-if="students[0].verband" class="text-muted"> | Verband </strong>
						{{students[0].verband}}
						<strong v-if="students[0].gruppe" class="text-muted"> | Gruppe </strong>
						{{students[0].gruppe}}
					  </h5>
					  <h5 class="h6">
						<strong class="text-muted">Email </strong>
						<span>
							<a :href="'mailto:'+students[0]?.mail_intern">{{students[0].mail_intern}}</a>
						</span>
						<strong class="text-muted"> | Status </strong>
						 {{students[0].status}}
						<strong class="text-muted"> | MatrNr </strong>
						  {{students[0].matr_nr}}
						<strong class="text-muted"> | UID </strong>
						{{students[0].uid}}
						<strong class="text-muted"> | Person ID </strong>
						{{students[0].person_id}}
					  </h5>

				</div>
			</div>
			<fhc-tabs
				v-if="students.length == 1"
				ref="tabs"
				:modelValue="students[0]"
				:config="config"
				:default="$route.params.tab"
				style="flex: 1 1 0%; height: 0%"
				@changed="reload"
				>
				</fhc-tabs>
			<fhc-tabs v-else ref="tabs" :modelValue="students" :config="config" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
		</div>
		<div v-else>
			Loading...
		</div>
	</div>`
};