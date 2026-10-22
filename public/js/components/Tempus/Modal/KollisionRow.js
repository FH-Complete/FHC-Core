export default {
	name: 'EventKollisionRow',
	props: {
		event: {
			type: Object,
			required: true,
		}
	},
	inject: {
		modalActions: {
			from: 'modalActions',
			default: () => ({})
		},
	},
	computed: {
		collisions() {
			return Array.isArray(this.event.collisions) ? this.event.collisions : [];
		},
	},
	methods: {
		isJumpable(collision) {
			return !!collision.datum;
		},
		jumpTo(collision) {
			if (!this.isJumpable(collision))
				return;

			const modalEl = this.$el?.closest?.('.modal');
			if (modalEl)
				bootstrap.Modal.getInstance(modalEl)?.hide();

			this.modalActions?.goToEvent({
				kalender_id: collision.kalender_id ?? null,
				datum: collision.datum,
			});
		}
	},
	template: /*html*/`
	<tr v-if="collisions.length">
		<th>
			<i class="fa-solid fa-xl fa-triangle-exclamation text-danger"></i>
			{{ $p.t('ui','collision') }}:
		</th>
		<td>
			<div class="overflow-auto" style="max-height: 15vh;">
				<div v-for="(collision, index) in collisions" :key="collision.kalender_id ?? index" class="d-block">
					<a
						v-if="isJumpable(collision)"
						href="#"
						@click.prevent="jumpTo(collision)"
					>
						{{ collision.message }}
					</a>
					<template v-else>
						{{ collision.message }}
					</template>
				</div>
		</div>
		</td>
	</tr>`
};