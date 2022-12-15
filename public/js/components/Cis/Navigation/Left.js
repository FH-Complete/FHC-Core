export default {
    template: `
    <div id="sidebar-container" class="d-none d-lg-block position-sticky">
        <ul class="list-group list-group-flush">
            <!-- Separator with title -->
            <li class="list-group-item sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
                <small>MAIN MENU</small>
            </li>
            <!-- /end Separator -->
            <a href="#submenu1" data-bs-toggle="collapse" aria-expanded="true" class="bg-primary list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <i class="fa fa-user fa-fw me-3"></i>
                    <span class="menu-collapsed">Mein CIS</span>
                </div>
            </a>
            <div id='submenu1' class="collapse show sidebar-submenu">
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white active" id="menu-mein-bereich">
                    <span class="menu-collapsed">Mein Bereich</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Studium</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white" id="menu-lvplan">
                    <span class="menu-collapsed">LV Plan</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Campus Life</span>
                </a>
            </div>
            <a href="#submenu2" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <i class="fa fa-user fa-fw me-3"></i>
                    <span class="menu-collapsed">FHTW Hochschule</span>
                </div>
            </a>
            <div id='submenu2' class="collapse sidebar-submenu">
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white" id="menu-organisation">
                    <span class="menu-collapsed">Organisation</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Studiengänge</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Forschung & Entwicklung</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Technikum Wien Acadamy</span>
                </a>
            </div>
            <a href="#submenu3" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary list-group-item flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <i class="fa fa-user fa-fw me-3"></i>
                    <span class="menu-collapsed">FHTW Services</span>
                </div>
            </a>
            <div id='submenu3' class="collapse sidebar-submenu">
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Teaching & Learning Center</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">International Office</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">Bibliothek</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-dark text-white">
                    <span class="menu-collapsed">IT-Services</span>
                </a>
            </div>
            <a href="#submenu4" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary list-group-item flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <i class="fa fa-user fa-fw me-3"></i>
                    <span class="menu-collapsed">Links & Downloads</span>
                </div>
            </a>
            <li class="list-group-item sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
                <small>AKTUELL</small>
            </li>
            <a href="#" class="bg-primary list-group-item">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <i class="fa fa-medkit fa-fw me-3"></i>
                    <span class="menu-collapsed">COVID 19</span>
                </div>
            </a>
            <li class="list-group-item sidebar-separator menu-collapsed"></li>
        </ul>
    </div>`
};