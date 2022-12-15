export default {
  data: () => {
    return {
      imgSrc: FHC_JS_DATA_STORAGE_OBJECT.app_root + '/cis/public/bild.php'
    }
  },
  created() {
    axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/cis/navigation/user').then(res => {this.imgSrc += '?src=person&person_id=' + res.data.retval});
  },
  template: `
  <div class="dropdown dropstart">
    <a class="dropdown-toggle nav-link" href="#" id="dropdown01" data-bs-toggle="dropdown" aria-expanded="false">
      <img :src="imgSrc" class="avatar rounded-circle" width="45" height="45"/>
    </a>
    <ul class="dropdown-menu dropdown-menu-dark m-0" aria-labelledby="dropdown01">
      <li><a class="dropdown-item" href="#" id="menu-profil">Profil</a></li>
      <li><a class="dropdown-item" href="#">Ampeln</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="#">Logout</a></li>
    </ul>
  </div>`
};