
import { CoreFilterCmpt } from "../../filter/Filter.js";
import AcceptDenyUpdate from "./AcceptDenyUpdate.js";
import Alert from "../../../components/Bootstrap/Alert.js";
import Loading from "../../../components/Loader.js";


const sortProfilUpdates = (ele1, ele2) => {
  let result = 0;
  if (ele1.status === "pending") {
    result = -1;
  } else if (ele1.status === "accepted") {
    result = ele2.status === "rejected" ? -1 : 1;
  } else {
    result = 1;
  }
  //? if they have the same status the insert date is used for ordering
  if (ele1.status === ele2.status) {
    result =
      new Date(ele2.insertamum.split(".").reverse().join("-")) -
      new Date(ele1.insertamum.split(".").reverse().join("-"));
  }
  return result;
};

export default{
  components: {
    CoreFilterCmpt,
    Loading,
  },
  props:{
    id:{
        type:Number,
    }
  },
  data() {
    return {
      loading:false,
      showAll: false,
      events:[],
      profil_update_id:Number(this.id),
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
            return url + "getProfilUpdateWithPermission/pending";
          }
        },
        ajaxResponse: function (url, params, response) {
          //url - the URL of the request
          //params - the parameters passed with the request
          //response - the JSON object returned in the body of the response.
          //? sorts the response data from the backend
          if (response) response.sort(sortProfilUpdates);

          return response;
        },
        //? adds tooltip with the status message of a profil update request if its status is not pending
        columnDefaults: {
          tooltip: (e, cell, onRendered) =>{
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
            statusDateEl.classList.add("d-block","mb-1");
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
          if (component.getData().status === "pending") {
            menu.push(
              {
                label: "<i class='fa fa-check'></i> Accept Request",
                action: (e, column) => {
                  Vue.$fhcapi.ProfilUpdate.acceptProfilRequest(column.getData())
                    .then((res) => {
                      this.$refs.UpdatesTable.tabulator.setData();
                    })
                    .catch((e) => {
                      Alert.popup(Vue.h('div',{innerHTML:e.response.data}));
                    });
                },
              },
              {
                separator: true,
              },
              {
                label:
                  " <i style='width:16px' class='text-center fa fa-xmark'></i> Deny Request",
                action: (e, column) => {
                  Vue.$fhcapi.ProfilUpdate.denyProfilRequest(
                    column.getData()
                  ).then((res) => {
                    this.$refs.UpdatesTable.tabulator.setData();
                  }).catch((e) => {
                    Alert.popup(Vue.h('div',{innerHTML:e.response.data}));
                  });
                },
              },
              {
                separator: true,
              },
              {
                label: "<i class='fa fa-eye'></i> Show Request",
                action: (e, column) => {
                  this.showModal(column.getData());
                    
                },
              }
            );
          } else {
            menu.push({
              label: "<i class='fa fa-eye'></i> Show Request",
              action: (e, column) => {
                this.showModal(column.getData());
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
            field: "status",
            hozAlign: "center",
            headerFilter: true,
            formatter: function (cell, para) {
              switch (cell.getValue()) {
                case "pending":
                  return "<div class='row justify-content-center'><div class='col-2'><i class='fa fa-circle-info text-info fa-lg'></i></div> <div class='col-4'><span>pending</span></div></div>";
                case "accepted":
                  return "<div class='row justify-content-center'><div class='col-2'><i class='fa fa-circle-check text-success fa-lg'></i></div> <div class='col-4'><span>accepted</span></div></div>";
                case "rejected":
                  return "<div class='row justify-content-center'><div class='col-2'><i class='fa-solid fa-circle-xmark text-danger fa-lg '></i></div> <div class='col-4'><span>rejected</span></div></div>";
                default:
                  return "<p>default</p>";
              }
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
              this.showModal(cellData);
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
    setLoading: function(newValue){
      this.loading = newValue;
    },
   
    showModal: function (value) {
      AcceptDenyUpdate.popup({ value: value, setLoading:this.setLoading })
        .then((res) => {
          
          //? refetches the data, if any request was denied or accepted
          //* setData will call the ajaxURL again to refresh the data
          this.$refs.UpdatesTable.tabulator.setData();
        }).catch(err=>{
         
        })
        
    },
    updateData: function (event) {
      this.$refs.UpdatesTable.tabulator.setData();
      //? store the selected view in the session storage of the browser
      sessionStorage.setItem("showAll", event.target.value);
    },
  },
  watch:{
    loading: function(newValue, oldValue){
      if(newValue){
        this.$refs.loadingModalRef.show();
      }else{
        this.$refs.loadingModalRef.hide();
      }
    }
  },

  mounted() {

    //? opens the AcceptDenyUpdate Modal if the a preselected profil_update_id was passed to the component (used for email links)
    if(this.profil_update_id){
        
        this.$refs.UpdatesTable.tabulator.on("dataProcessed", ()=>{
        const arrayRowData = this.$refs.UpdatesTable.tabulator.getData().filter(row => {
            return row.profil_update_id === this.profil_update_id;
        });
        if(arrayRowData.length){
            this.showModal(arrayRowData[0]);
        }
        })
    }
   

    if (!(sessionStorage.getItem("showAll") === null)) {
      //? converting string into a boolean: https://sentry.io/answers/how-can-i-convert-a-string-to-a-boolean-in-javascript/
      this.showAll = sessionStorage.getItem("showAll")==="true";
      
    }
  },
  template: `
    <div>
    
    <div  class="form-underline flex-fill ">
      <div class="form-underline-titel">Show </div>

      <select class="mb-4 " v-model="showAll" @change="updateData" class="form-select" aria-label="Profil updates display selection">
        <option :selected="true" :value="false">Pending Requests</option>
        <option :value="true">All Requests</option>
      </select>
  
    </div>

    <loading ref="loadingModalRef" :timeout="0"></loading>
    
    <core-filter-cmpt title="Update Requests"  ref="UpdatesTable" :tabulatorEvents="events" :tabulator-options="profil_updates_table_options" tableOnly :sideMenu="false" />

    </div>`,
};
