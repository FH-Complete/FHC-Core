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
		class="searchbar-result-organisationunit"
		:res="res"
		:actions="actions"
		:title="res.name"
		image-fallback="fas fa-sitemap fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_parent_oe') }}</div>
				<div class="searchbar_tablecell">
					{{ res.parentoe_name }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_group_emails') }}</div>
				<div class="searchbar_tablecell">
					<a v-if="res.mailgroup" :href="'mailto:' + res.mailgroup">
						{{ res.mailgroup }}
					</a>
					<template v-else>-</template>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_leader') }}</div>
				<div class="searchbar_tablecell">
					<ul class="searchbar_inline_ul" v-if="res.leaders.length > 0">
						<li v-for="(leader, idx) in res.leaders" :key="idx">{{ leader.name }}</li>
					</ul>
					<span v-else="">{{ $p.t('search/result_leader_none') }}</span>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_number_of_employees') }}</div>
				<div class="searchbar_tablecell">
					{{ res.number_of_people }}
				</div>
			</div>
		</div>
	</template-frame>`
};