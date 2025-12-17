import { CoreFilterCmpt } from "../../filter/Filter.js";
import AcceptDenyUpdate from "./AcceptDenyUpdate.js";
import Alert from "../../../components/Bootstrap/Alert.js";
import Loading from "../../../components/Loader.js";

import ApiProfilUpdate from '../../../api/factory/profilUpdate.js';
import { dateFilter } from '../../../tabulator/filters/Dates.js';

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
  inject: ["profilUpdateStates"],
  props: {
    id: {
      type: String,
    },
  },
  data() {
    return {
      categoryLoaded: false,
      showModal: false,
      modalData: null,
      loading: false,
      filter: "Pending",
      profil_update_id: Number(this.id),

  };
  },
	computed: {
		profilUpdateEvents: function () {
			return [
				{
					"event": "dataProcessed",
					"handler": this.handleDataProcessed
				}
			];
		},
		profilUpdateOptions: function () {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: (url, config, params) => {
					return this.$api.call(ApiProfilUpdate.getProfilUpdateWithPermission(params.filter));
				},
				ajaxParams: () => {
					let filter = '';
					switch (this.filter) {
						case this.profilUpdateStates["Pending"]:
							filter = this.profilUpdateStates["Pending"];
							break;
						case this.profilUpdateStates["Accepted"]:
							filter = this.profilUpdateStates["Accepted"];
							break;
						case this.profilUpdateStates["Rejected"]:
							filter = this.profilUpdateStates["Rejected"];
							break;
						default:
							filter = '';
					}
					return {
						"filter": filter
					};
				},
        ajaxResponse: (url, params, response) => {
          //url - the URL of the request
          //params - the parameters passed with the request
          //response - the JSON object returned in the body of the response.
          //? sorts the response data from the backend
					if (response?.data)
						response.data.sort((ele1, ele2) => sortProfilUpdates(ele1, ele2, this));

					return response.data;
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
                  this.$api
                    .call(ApiProfilUpdate.acceptProfilRequest(column.getData()))
                    .then((res) => {
                      this.$refs.UpdatesTable.tabulator.setData();
                    })
					.catch((e) => this.$fhcAlert.handleSystemError);
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
                  this.$api
                    .call(ApiProfilUpdate.denyProfilRequest(column.getData()))
                    .then((res) => {
                      this.$refs.UpdatesTable.tabulator.setData();
                    })
					.catch((e) => this.$fhcAlert.handleSystemError);
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
        layout: "fitDataStretchFrozen",

        columns: [
          {
            title: this.$p.t("profilUpdate", "UID"),
            field: "uid",
            minWidth: 100,
            resizable: true,
            headerFilter: true,
            //responsive:0,
          },
          {
            title: this.$p.t("profilUpdate", "Name"),
            field: "name",
            minWidth: 200,
            resizable: true,
            headerFilter: true,
            //responsive:0,
          },
          {
            title: this.$p.t("lehre", "studiengang") + ' (' + this.$p.t("profil", "studentIn") + ')',
            field: "studiengang",
            minWidth: 50,
            resizable: true,
            headerFilter: "list",
            headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"},
            //responsive:0,
          },
          {
            title: this.$p.t("lehre", "organisationsform") + ' (' + this.$p.t("profil", "studentIn") + ')',
            field: "orgform",
            minWidth: 50,
            resizable: true,
            headerFilter: "list",
            headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"},
            //responsive:0,
          },
          {
            title: this.$p.t("lehre", "organisationseinheit") + ' (' + this.$p.t("profil", "mitarbeiterIn") + ')',
            field: "oezuordnung",
            minWidth: 200,
            resizable: true,
            headerFilter: "list",
            headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"},
            //responsive:0,
          },
          {
            title: this.$p.t("profilUpdate", "Topic"),
            field: "topic",
            resizable: true,
            minWidth: 200,
            headerFilter: "list",
            headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"},
            //responsive:0,
          },
          {
            title: this.$p.t("profilUpdate", "insertamum"),
            field: "insertamum_iso",
            resizable: true,
			headerFilterFunc: 'dates',
			headerFilter: dateFilter,
            minWidth: 200,
			formatter:"datetime",
			formatterParams: this.datetimeFormatterParams(),
            //responsive:0,
          },
          {
            title: this.$p.t("profilUpdate", "Status"),
            field: "status_translated",
            hozAlign: "center",
            headerFilter: "list",
            headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"},
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
            title: this.$p.t("profilUpdate", "actions"),
            headerSort: false,
			frozen: true,
            formatter: (cell, params) => {
              let details = this.$p.t('global', 'details');
              let html = `<div class="d-flex justify-content-evenly align-items-center">
                <button class="btn btn-secondary" id="showButton">${details}</button>
              </div>`;

              // Convert the HTML string to an HTML node
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, "text/html");
              const node = doc.body.firstChild;

              // Add event listeners
              node
                .querySelector("#showButton")
                .addEventListener("click", () => {
                  this.showAcceptDenyModal(cell.getRow().getData());
                });

              return node;
            },
            minWidth: 200,
            resizable: true,
            hozAlign: "center",
          },
        ],
      };
    }
    
  },
  methods: {
    denyProfilUpdate: function (data) {
      this.$api
        .call(ApiProfilUpdate.denyProfilRequest(data))
        .then((res) => {
          // block when the request was successful
        })
		.catch((e) => this.$fhcAlert.handleSystemError)
        .finally(() => {
          this.$refs.UpdatesTable.tabulator.setData();
        });
    },
    acceptProfilUpdate: function (data) {
      this.$api
        .call(ApiProfilUpdate.acceptProfilRequest(data))
        .then((res) => {
          // block when the request was successful
        })
		.catch((e) => this.$fhcAlert.handleSystemError)
        .finally(() => {
          // update the data inside the table
          this.$refs.UpdatesTable.tabulator.setData();
        });
    },
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
      sessionStorage.setItem("filter", event.target.value);
		},
		handleDataProcessed: function () {
			if (this.profil_update_id) {
				const arrayRowData = this.$refs.UpdatesTable.tabulator
					.getData()
					.filter((row) => {
						return row.profil_update_id === this.profil_update_id;
					});
				if (arrayRowData.length) {
					this.showAcceptDenyModal(arrayRowData[0]);
				}
			}
		},
		datetimeFormatterParams: function() {
			const params = {
				inputFormat:"yyyy-MM-dd",
				outputFormat:"dd.MM.yyyy",
				invalidPlaceholder:"(invalid date)",
				timezone:FHC_JS_DATA_STORAGE_OBJECT.timezone
			};
			return params;
		}
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
  created() {
    this.$p.loadCategory(["profilUpdate", "lehre", "profil", "global"]).then(() => {
      this.categoryLoaded = true;
    });
  },

  mounted() {
		//? opens the AcceptDenyUpdate Modal if a preselected profil_update_id was passed to the component (used for email links)
    if (sessionStorage.getItem("filter")) {
      this.filter = sessionStorage.getItem("filter");
    }
  },
  template: /*html*/ `
    <div>
   
    <accept-deny-update :title="$p.t('profilUpdate','profilUpdateRequest')" v-if="showModal" ref="AcceptDenyModal" @hideBsModal="hideAcceptDenyModal" :value="JSON.parse(JSON.stringify(modalData))" :setLoading="setLoading" ></accept-deny-update>
    <div  class="form-underline flex-fill ">
      <div class="form-underline-titel">{{$p.t('ui','anzeigen')}} </div>
      
      <select class="mb-4 form-select" v-model="filter" @change="updateData" aria-label="Profil updates display selection">
        <option :selected="true" :value="profilUpdateStates['Pending']" >{{$p.t('profilUpdate','pendingRequests')}}</option>
        <option :value="profilUpdateStates['Accepted']">{{$p.t('profilUpdate','acceptedRequests')}}</option>
        <option :value="profilUpdateStates['Rejected']">{{$p.t('profilUpdate','rejectedRequests')}}</option>
        <option :value="'Alle'">{{$p.t('profilUpdate','allRequests')}}</option>
      </select>
  
    </div>
    <loading ref="loadingModalRef" :timeout="0"></loading>
    
    <core-filter-cmpt v-if="profilUpdateStates && categoryLoaded" :title="$p.t('profilUpdate','profilUpdateRequests')"  ref="UpdatesTable" :tabulatorEvents="profilUpdateEvents" :tabulator-options="profilUpdateOptions" tableOnly :sideMenu="false" />

    </div>`,
};
