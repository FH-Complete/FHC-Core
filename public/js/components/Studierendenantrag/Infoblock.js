export default {
	props: {
		infos: Array,
	},
	template: `
	<div class="studierendenantrag-infoblock">
		<div v-for="(info, index) in infos" :key="index" class="alert d-flex align-items-center" :class="'alert-' + (info.severity || 'info')" role="alert">
			<i class="fa fa-lg flex-shrink-0 me-3" :class="'fa-' + (info.icon || 'info-circle')" role="img" aria-label="Info:"></i>
			<div v-if="info.title">
				<h4 v-if="info.title" class="alert-heading">
					{{info.title}}
				</h4>
				<p class="mb-0" v-html="info.body"></p>
			</div>
			<p v-else class="mb-0" v-html="info.body"></p>
		</div>
	</div>
	`
}
