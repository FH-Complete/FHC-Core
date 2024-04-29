import FhcTabs from "../../Tabs.js";

// TODO(chris): alt & title
// TODO(chris): phrasen

export default {
	components: {
		FhcTabs
	},
	props: {
		students: Array
	},
	computed: {
		appRoot() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root;
		}
	},
	methods: {
		reload() {
			if (this.$refs.tabs?.$refs?.current?.reload)
				this.$refs.tabs.$refs.current.reload();
		}
	},
	template: `
	<div class="stv-details h-100 pb-3 d-flex flex-column">
		<div v-if="!students?.length" class="justify-content-center d-flex h-100 align-items-center">
			Bitte StudentIn auswählen!
		</div>
		<div v-else class="d-flex flex-column h-100 pb-3">
			<div class="d-flex justify-content-start align-items-center w-100 pb-3 gap-3" style="max-height:8rem">
				<img v-for="student in students" :key="student.person_id" class="d-block h-100 rounded" alt="profilbild" :src="appRoot + 'cis/public/bild.php?src=person&person_id=' + student.person_id">
				<div v-if="students.length == 1">
					<h2 class="h4">{{students[0].titlepre}} {{students[0].vorname}} {{students[0].nachname}} {{students[0].titlepost}}</h2>
				</div>
			</div>
			<fhc-tabs v-if="students.length == 1" ref="tabs" :modelValue="students[0]" :config="$fhcApi.factory.stv.configStudent()" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
			<fhc-tabs v-else ref="tabs" :modelValue="students" :config="$fhcApi.factory.stv.configStudents()" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
		</div>
	</div>`
};