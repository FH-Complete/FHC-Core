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
				<div class="searchbar_tablecell">{{ $p.t('search/result_student_uid') }}</div>
				<div class="searchbar_tablecell">
					{{ res.uid }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('person/person_id') }}</div>
				<div class="searchbar_tablecell">
					{{ res.person_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('person/matrikelnummer') }}</div>
				<div class="searchbar_tablecell">
					{{ res.matrikelnr }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_emails') }}</div>
				<div class="searchbar_tablecell">
					<a v-for="email in emails" :key="email" :href="'mailto:' + email" class="d-block">
						{{ email }}
					</a>
				</div>
			</div>
		</div>
	</template-frame>`
};