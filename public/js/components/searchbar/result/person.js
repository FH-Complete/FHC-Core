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
		class="searchbar-result-student"
		:res="res"
		:actions="actions"
		:title="res.name"
		:image="res.photo_url"
		image-fallback="fas fa-user fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('person/person_id') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.person_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('search/result_emails') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					<a v-for="email in emails" :key="email" :href="'mailto:' + email" class="d-block">
						{{ email }}
					</a>
				</div>
			</div>
		</div>
	</template-frame>`
};