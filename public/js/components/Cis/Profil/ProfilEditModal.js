
export default {
  
    data() {
      return {
        
       
      };
    },
  
    
    props: ["editData"],
    methods: {
      
    },
  
    computed: {
    
  
  
    
    },
  
    mounted() {
     console.log(this.editData);
  
     
    },
  
    template: ` 
    <!-- Modal -->
    <div class="modal fade" id="editProfil" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editProfilLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProfilLabel">Edit Profil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" @click="insertEditData" class="btn btn-primary">Understood</button>
          </div>
        </div>
      </div>
    </div>
    
    
    
    <!-- end of trying the modal -->
              
      `,
  };
  





