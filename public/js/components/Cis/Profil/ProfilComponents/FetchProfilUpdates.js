
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
                case "Private Kontakte" : return status ==='pending'? "EditKontakt": "Status"; break;
                case "Private Adressen" : return status ==='pending'? "EditAdresse": "Status"; break;
                case "Add Adressen" : return status ==='pending'? "EditAdresse": "Status"; break;
                case "Add Kontakte" : return status ==='pending'? "EditKontakt": "Status"; break;
                case "Delete Adressen" :  return status ==='pending'? "Adresse": "Status" ; break;
                case "Delete Kontakte" : return status ==='pending'? "Kontakt": "Status"; break;
                default: return status ==='pending'? "TextInputDokument": "Status"; break;
            }
        },
        openModal(updateRequest) {

            let view = this.getView(updateRequest.topic,updateRequest.status);
            let data = null;
            let content =null;
            let files =null;
            let withFiles = false;

           
            if(view === "TextInputDokument"){
             
                data = {
                    titel:updateRequest.topic,
                    value: updateRequest.requested_change.value,
                    
                }; 
                if(updateRequest.requested_change.files.length){
                const FILE = updateRequest.requested_change.files?.map(file=>{return new File(["files[]"], file.name);})
                const FILELIST = new DataTransfer();
                FILE.forEach(file => {
                    FILELIST.items.add(file);
                })
                files=updateRequest.requested_change.files;
                }
                withFiles = true;
            }
            else{
                data = updateRequest.requested_change;
            }
                
            
            

            content={
                updateID:updateRequest.profil_update_id,
                view:view,
                data:data,
                withFiles:withFiles,
                topic:updateRequest.topic,
                files: files,
                
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
   
    <div  class="card text-nowrap" >
                      <div class="card-header">
                      Profil Updates
                      </div>
                      <div class="card-body" >
    <div class="table-responsive">
        <table class="m-0  table  table-hover">
            <thead >
                <tr >
                <th scope="col">Topic</th>
                <th scope="col">Status</th>
                <th scope="col">Date</th>
                <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in data" :style="item.status=='accepted'?'background-color:lightgreen':item.status==='rejected'?'background-color:lightcoral':''">
                <td class="align-middle text-wrap ">{{item.topic}}</td>
                <td class="align-middle " >{{item.status}}</td>
                <td class="align-middle">{{item.change_timestamp}}</td>
                
                <template v-if="item.status === 'pending'">
                <td>
                
                <div class="d-flex flex-row justify-content-evenly">
                <template v-if="item.topic.toLowerCase().includes('delete')">
                <div  class="align-middle text-center"><i role="button" @click="openModal(item)" class="fa fa-eye"></i></div>
                </template>

                <template v-else >
                <div class="align-middle text-center" ><i style="color:#00639c" @click="openModal(item)" role="button" class="fa fa-edit"></i></div>
                </template>
                
                <div class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></div>
                </div>

                </td>
                </template>

                <template v-else>
                
                <td  class="align-middle text-center">
                <div class="d-flex flex-row justify-content-evenly">
                <i  role="button" @click="openModal(item)" class="fa fa-eye"></i>
                </div>
                </td>
                
                </template>
                
                
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    </div>

    
    `
};