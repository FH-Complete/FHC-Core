import TemplateFrame from "./template/frame.js";

export default {
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		mode: String,
		res: Object,
		actions: Object
	},
	computed: {
		title() {
			if (this.mode == 'simple')
				return this.res.name;
			return this.res.name + ' (' + this.res.status + ' ' + this.res.stg_kuerzel + ')';
		},
		photo_url() {
			if (this.mode != 'simple')
				return this.res.photo_url;
			if (this.res.foto)
				return 'data:image/jpeg;base64,' + this.res.foto;
			return null;
		},
		emails() {
			if (this.mode == 'simple')
				return new Set([this.res.email]);
			return new Set(this.res.email);
		}
	},
	template: `
	<template-frame
		class="searchbar-result-prestudent"
		:res="res"
		:actions="actions"
		:title="title"
		:image="photo_url"
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
			<div v-if="res.uid" class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('search/result_student_uid') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.uid }}
				</div>
			</div>
			<div v-if="res.matrikelnr" class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('person/matrikelnummer') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.matrikelnr }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('search/result_prestudent_id') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.prestudent_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('lehre/studiengang') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.bezeichnung }} {{ res.orgform ? '(' + res.orgform + ')' : '' }}
				</div>
			</div>
		</div>
	</template-frame>`
};