import fhcapifactory from "../api/fhcapifactory.js";
import {CoreFilterCmpt} from '../../components/filter/Filter.js'
Vue.$fhcapi = fhcapifactory;

const app = Vue.createApp({
    components:{
        CoreFilterCmpt,
    },
    data(){
        return{
            profil_updates_table_options:{
                height:300,
                layout:'fitColumns',
                responsiveLayout: "collapse",
                data: [
                    {
                      uid: "",
                      profil_changes: "",
                      change_timestamp: "",
                    },
                  ],
                  columns: [
                    {
                      title: "Uid",
                      field: "uid",
                      minWidth: 200,
                    },
                    {
                      title: "Update",
                      field: "profil_changes",
                      minWidth: 200,
                    },
                    {
                      title: "Date",
                      field: "change_timestamp",
                      resizable: true,
                      minWidth: 200,
                    },
                    
                  ],
            },
        }
    },
    methods:{
        sideMenuFunction: function(){
            console.log("test from the side menu");
        }
    },
    created(){

        


    },
    mounted(){

        this.$refs.UpdatesTable.tabulator.on('tableBuilt',()=>{
            Vue.$fhcapi.UserData.getProfilUpdateRequest().then((data)=>{
                this.$refs.UpdatesTable.tabulator.setData(data.data);
            }).catch(()=>{});
            
        });
    },
    template:`
    <div>
    
    <core-filter-cmpt title="Update Requests"  ref="UpdatesTable" :tabulator-options="profil_updates_table_options" tableOnly :sideMenu="false" />

    </div>`,

})

app.mount('#content');