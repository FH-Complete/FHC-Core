import BaseTreemenu from '../Base/Treemenu.js';

export default {
	name: 'TempusRaumMenu',
	components: {
		BaseTreemenu,
	},
	emits: ['select-raum-and-close'],
	methods: {
		onSelectRaumAndClose(payload) {
			this.$emit('select-raum-and-close', payload);
		},
	},
	template: /* html */ `
    <div id="raumMenu" class="offcanvas offcanvas-start col-md p-md-0 h-100" tabindex="-1" data-cy="raumMenu">
		<div class="offcanvas-header justify-content-end px-1 d-md-none">
			<h5 class="offcanvas-title" id="verbandMenuLabel">
				<i class="fa-solid fa-university me-2"></i>Raum
			</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
		</div>
		<base-treemenu config="tempusorte" @select-entry="onSelectRaumAndClose" class="col" style="height:0%"></base-treemenu>
	</div>
  `,
};
