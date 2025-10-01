import FhcTabs from "../../Tabs.js";
import FhcHeader from "../../DetailHeader/DetailHeader.js";

import ApiStvApp from '../../../api/factory/stv/app.js';

// TODO(chris): alt & title
// TODO(chris): phrasen

export default {
	name: "DetailsPrestudent",
	components: {
		FhcTabs,
		FhcHeader
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
	<div class="stv-details h-100 d-flex flex-column">
		<div v-if="!students?.length" class="justify-content-center d-flex h-100 align-items-center">
			{{$p.t('ui', 'chooseStudent')}}
		</div>
		<div v-else-if="configStudent && configStudents" class="d-flex flex-column h-100">
			<fhc-header
				:headerData="students"
				typeHeader="student"
			>
			</fhc-header>
			<fhc-tabs
				v-if="students.length == 1"
				ref="tabs" 
				:useprimevue="true"
				:modelValue="students[0]"
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
