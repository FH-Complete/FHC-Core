import fhcapifactory from "../api/fhcapifactory.js";
import {CoreFilterCmpt} from '../../components/filter/Filter.js'
Vue.$fhcapi = fhcapifactory;
/* 
data: [
  {
    uid: "",
    profil_changes: "",
    change_timestamp: "",
  },
], */

const app = Vue.createApp({
    components:{
        CoreFilterCmpt,
    },
    data(){
        return{
            profil_updates_table_options:{
              ajaxURL:FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/getAllRequests',
                height:300,
                layout:'fitColumns',
                responsiveLayout: "collapse",
                responsiveLayoutCollapseUseFormatters:false,
                responsiveLayoutCollapseFormatter:this.collapseFormatter,
                
                  columns: [
                    {
                      title: "Uid",
                      field: "uid",
                      minWidth: 200,
                      responsive:0,
                    },
                    {
                      title: "Update",
                      field: "profil_changes",
                      minWidth: 10000,
                      responsive:3,
                    },
                    {
                      title: "Date",
                      field: "change_timestamp",
                      resizable: true,
                      minWidth: 200,
                      responsive:0,
                    },
                    
                  ],
            },
        }
    },
    methods:{
        sideMenuFunction: function(){
            console.log("test from the side menu");
        },
        collapseFormatter: function(data){
          //data - an array of objects containing the column title and value for each cell
          var container = document.createElement("div");
          container.classList.add("tabulator-collapsed-row");
          container.classList.add("text-break");
          
          var list = document.createElement("div");
          list.classList.add("row");
          
          
          container.appendChild(list);
          
          data.forEach(function(col){
            let item = document.createElement("div");
            item.classList.add("col-12");
          
            
            item.innerHTML = Object.keys(JSON.parse(col.value)).map(key => {return key+'<br/>'});
            
          
            list.appendChild(item);
         
          });
          
          return Object.keys(data).length ? container : "";
          },
    },
    created(){

        


    },
    mounted(){

        
    },
    template:`
    <div>
    
    <core-filter-cmpt title="Update Requests"  ref="UpdatesTable" :tabulator-options="profil_updates_table_options" tableOnly :sideMenu="false" />

    </div>`,

})

app.mount('#content');