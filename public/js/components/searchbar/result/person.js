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
		class="searchbar-result-student"
		:res="res"
		:actions="actions"
		:title="res.name"
		:image="foto"
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
					<a v-for="email in res.email" :key="email" :href="'mailto:' + email" class="d-block">
						{{ email }}
					</a>
				</div>
			</div>
		</div>
	</template-frame>`
};