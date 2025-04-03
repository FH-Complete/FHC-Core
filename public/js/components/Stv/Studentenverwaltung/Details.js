import FhcTabs from "../../Tabs.js";

// TODO(chris): alt & title
// TODO(chris): phrasen

export default {
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
		this.$fhcApi
			.factory.stv.configStudent()
			.then(result => {
				this.configStudent = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.factory.stv.configStudents()
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
					<h2 class="h4">{{students[0].titlepre}} {{students[0].vorname}} {{students[0].nachname}} {{students[0].titlepost}}</h2>
				</div>
			</div>
			<fhc-tabs v-if="students.length == 1" ref="tabs" :modelValue="students[0]" :config="config" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
			<fhc-tabs v-else ref="tabs" :modelValue="students" :config="config" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
		</div>
		<div v-else>
			Loading...
		</div>
	</div>`
};