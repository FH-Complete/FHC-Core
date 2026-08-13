import FhcTabs from "../../Tabs.js";
import Setup from "../../../api/lehrveranstaltung/setup.js";

export default {
	name: "LVVerwaltungTabs",
	components: {
		FhcTabs
	},
	data() {
		return {
			configLETabs: {},
			configLVTabs: {},
		};
	},
	props: {
		lv: Object
	},
	computed: {
		config() {
			if (!this.lv || !this.lv.length)
				return {};

			return this.configLETabs;
		}
	},
	methods: {
		reload() {
			if (this.$refs.tabs?.$refs?.current?.reload)
			{
				this.$refs.tabs.$refs.current.reload();
			}
		}
	},
	created() {
		this.$api.call(Setup.getLETabs())
			.then(result => {
				this.configLETabs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(Setup.getLVTabs())
			.then(result => {
				this.configLVTabs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-details h-100 pb-3 d-flex flex-column">
			<div v-if="!lv?.length" class="justify-content-center d-flex h-100 align-items-center">
				Bitte eine Lehreinheit auswählen!
			</div>
			<div v-else-if="configLETabs && configLVTabs" class="d-flex flex-column h-100 pb-3">
				<fhc-tabs 
					v-if="lv.length === 1 && lv[0]?.lehreinheit_id"
					ref="tabs"
					:useprimevue="true"
					:modelValue="lv[0]"
					:config="configLETabs"
					:default="$route.params.tab"
					style="flex: 1 1 0%; height: 0%"
					@changed="reload"
				/>
				<fhc-tabs 
					v-else-if="lv.length === 1"
					ref="tabs"
					:useprimevue="true"
					:modelValue="lv[0]"
					:config="configLVTabs"
					:default="$route.params.tab"
					style="flex: 1 1 0%; height: 0%"
					@changed="reload"
				/>
			</div>
			<div v-else>
				Loading...
			</div>
		</div>
	`
};