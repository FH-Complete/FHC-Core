import { CoreFilterCmpt } from "../../filter/Filter.js";
import AcceptDenyUpdate from "./AcceptDenyUpdate.js";
import Alert from "../../../components/Bootstrap/Alert.js";
import Loading from "../../../components/Loader.js";

const sortProfilUpdates = (ele1, ele2, thisPointer) => {
  let result = 0;
  if (ele1.status === thisPointer.profilUpdateStates["Pending"]) {
    result = -1;
  } else if (ele1.status === thisPointer.profilUpdateStates["Accepted"]) {
    result =
      ele2.status === thisPointer.profilUpdateStates["Rejected"] ? -1 : 1;
  } else {
    result = 1;
  }

  if (ele1.status === ele2.status) {
    //? if they have the same status , insert_date gets compared for order
    result =
      new Date(ele2.insertamum.split(".").reverse().join("-")) -
      new Date(ele1.insertamum.split(".").reverse().join("-"));
  }
  return result;
};

export default {
  components: {
    CoreFilterCmpt,
    Loading,
    AcceptDenyUpdate,
  },
  inject: ["profilUpdateTopic", "profilUpdateStates"],
  props: {
    id: {
      type: Number,
    },
  },
  data() {
    return {
      showModal: false,
      modalData: null,
      loading: false,
      showAll: false,
      events: [],
      profil_update_id: Number(this.id),
      profil_updates_table_options: {
        ajaxURL:
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/`,

        ajaxURLGenerator: (url, config, params) => {
          //? this function needs to be an array function in order to access the this properties of the Vue component
          if (this.showAll) {
            return url + "getProfilUpdateWithPermission";
          } else {
            return (
              url +
              `getProfilUpdateWithPermission/${this.profilUpdateStates["Pending"]}`
            );
          }
        },
        ajaxResponse: (url, params, response) => {
          //url - the URL of the request
          //params - the parameters passed with the request
          //response - the JSON object returned in the body of the response.
          //? sorts the response data from the backend
          if (response)
            response.sort((ele1, ele2) => sortProfilUpdates(ele1, ele2, this));

          return response;
        },
        //? adds tooltip with the status message of a profil update request if its status is not pending
        columnDefaults: {
          tooltip: (e, cell, onRendered) => {
            //e - mouseover event
            //cell - cell component
            //onRendered - onRendered callback registration function
            let statusMessage = cell.getData().status_message;
            let statusDate = cell.getData().status_timestamp;
            let status = cell.getData().status;
            if (!statusMessage) {
              return null;
            }
            let el = document.createElement("div");
            el.classList.add("border", "border-dark");

            let statusDateEl = document.createElement("span");
            statusDateEl.classList.add("d-block", "mb-1");
            statusDateEl.innerHTML =
              "Request was " + status + " on " + statusDate;
            let statusMessageEl = document.createElement("span");
            statusMessageEl.innerHTML = "Status message: " + statusMessage;

            el.appendChild(statusDateEl);
            el.appendChild(statusMessageEl);
            return el;
          },
        },
        rowContextMenu: (e, component) => {
          let menu = [];
          if (
            component.getData().status === this.profilUpdateStates["Pending"]
          ) {
            menu.push(
              {
                label: `<i class='fa fa-check'></i> ${this.$p.t(
                  "profilUpdate",
                  "acceptUpdate"
                )}`,
                action: (e, column) => {
                  Vue.$fhcapi.ProfilUpdate.acceptProfilRequest(column.getData())
                    .then((res) => {
                      this.$refs.UpdatesTable.tabulator.setData();
                    })
                    .catch((e) => {
                      Alert.popup(Vue.h("div", { innerHTML: e.response.data }));
                    });
                },
              },
              {
                separator: true,
              },
              {
                label: ` <i style='width:16px' class='text-center fa fa-xmark'></i> ${this.$p.t(
                  "profilUpdate",
                  "denyUpdate"
                )}`,
                action: (e, column) => {
                  Vue.$fhcapi.ProfilUpdate.denyProfilRequest(column.getData())
                    .then((res) => {
                      this.$refs.UpdatesTable.tabulator.setData();
                    })
                    .catch((e) => {
                      Alert.popup(Vue.h("div", { innerHTML: e.response.data }));
                    });
                },
              },
              {
                separator: true,
              },
              {
                label: `<i class='fa fa-eye'></i> ${this.$p.t(
                  "profilUpdate",
                  "showRequest"
                )}`,
                action: (e, column) => {
                  this.showAcceptDenyModal(column.getData());
                },
              }
            );
          } else {
            menu.push({
              label: `<i class='fa fa-eye'></i> ${this.$p.t(
                "profilUpdate",
                "showRequest"
              )}`,
              action: (e, column) => {
                this.showAcceptDenyModal(column.getData());
              },
            });
          }
          return menu;
        },

        height: 600,
        layout: "fitColumns",

        columns: [
          {
            title: "UID",
            field: "uid",
            minWidth: 200,
            resizable: true,
            headerFilter: true,
            //responsive:0,
          },
          {
            title: "Name",
            field: "name",
            minWidth: 200,
            resizable: true,
            headerFilter: true,
            //responsive:0,
          },
          {
            title: "Topic",
            field: "topic",
            resizable: true,
            minWidth: 200,
            headerFilter: true,
            //responsive:0,
          },
          {
            title: "Insert Date",
            field: "insertamum",
            resizable: true,
            headerFilter: true,
            minWidth: 200,
            //responsive:0,
          },
          {
            title: "Status",
            field: "status_translated",
            hozAlign: "center",
            headerFilter: true,
            formatter: (cell, para) => {
              let iconClasses = "";
              let status = cell.getRow().getData().status;
              switch (status) {
                case this.profilUpdateStates["Pending"]:
                  iconClasses += "fa fa-lg fa-circle-info text-info ";
                  break;
                case this.profilUpdateStates["Accepted"]:
                  iconClasses += "fa fa-lg fa-circle-check text-success ";
                  break;
                case this.profilUpdateStates["Rejected"]:
                  iconClasses += "fa fa-lg fa-circle-xmark text-danger ";
                  break;
              }
              return `<div class='row justify-content-center'><div class='col-2'><i class='${iconClasses}'></i></div> <div class='col-4'><span>${cell.getValue()}</span></div></div>`;
            },

            resizable: true,
            minWidth: 200,
            //responsive:0,
          },
          {
            title: "View",
            formatter: function () {
              return "<i class='fa fa-eye'></i>";
            },
            resizable: true,
            minWidth: 200,
            hozAlign: "center",
            cellClick: (e, cell) => {
              //! function that is called when clicking on a row in the table
              let cellData = cell.getRow().getData();
              this.showAcceptDenyModal(cellData);
            },
            //responsive:0,
          },
        ],
      },
    };
  },
  computed: {
    getFetchUrl: function () {
      let url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/`;
      if (this.showAll) {
        url + "getAllRequests";
      } else {
        url + "getPendingRequests";
      }
      return url;
    },
  },
  methods: {
    setLoading: function (newValue) {
      this.loading = newValue;
    },
    hideAcceptDenyModal: function () {
      //? checks the AcceptDenyModal component property result, if the user made a successful request or not

      if (this.$refs.AcceptDenyModal.result) {
        //? refetches the data, if any request was denied or accepted
        //* setData will call the ajaxURL again to refresh the data

        this.$refs.UpdatesTable.tabulator.setData();
      } else {
        // when modal was closed without submitting request
      }
      this.showModal = false;
      this.modalData = null;
    },

    showAcceptDenyModal(value) {
      this.modalData = value;
      if (!this.modalData) {
        return;
      }
      this.showModal = true;

      // after a state change, wait for the DOM updates to complete
      Vue.nextTick(() => {
        this.$refs.AcceptDenyModal.show();
      });
    },

    updateData: function (event) {
      this.$refs.UpdatesTable.tabulator.setData();
      //? store the selected view in the session storage of the browser
      sessionStorage.setItem("showAll", event.target.value);
    },
  },
  watch: {
    loading: function (newValue, oldValue) {
      if (newValue) {
        this.$refs.loadingModalRef.show();
      } else {
        this.$refs.loadingModalRef.hide();
      }
    },
  },

  mounted() {
    //? opens the AcceptDenyUpdate Modal if a preselected profil_update_id was passed to the component (used for email links)
    if (this.profil_update_id) {
      this.$refs.UpdatesTable.tabulator.on("dataProcessed", () => {
        const arrayRowData = this.$refs.UpdatesTable.tabulator
          .getData()
          .filter((row) => {
            return row.profil_update_id === this.profil_update_id;
          });
        if (arrayRowData.length) {
          this.showAcceptDenyModal(arrayRowData[0]);
        }
      });
    }

    if (!(sessionStorage.getItem("showAll") === null)) {
      //? converting string into a boolean: https://sentry.io/answers/how-can-i-convert-a-string-to-a-boolean-in-javascript/
      this.showAll = sessionStorage.getItem("showAll") === "true";
    }
  },
  template: /*html*/ `
    <div>
    
    <accept-deny-update :title="$p.t('profilUpdate','profilUpdateRequest')" v-if="showModal" ref="AcceptDenyModal" @hideBsModal="hideAcceptDenyModal" :value="JSON.parse(JSON.stringify(modalData))" :setLoading="setLoading" ></accept-deny-update>
    
    <div  class="form-underline flex-fill ">
      <div class="form-underline-titel">{{$p.t('ui','anzeigen')}} </div>

      <select class="mb-4 " v-model="showAll" @change="updateData" class="form-select" aria-label="Profil updates display selection">
        <option :selected="true" :value="false">{{$p.t('profilUpdate','pendingRequests')}}</option>
        <option :value="false">{{$p.t('profilUpdate','acceptedRequests')}}</option>
        <option :value="false">{{$p.t('profilUpdate','rejectedRequests')}}</option>
        <option :value="true">{{$p.t('profilUpdate','allRequests')}}</option>
      </select>
  
    </div>

    <loading ref="loadingModalRef" :timeout="0"></loading>
    
    <core-filter-cmpt :title="$p.t('profilUpdate','profilUpdateRequests')"  ref="UpdatesTable" :tabulatorEvents="events" :tabulator-options="profil_updates_table_options" tableOnly :sideMenu="false" />

    </div>`,
};
