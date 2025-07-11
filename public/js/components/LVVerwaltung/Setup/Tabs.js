import FhcTabs from "../../Tabs.js";
import Setup from "../../../api/lehrveranstaltung/setup.js";

export default {
	name: "LVVerwaltungTabs",
	components: {
		FhcTabs
	},
	data() {
		return {
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

			return this.configLVTabs;
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
		this.$api.call(Setup.getTabs())
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
			<div v-else-if="configLVTabs" class="d-flex flex-column h-100 pb-3">
				<fhc-tabs 
					v-if="lv.length === 1"
					ref="tabs"
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