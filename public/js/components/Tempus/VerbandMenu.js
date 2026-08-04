import StvVerband from "../Stv/Studentenverwaltung/Verband.js";

export default {
  name: "TempusVerbandMenu",
  components: {
    StvVerband,
  },
  props: {
    endpoint: {
      type: Object,
      required: true,
    },
  },
  emits: ["select-verband"],
  methods: {
    onSelectVerband(payload) {
      this.$emit("select-verband", payload);
      bootstrap.Offcanvas.getOrCreateInstance(this.$refs.menu).hide();
    },
  },
  template: /* html */ `
    <div
      id="verbandMenu"
      ref="menu"
      class="offcanvas offcanvas-start col-md p-md-0 h-100"
      tabindex="-1"
      data-cy="verbandMenu"
    >
      <div class="offcanvas-header justify-content-end px-1 d-md-none">
        <h5 class="offcanvas-title" id="verbandMenuLabel">
          <i class="fa-solid fa-university me-2"></i>Verband
        </h5>
        <button
          type="button"
          class="btn-close text-reset"
          data-bs-dismiss="offcanvas"
          :aria-label="$p.t('ui/schliessen')"
        ></button>
      </div>
      <stv-verband
        :endpoint="endpoint"
        class="col"
        style="height:0%"
        @select-verband="onSelectVerband"
      ></stv-verband>
    </div>
  `,
};
