// NOTE(chris): cache calls globally to prevent multiple calls on the same source
const calledPermissionUrls = {};
async function callPermissionUrl($api, url) {
	if (!calledPermissionUrls[url]) {
		calledPermissionUrls[url] = $api.get(url);
	}
	return (await calledPermissionUrls[url]).data;
}

export default {
	name: 'ZeugnisDocuments',
	components: {
		PvTieredMenu: primevue.tieredmenu
	},
	props: {
		list: Array,
		data: Object
	},
	data() {
		return {
			filteredList: []
		};
	},
	watch: {
		async data() {
			this.filteredList = await this.copyListPart(this.list);
		}
	},
	methods: {
		addParamsToString(link) {
			for (var k in this.data)
				link = link.replace("{" + k + "}", this.data[k]);
			return link;
		},
		async copyListPart(list) {
			let result = [], res;

			for (const part of list) {
				if (part.permissioncheck) {
					if (!this.data)
						continue;
					let permissioncheckUrl = part.permissioncheck;
					for (const k in this.data) {
						permissioncheckUrl = permissioncheckUrl.replace("{" + k + "}", this.data[k]);
					}
					if (!await callPermissionUrl(this.$api, permissioncheckUrl))
						continue;
				}
				const item = {label: part.title};
				if (part.children)
					item.items = await this.copyListPart(part.children);
				if (!item.items || !item.items.length) {
					if (part.action && part.action.url) {
						item.command = () => {
							const post = {};
							if (part.action.post)
								Object.entries(part.action.post)
									.forEach(
										([key, value]) =>
											post[key] = value[0] == '{' 
												? this.data[value.slice(1,-1)] 
												: value
									);
							this.$api
								.post(this.addParamsToString(part.action.url), post)
								.then(() => part.action.response || this.$p.t('ui/successSave'))
								.then(this.$fhcAlert.alertSuccess)
								.catch(this.$fhcAlert.handleSystemError);
						};
					} else if(part.link) {
						item.url = this.addParamsToString(part.link);
						item.target = '_blank';
					}
				}
				result.push(item);
			}

			return result;
		}
	},
	async created() {
		this.filteredList = await this.copyListPart(this.list);
	},
	template: `
	<div class="stv-details-noten-zeugnis-documents d-inline-flex gap-2">
		<template v-for="(item, i) in filteredList" :key="i">
			<button
				type="button"
				@click="evt => $refs.menu[i].toggle(evt)"
				aria-haspopup="true"
				class="btn btn-outline-secondary dropdown-toggle"
				v-html="item.label"
				v-bind="$attrs"
				/>
			<pv-tiered-menu ref="menu" :model="item.items" popup />
		</template>
	</div>`
};