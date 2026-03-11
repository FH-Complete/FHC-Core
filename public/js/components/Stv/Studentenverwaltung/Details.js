import FhcTabs from "../../Tabs.js";
import FhcHeader from "../../DetailHeader/DetailHeader.js";

import ApiStvApp from '../../../api/factory/stv/app.js';
import ApiStudent from '../../../api/factory/stv/students.js';

// TODO(chris): alt & title
// TODO(chris): phrasen

export default {
	name: "DetailsPrestudent",
	inject: {
		currentSemester: {
			from: 'currentSemester',
		},
	},
	components: {
		FhcTabs,
		FhcHeader
	},
	data() {
		return {
			configStudent: {},
			configStudents: {},
			localStudent: null
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
					return Object.fromEntries(Object.entries(this.configStudent).filter(([ , value ]) => !value.showOnlyWithoutUid));
				return Object.fromEntries(Object.entries(this.configStudent).filter(([ , value ]) => !value.showOnlyWithUid));
			} else if (this.students.every(student => student.uid)) {
				return Object.fromEntries(Object.entries(this.configStudents).filter(([ , value ]) => !value.showOnlyWithoutUid));
			} else if (this.students.every(student => !student.uid)) {
					return Object.fromEntries(Object.entries(this.configStudents).filter(([ , value ]) => !value.showOnlyWithUid));
			}
			return Object.fromEntries(Object.entries(this.configStudents).filter(([ , value ]) => !value.showOnlyWithUid && !value.showOnlyWithUid));
		},
		//for reloading component if data changes
		headerKey() {
			return this.students?.[0]?.uid || this.localStudent?.[0]?.uid ||  "empty";
		}
	},
	watch: {
		'$p.user_language.value'(n, o) {
			if (n !== o && o !== undefined)
				this.loadConfig();
		},
		//ohne zusätzlichen Watcher reload header und details
		currentSemester(newVal) {
			if (
				this.students &&
				this.students.length > 0 &&
				newVal !== this.students[0].query_studiensemester_kurzbz
			) {
				console.log("studiensemester_kurzbz "  + this.students[0].query_studiensemester_kurzbz + " vs " + newVal);
				this.reloadDataStudent();
			}
		},
		headerKey(newVal){
			this.reloadDataStudent();
		}

	},
	methods: {
		loadConfig() {
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
		reload() {
			if (this.$refs.tabs?.$refs?.current?.reload)
				this.$refs.tabs.$refs.current.reload();
		},
		reloadDataStudent(){
			//TODO(check)
			this.localStudent = null;
			const studentArr = this.students;

			if (!studentArr || !studentArr.length) {
				console.log("no data");
				return;
			}

			console.log("uid " + studentArr[0].uid);

			this.$api
				.call(ApiStudent.uid(studentArr[0].uid, this.currentSemester))
				.then(result => {
					this.localStudent = result.data;
				});
		}
	},
	created() {
		this.loadConfig();
	},
	/*	//TODO(remove)
	* 		{{headerKey}} {{localStudent?.[0]?.uid}} <br> {{ students?.[0]?.uid }}
	* */
	template: `
	<div class="stv-details h-100 d-flex flex-column">
		<div v-if="!students?.length" class="justify-content-center d-flex h-100 align-items-center">
			{{$p.t('ui', 'chooseStudent')}}
		</div>
		<div v-else-if="configStudent && configStudents" class="d-flex flex-column h-100">
			<fhc-header
				:key="headerKey"
				:headerData="localStudent || students"
				typeHeader="student"
			>
			</fhc-header>
			<fhc-tabs
				v-if="students.length == 1"
				ref="tabs" 
				:useprimevue="true"
				:modelValue="localStudent[0] || students[0]"
				:config="config"
				:default="$route.params.tab"
				style="flex: 1 1 0%; height: 0%"
				@changed="reload"
				>
				</fhc-tabs>
			<fhc-tabs v-else ref="tabs" :useprimevue="true" :modelValue="students" :config="config" :default="$route.params.tab" style="flex: 1 1 0%; height: 0%" @changed="reload"></fhc-tabs>
		</div>
		<div v-else>
			Loading...
		</div>
	</div>`
};
