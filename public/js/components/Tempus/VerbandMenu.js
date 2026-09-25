import BaseTreemenu from '../Base/Treemenu.js';

export default {
	name: 'TempusVerbandMenu',
	components: {
		BaseTreemenu,
	},
	emits: ['select-verband-and-close'],
	methods: {
		onSelectVerbandAndClose(payload) {
			this.$emit('select-verband-and-close', payload);
		},
	},
	template: /* html */ `
    <div id="verbandMenu" class="offcanvas offcanvas-start col-md p-md-0 h-100" tabindex="-1" data-cy="verbandMenu">
		<div class="offcanvas-header justify-content-end px-1 d-md-none">
			<h5 class="offcanvas-title" id="verbandMenuLabel">
				<i class="fa-solid fa-university me-2"></i>Verband
			</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
		</div>
		<base-treemenu config="tempus" @select-entry="onSelectVerbandAndClose" class="col" style="height:0%"></base-treemenu>
	</div>
  `,
};
