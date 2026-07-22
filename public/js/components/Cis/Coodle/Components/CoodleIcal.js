import CoodleApi from "../../../../api/factory/coodle.js";

export default {
	name: "CoodleIcal",
	props: {
		authUid: String | null,
	},
	data() {
		return {
			icalLink: "",
			encryptedIcalLink: "",
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
		link() {
			return this.icalLink;
		},
	},
	methods: {
		async getIcalLink() {
			const icalLinkResponse = await this.$api.call(CoodleApi.getCoodleIcalLink());
			if (icalLinkResponse.meta.status === "success") {
				this.icalLink = icalLinkResponse.data.unencryptedUrl;
				this.encryptedIcalLink = icalLinkResponse.data.encryptedUrl;
				console.log(icalLinkResponse);
				console.log(this.icalLink);
				console.log(this.encryptedIcalLink);
			}
		},
	},
	async created() {
		await this.getIcalLink();
		console.log(this.icalLink);
				console.log(this.encryptedIcalLink);
	},
	template: /*html*/ `
	<div class="card" style="min-height:100%">
		<div class="card-body">
			<div class="d-flex flex-column gap-3">
				<span>
					{{ "You can integrate your selections from active Coodle surveys into your preferred calendar application. To do so, import the .ics file from one of the following links." }}
				</span>
				<span>
					{{ "To exclude identifiable survey data: " }}
					<a v-if="icalLink?.length" :href="icalLink" target="_blank">{{ icalLink }}</a>
				</span>
				<span>
					{{ "To include identifiable survey data: " }}
					<a v-if="encryptedIcalLink?.length" :href="encryptedIcalLink" target="_blank">{{ encryptedIcalLink }}</a>
				</span>
								
			</div>
		</div>
	</div>
	`,
};
