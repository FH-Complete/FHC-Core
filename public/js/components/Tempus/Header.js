import CoreSearchbar from "../searchbar/searchbar.js";
import NavLanguage from "../navigation/Language.js";

export default {
  name: "TempusHeader",
  components: {
    CoreSearchbar,
    NavLanguage,
  },
  props: {
    tempusRoot: String,
    avatarUrl: String,
    logoutUrl: String,
    searchbaroptions: {
      type: Object,
      required: true,
    },
    searchfunction: {
      type: Function,
      required: true,
    },
  },
  emits: ["focus-searchbar", "language-changed"],
  methods: {
    focusSearchbar() {
      this.$refs.searchbar?.$refs?.input?.focus();
      this.$emit("focus-searchbar");
    },
  },
  template: /* html */ `
    <header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
      <div class="col-md-4 col-lg-3 col-xl-2 d-flex align-items-center">
        <button
          class="btn btn-outline-light border-0 m-1 collapsed"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#appMenu"
          aria-controls="appMenu"
          aria-expanded="false"
          :aria-label="$p.t('ui/toggle_nav')"
        >
          <span class="svg-icon svg-icon-apps"></span>
        </button>
        <a class="navbar-brand me-0" :href="tempusRoot">Tempus</a>
      </div>
      <button
        class="btn btn-outline-light border-0 d-md-none m-1 collapsed"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu"
        aria-expanded="false"
        :aria-label="$p.t('ui/toggle_nav')"
      >
        <span class="fa-solid fa-table-list"></span>
      </button>
      <core-searchbar
        ref="searchbar"
        :searchoptions="searchbaroptions"
        :searchfunction="searchfunction"
        class="searchbar position-relative w-100"
        show-btn-submit
      ></core-searchbar>
      <div id="nav-user" class="dropdown">
        <button
          id="nav-user-btn"
          class="btn btn-link rounded-0 py-0"
          type="button"
          data-bs-toggle="dropdown"
          data-bs-target="#nav-user-menu"
          aria-expanded="false"
          aria-controls="nav-user-menu"
        >
          <img
            :src="avatarUrl"
            :alt="$p.t('profilUpdate/profilBild')"
            class="bg-light avatar rounded-circle border border-light"
          />
        </button>
        <ul
          ref="navUserDropdown"
          class="dropdown-menu dropdown-menu-dark dropdown-menu-end rounded-0 text-center m-0"
          aria-labelledby="nav-user-btn"
        >
          <li>
            <button
              type="button"
              class="dropdown-item"
              data-bs-toggle="modal"
              data-bs-target="#configModal"
            >
              {{ $p.t('ui/settings') }}
            </button>
          </li>
          <li><hr class="dropdown-divider m-0"/></li>
          <li>
            <nav-language @changed="$emit('language-changed', $event)" item-class="dropdown-item border-left-dark" />
          </li>
          <li><hr class="dropdown-divider m-0"/></li>
          <li>
            <a class="dropdown-item" :href="logoutUrl">
              {{ $p.t('ui/logout') }}
            </a>
          </li>
        </ul>
      </div>
    </header>
  `,
};
