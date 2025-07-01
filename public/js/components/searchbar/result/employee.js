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
				<div class="searchbar_tablecell">{{ $p.t('search/result_stdkst') }}</div>
				<div class="searchbar_tablecell">
					<ul class="searchbar_inline_ul" v-if="res.standardkostenstelle.length > 0">
						<li v-for="(stdkst, idx) in res.standardkostenstelle" :key="idx">{{ stdkst }}</li>
					</ul>
					<span v-else="">{{ $p.t('search/result_stdkst_none') }}</span>
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('lehre/organisationseinheit') }}</div>
				<div class="searchbar_tablecell">
					<ul class="searchbar_inline_ul" v-if="res.organisationunit_name.length > 0">
						<li v-for="(oe, idx) in res.organisationunit_name" :key="idx">{{ oe }}</li>
					</ul>
					<span v-else="">{{ $p.t('search/result_oe_none') }}</span>
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_emails') }}</div>
				<div class="searchbar_tablecell">
					<a :href="'mailto:' + res.email" class="d-block">
						{{ res.email }}
					</a>
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('person/telefon') }}</div>
				<div class="searchbar_tablecell">
					<a :href="'tel:' + res.phone" class="d-block">
						{{ res.phone }}
					</a>
				</div>
			</div>
		</div>
	</template-frame>`
};