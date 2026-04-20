import { replaceRelativeLegacyLink } from "../../../../helpers/LegacyLinkReplaceHelper.js";

export default {
	name: "iframe_content",
	props: {
		content: { type: String, required: true }
	},
	computed: {
		srcUrl() {
			const parser = new DOMParser()
			const doc = parser.parseFromString(`<div>${this.content}</div>`, "text/html");
			const iframe = doc.querySelector("iframe[src]");

			if (!iframe)
				return "";

			let url = iframe.getAttribute("src") || "";
			return replaceRelativeLegacyLink(url);
		}
	},
	template: `
		<div class="w-100">
			<iframe
				v-if="srcUrl"
				:src="srcUrl"
				style="width:100%; height:90vh; border:0; display:block;"
			></iframe>
		<div v-else class="alert alert-warning">Keine URL gefunden.</div>
		</div>
	`
};
