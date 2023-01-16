import CisMenuEntry from "./Menu/Entry.js";
import FhcSearchbar from "../searchbar/searchbar.js";

export default {
    components: {
        CisMenuEntry,
        FhcSearchbar
    },
    props: {
        rootUrl: String,
        logoUrl: String,
        avatarUrl: String,
        logoutUrl: String,
        searchbaroptions: Object,
        searchfunction: Function
    },
    data: () => {
        return {
            entries: []
        };
    },
    created() {
        axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/CisVue/Menu').then(res => {
            this.entries = res.data.retval.childs;
        });
    },
    template: `
    <button id="nav-main-btn" class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#nav-main" aria-controls="nav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a id="nav-logo" :href="rootUrl">
        <img :src="logoUrl" alt="Logo">
    </a>
    <nav id="nav-main" class="offcanvas offcanvas-start bg-dark" tabindex="-1" aria-labelledby="nav-main-btn" data-bs-backdrop="false">
        <div id="nav-main-toggle" class="position-static d-none d-lg-block bg-dark">
            <button type="button" class="btn bg-dark text-light rounded-0 p-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#nav-main-menu" aria-expanded="true" aria-controls="nav-main-menu">
                <i class="fa fa-arrow-circle-left"></i>
            </button>
        </div>
        <div class="offcanvas-body p-0">
            <fhc-searchbar id="nav-search" class="fhc-searchbar w-100" :searchoptions="searchbaroptions" :searchfunction="searchfunction"></fhc-searchbar>
            <button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
                <img :src="avatarUrl" class="avatar rounded-circle"/>
            </button>
            <ul id="nav-user-menu" class="collapse list-unstyled" aria-labelledby="nav-user-btn">
                <li><a class="btn btn-level-2 rounded-0 d-block" href="#" id="menu-profil">Profil</a></li>
                <li><a class="btn btn-level-2 rounded-0 d-block" href="#">Ampeln</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="btn btn-level-2 rounded-0 d-block" :href="logoutUrl">Logout</a></li>
            </ul>
            <div id="nav-main-menu" class="collapse collapse-horizontal show">
                <div>
                    <cis-menu-entry v-for="entry in entries" :key="entry.content_id" :entry="entry" />
                </div>
            </div>
        </div>
    </nav>`
};