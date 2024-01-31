import fhcapifactory from "../api/fhcapifactory.js";
import {CoreFilterCmpt} from "../../components/filter/Filter.js";
import AcceptDenyUpdate from "../../components/Cis/ProfilUpdate/AcceptDenyUpdate.js";
Vue.$fhcapi = fhcapifactory;




const app = Vue.createApp({
    components:{
        CoreFilterCmpt,
        
    },
    data(){
        return{
          
            profil_updates_table_options:{
              ajaxURL:FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/getAllRequests',
                height:600,
                layout:'fitColumns',
                
                  columns: [
                    {
                      title: "UID",
                      field: "uid",
                      minWidth: 200,
                      resizable:true,
                      headerFilter: true,
                      //responsive:0,
                    },
                    {
                      title: "Name",
                      field: "name",
                      minWidth: 200,
                      resizable:true,
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
                      hozAlign:'center',
                      headerFilter: true,
                      formatter: function(cell,para){
                        let res =Object.getPrototypeOf(cell);
                        //console.log(res);
                        
                        switch(cell.getValue()){
                          case "pending": return "<i class='fa fa-circle-info text-info fa-lg'></i> pending";
                          case "accepted": return "<i class='fa fa-circle-check text-success fa-lg'></i> accepted";
                          case "rejected": return "<i class='fa-solid fa-circle-xmark text-danger fa-lg '></i> rejected";
                          default: return "<p>default</p>";
                        }
                      },
                      
                      resizable: true,
                      minWidth: 200,
                      //responsive:0,
                    },
                    {
                      title: "View",
                      formatter:function(){ return "<i class='fa fa-eye'></i>";},
                      resizable: true,
                      minWidth: 200,
                      hozAlign: 'center',
                      cellClick:(e, cell)=>{
                        //! function that is called when clicking on a row in the table
                        
                        let cellData = cell.getRow().getData();
                        
                        AcceptDenyUpdate.popup({value:cellData}).then(res=>{
                          console.log("res of the modal: ",res);
                          //? refetches the data, if any request was denied or accepted
                          //* setData will call the ajaxURL again to refresh the data 
                          this.$refs.UpdatesTable.tabulator.setData();
                        }).catch(e=>{
                          //? catches the rejected Promise if the result of the modal was falsy
                          console.log("catch of the modal: ",e);
                        });
                      
                          
                      },
                      //responsive:0,
                    },
                    
                    
                    
                  ],
            },
        }
    },
    computed:{
      
    },
    methods:{
        
      
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