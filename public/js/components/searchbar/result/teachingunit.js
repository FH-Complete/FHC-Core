import TemplateFrame from "./template/frame.js";

export default {
	name: 'SearchbarResultTeachingUnit',
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	template: `
	<template-frame
		class="searchbar-result-teachingunit"
		:res="res"
		:actions="actions"
		:title="res.bezeichnung"
		:image-fallback="res.foto"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label"> {{ res.type === 'lehreinheit' ? $p.t('lehre/lehreinheit') : $p.t('lehre/lehrveranstaltung') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('lehre/studiengang') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.studiengang }}
				</div>
			</div>
			
			<div class="searchbar_tablerow" v-if="res.studiensemester_kurzbz">
				<div class="searchbar_tablecell searchbar_label">{{ $p.t('lehre/studiensemester') }}</div>
				<div class="searchbar_tablecell searchbar_value">
					{{ res.studiensemester_kurzbz }}
				</div>
			</div>
		
		</div>
	</template-frame>`
};