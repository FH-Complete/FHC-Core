import AppMenu from "../AppMenu.js";

export default {
  name: "TempusAppMenu",
  components: {
    AppMenu,
  },
  template: /* html */ `
    <aside id="appMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
      <div class="offcanvas-header">
        Tempus
        <button
          type="button"
          class="btn-close text-reset"
          data-bs-dismiss="offcanvas"
          :aria-label="$p.t('ui/schliessen')"
        ></button>
      </div>
      <div class="offcanvas-body">
        <app-menu app-identifier="tempus" />
      </div>
    </aside>
  `,
};
