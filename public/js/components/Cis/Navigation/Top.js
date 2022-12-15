import FhcSearchbar from "../../searchbar/searchbar.js";
import CisNavigationUser from "./User.js";

export default {
    components: {
        FhcSearchbar,
        CisNavigationUser
    },
    data: () => {
        return {
            nav: null
        }
    },
    computed: {
        rootUrl() {
            return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/cis';
        },
        logoImg() {
            return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'public/images/logo-300x160.png';
        }
    },
    methods: {
        openNav() {
            if (this.nav)
                this.nav.toggle();
        },
        async search() {
            return {data: {data: []}};
        }
    },
    mounted() {
        this.nav = new bootstrap.Offcanvas(this.$refs.nav, {backdrop: false});
    },
    template: `
    <div class="cis-navigation-top navbar navbar-expand-lg fixed-top navbar-dark bg-primary p-0">
        <button class="navbar-toggler border-0" type="button" @click="openNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand col-auto col-lg-2 px-3 py-0 m-0" :href="rootUrl">
            <img :src="logoImg" width="90">
        </a>
        <div ref="nav" class="offcanvas offcanvas-start align-items-stretch mt-lg-0 w-100 px-3 px-lg-0 d-flex flex-column flex-lg-row bg-dark" tabindex="-1">
            <fhc-searchbar class="fhc-searchbar w-100 me-0" :searchoptions="{types:[],actions:{}}" :searchfunction="search"></fhc-searchbar>
            <ul class="navbar-nav flex-grow-1">
                <li class="nav-item d-lg-none">
                    <a class="nav-link" href="#">Mein CIS</a>
                </li>
                <li class="nav-item d-lg-none">
                    <a class="nav-link" href="#">FHTW Campus</a>
                </li>
                <li class="nav-item d-lg-none">
                    <a class="nav-link" href="#">FHTW Services</a>
                </li>
                <li class="nav-item d-lg-none">
                    <a class="nav-link" href="#">COVID 19</a>
                </li>
                <li class="nav-item">
                    <cis-navigation-user></cis-navigation-user>
                </li>
            </ul>
        </div>
    </div>`
};
