import TemplateFrame from "./template/frame.js";

export default {
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	computed: {
		foto() {
			if (this.res.foto)
				return 'data:image/jpeg;base64,' + this.res.foto;
			return null;
		}
	},
	template: `
	<template-frame
		class="searchbar-result-organisationunit"
		:res="res"
		:actions="actions"
		:title="res.name"
		image-fallback="fas fa-sitemap fa-4x p-4 text-white bg-primary"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">übergeordnete OrgEinheit</div>
				<div class="searchbar_tablecell">
					{{ res.parentoe_name }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Gruppen-EMail</div>
				<div class="searchbar_tablecell">
					<a :href="'mailto:' + res.mailgroup">
						{{ res.mailgroup }}
					</a>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Leiter</div>
				<div class="searchbar_tablecell">
					<ul class="searchbar_inline_ul" v-if="res.leaders.length > 0">
						<li v-for="(leader, idx) in res.leaders" :key="idx">{{ leader.name }}</li>
					</ul>
					<span v-else="">N.N.</span>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Mitarbeiter-Anzahl</div>
				<div class="searchbar_tablecell">
					{{ res.number_of_people }}
				</div>
			</div>
		</div>
	</template-frame>`
};