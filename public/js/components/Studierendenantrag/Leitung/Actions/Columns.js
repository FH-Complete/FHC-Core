export default {
	props: {
		columns: Array
	},
	methods: {
		toggleColumn(col) {
			col.visible = !col.visible;
			col.original.toggle()
		}
	},
	template: `
	<div class="studierendenantrag-leitung-actions-columns">
		<div class="card mt-3">
			<div class="card-body d-flex flex-wrap gap-2 justify-content-center">
				<a v-for="col in columns" class="btn" :class="col.visible ? 'btn-dark' : 'btn-outline-dark'" href="#" @click.prevent="toggleColumn(col)">
					{{col.title}}
				</a>
			</div>
		</div>
	</div>
	`
}
