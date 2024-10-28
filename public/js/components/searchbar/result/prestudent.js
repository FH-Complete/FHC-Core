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
		emails() {
			return new Set(this.res.email);
		}
	},
	template: `
	<template-frame
		class="searchbar-result-prestudent"
		:res="res"
		:actions="actions"
		:title="res.name + ' (' + res.status + ' ' + res.stg_kuerzel + ')'"
		:image="res.photo_url"
		image-fallback="fas fa-user-circle fa-7x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Person ID</div>
				<div class="searchbar_tablecell">
					{{ res.person_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">EMails</div>
				<div class="searchbar_tablecell">
					<a v-for="email in emails" :key="email" :href="'mailto:' + email" class="d-block">
						{{ email }}
					</a>
				</div>
			</div>
			<div v-if="res.uid" class="searchbar_tablerow">
				<div class="searchbar_tablecell">Student UID</div>
				<div class="searchbar_tablecell">
					{{ res.uid }}
				</div>
			</div>
			<div v-if="res.matrikelnr" class="searchbar_tablerow">
				<div class="searchbar_tablecell">Matrikelnummer</div>
				<div class="searchbar_tablecell">
					{{ res.matrikelnr }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Prestudent ID</div>
				<div class="searchbar_tablecell">
					{{ res.prestudent_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Studiengang</div>
				<div class="searchbar_tablecell">
					{{ res.bezeichnung }}
				</div>
			</div>
		</div>
	</template-frame>`
};