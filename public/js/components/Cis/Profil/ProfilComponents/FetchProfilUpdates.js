
import EditProfil from "../EditProfil.js";
//? EditProfil is the modal used to edit the profil updates
export default {
    props:{
        data:{
            type:Object,
        },
    },

    emits:["fetchUpdates"],
    
    data(){
        return {
            
        }
    },
    methods:{
        deleteRequest: function(item){
            
            Vue.$fhcapi.UserData.deleteProfilRequest(item.profil_update_id).then((res)=>{
                if(res.data.error){
                    //? open alert
                    console.log(res.data);                    
                }else{
                    this.$emit('fetchUpdates');
                }
            });
        },
        getView: function(topic){
            switch(topic){
                case "Private Kontakte" : return "EditKontakt"; break;
                case "Private Adressen" : return "EditAdresse"; break;
                case "Add Adressen" : return "EditAdresse"; break;
                case "Add Kontakte" : return "EditKontakt"; break;
                case "Delete Adressen" : return "EditAdresse"; break;
                case "Delete Kontakte" : return "EditKontakt"; break;
                default: return "text_input"; break;
            }
        },
        openModal(updateRequest) {

            let view = this.getView(updateRequest.topic);
            let content =null;
            if(view === 'text_input'){
                content={
                    view:view,
                    data:{
                        titel:updateRequest.topic,
                        value:updateRequest.requested_change,
                    },
                    update:true,
                    topic:updateRequest.topic,
                    
                }
            }else{
                content = {
                    view: view,
                    data: updateRequest.requested_change,
                    update:true,
                    topic:updateRequest.topic,
                    
                    
                }
            }


            //? only show the popup if also the right content is available
            if(content){
            EditProfil.popup({ 
                
                value:content,
                timestamp:null,
              }).then((res) => {
                if(res === true){
                    this.$emit('fetchUpdates');
                }
                
              }).catch(e => {
                // Wenn der User das Modal abbricht ohne Änderungen
               
              });
            
            }
           
          
          }, 
    },
    created(){
        
    },
    computed:{
        
    },
    template:`
   
    <div  class="card">
                      <div class="card-header">
                      Profil Updates
                      </div>
                      <div class="card-body" >
    <div class="table-responsive">
        <table class="m-0  table  table-hover">
            <thead>
                <tr >
                <th scope="col">Topic</th>
                <th scope="col">Date of Request</th>
                <th  scope="col">Bearbeiten</th>
                <th style="white-space:normal" scope="col">Löschen</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in data">
                <td class="align-middle">{{item.topic}}</td>
                <td class="align-middle">{{item.change_timestamp}}</td>
                <td v-if="item.topic.toLowerCase().includes('delete')" class="align-middle text-center" >{{item.requested_change.adr_typ?item.requested_change.adr_typ:item.requested_change.kontakt}}</td>
                <td v-else class="align-middle text-center" ><i style="color:#00639c" @click="openModal(item)" role="button" class="fa fa-edit"></i></td>
                <td class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    </div>

    
    `
};