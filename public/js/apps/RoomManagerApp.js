/**
 * Copyright (C) 2023 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import RoomManagerOverview from "../components/RoomManager/RoomManagerOverview.js";

import FhcAlert from "../plugins/FhcAlert.js";
import Phrasen from "../plugins/Phrasen.js";
import FhcApi from "../plugins/Api.js";

import {capitalize} from "../helpers/StringHelpers.js";

const ciPath =
  FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, "") +
  FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(),
  routes: [
    {
      name: "overview",
      path: `/${ciPath}/RoomManager`,
      component: RoomManagerOverview,
    },
  ],
});

const app = Vue.createApp({
  components: {
    RoomManagerOverview
  },
});

app.config.globalProperties.$capitalize = capitalize;

app.use(router)
  .use(primevue.config.default, { zIndex: { overlay: 9999 } })
  .use(FhcAlert)
  .use(Phrasen)
  .use(FhcApi)
  .mount("#main");
