
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
        getView: function(topic,status){
            switch(topic){
                case "Private Kontakte" : return "EditKontakt"; break;
                case "Private Adressen" : return "EditAdresse"; break;
                case "Add Adressen" : return "EditAdresse"; break;
                case "Add Kontakte" : return "EditKontakt"; break;
                case "Delete Adressen" :  return status ==='pending'? "Adresse": "Status" ; break;
                case "Delete Kontakte" : return status ==='pending'? "Kontakt": "Status"; break;
                default: return "text_input"; break;
            }
        },
        openModal(updateRequest) {
            console.log(JSON.stringify(updateRequest));

            let view = this.getView(updateRequest.topic,updateRequest.status);
            let data = null;
            let content =null;
            if(view === 'text_input'){
               //TODO:  change data handling for text_input component to accept the data in the same way as the other components
                data = {
                        titel:updateRequest.topic,
                        value:updateRequest.requested_change,
                    };
            }else{
                data = updateRequest.requested_change;
            }

            content={
                view:view,
                data:data,
                update:true,
                topic:updateRequest.topic,
                
            }

            //? adds the status information if the profil update request was rejected or accepted
            if(updateRequest.status !== 'pending'){
                content['status']= updateRequest.status;
                content['status_message']= updateRequest.status_message;
                content['status_timestamp']=updateRequest.status_timestamp;
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
                <th scope="col">Status</th>
                <th scope="col">Date of Request</th>
                
                <th  scope="col">Action</th>
                
                
                </tr>
            </thead>
            <tbody>
            <!-- :class="{'bg-success':item.status === 'accepted', 'bg-danger':item.status === 'rejected', 'text-white':item.status =='rejected' || item.status=='accepted'}" -->
                <tr v-for="item in data" :style="item.status=='accepted'?'background-color:lightgreen':item.status==='rejected'?'background-color:lightcoral':''">
                <td class="align-middle">{{item.topic}}</td>
                <td class="align-middle text-center" >{{item.status}}</td>
                <td class="align-middle">{{item.change_timestamp}}</td>
                
                
                
                <template v-if="item.status === 'pending'">
                <td>
                <template v-if="item.topic.toLowerCase().includes('delete')">
                <!-- old edit view for delete requests <div class="align-middle text-center" >{{item.requested_change.adr_typ?item.requested_change.adr_typ:item.requested_change.kontakt}}</div>-->
                <div  class="align-middle text-center"><i style="color:gray" role="button" @click="openModal(item)" class="fa fa-eye"></i></div>
                </template>
                <template v-else >
                <div class="align-middle text-center" ><i style="color:#00639c" @click="openModal(item)" role="button" class="fa fa-edit"></i></div>
                </template>
                
                <div class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></div>
                
                </td>
                </template>
                <template v-else-if="item.status === 'accepted'">
                <td  class="align-middle text-center"><i style="color:gray" role="button" @click="openModal(item)" class="fa fa-eye"></i></td>
                </template>
                <template v-else-if="item.status === 'rejected'">
                <td  class="align-middle text-center"><i style="color:gray" role="button" @click="openModal(item)" class="fa fa-eye"></i></td>
                </template>
                
                
                
                
                
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    </div>

    
    `
};