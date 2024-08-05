import person from "./person.js";
import raum from "./raum.js";
import employee from "./employee.js";
import organisationunit from "./organisationunit.js";

export default {
    props: [ "searchoptions", "searchfunction","selectedtypes" ],
    
    data: function() {
      return {
        searchtimer: null,
        hidetimer: null,
        showsettings: false,
        searchsettings: {
            searchstr: '',
            types: [],
        },
        searchresult: [],
        showresult: false,  
        searching: false,
        error: null,
      };
    },
    components: {
      person: person,
      raum: raum,
      employee: employee,
      organisationunit: organisationunit
    },
   
    template: /*html*/`
          <form ref="searchform" class="d-flex me-3" action="javascript:void(0);" 
            @focusin="this.searchfocusin" @focusout="this.searchfocusout">
           
            <div class="h-100 input-group me-2 bg-white">
           
                <input ref="searchbox" @keyup="this.search" @focus="this.showsearchresult" 
                    v-model="this.searchsettings.searchstr" class="form-control" 
                    type="search" :placeholder="'Search: '+ searchsettings.types.join(' / ')" aria-label="Search">
                <button data-bs-toggle="collapse" data-bs-target="#searchSettings" aria-expanded="false" aria-controls="searchSettings" ref="settingsbutton"  class="btn btn-outline-secondary" type="button" id="search-filter"><i class="fas fa-cog"></i></button>
            </div>            
        
            <div v-show="this.showresult" ref="result" 
                 class="searchbar_results" tabindex="-1">
              <div v-if="this.searching">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
              </div>
              <div v-else-if="this.error !== null">{{ this.error }}</div>
              <div v-else-if="searchresult.length < 1">Es wurden keine Ergebnisse gefunden.</div>
              <template v-else="" v-for="res in searchresult">
                <person v-if="res.type === 'person'" :res="res" :actions="this.searchoptions.actions.person" @actionexecuted="this.hideresult"></person>
                <employee v-else-if="res.type === 'mitarbeiter'" :res="res" :actions="this.searchoptions.actions.employee" @actionexecuted="this.hideresult"></employee>
                <organisationunit v-else-if="res.type === 'organisationunit'" :res="res" :actions="this.searchoptions.actions.organisationunit" @actionexecuted="this.hideresult"></organisationunit>
                <raum v-else-if="res.type === 'raum'" :res="res" :actions="this.searchoptions.actions.raum" @actionexecuted="this.hideresult"></raum>
                <div v-else="">Unbekannter Ergebnistyp: '{{ res.type }}'.</div>
              </template>
            </div>

            <div id="searchSettings"  ref="settings" 
                 class="top-100 end-0 searchbar_settings text-white collapse" tabindex="-1">
              <div class="d-flex flex-column m-3" v-if="this.searchoptions.types.length > 0">
              <span class="fw-light mb-2">Suche filtern nach:</span>  
              <template v-for="(type, index) in this.searchoptions.types" :key="type">
                    <div class="form-check form-switch">
                        <input class="fhc-switches form-check-input" type="checkbox" role="switch" :id="this.$.uid + 'search_type_' + index" :value="type" v-model="searchsettings.types"  />
                        <label class="ps-2 form-check-label non-selectable" :for="this.$.uid + 'search_type_' + index">{{ type }}</label>
                    </div>
                </template>
              </div>
            </div>
        
          </form>
    `,
    watch:{
        'searchsettings.types'(newValue){
            this.search();
            
            
        },
        
    },
    beforeMount: function() {
        this.updateSearchOptions();
        
    },
    mounted(){
        window.addEventListener('resize', (event) =>{
            console.log(this.$refs.settings,"this is the refs of the settings")
            this.$refs.settings.hide();
            console.log("resizing")
        }); 
        //console.log(this.$refs.settings.show,"this are the refs")
    },
    methods: {
        

        updateSearchOptions: function() {
            this.searchsettings.types = [];
            for( const idx in this.selectedtypes ) {
                this.searchsettings.types.push(this.selectedtypes[idx]);
            }
        },
        calcSearchResultExtent: function() {
            var rect = this.$refs.searchbox.getBoundingClientRect();
            //console.log(window.innerWidth + ' ' + window.innerHeight + ' ' + JSON.stringify(rect));
            this.$refs.result.style.top = Math.floor(rect.bottom + 3) + 'px';
            this.$refs.result.style.right = Math.floor(window.innerWidth - rect.right) + 'px';
            this.$refs.result.style.width = Math.floor(window.innerWidth * 0.75) + 'px';
            this.$refs.result.style.height = Math.floor(window.innerHeight * 0.75) + 'px';
        },
        search: function() {
            if( this.searchtimer !== null ) {
                clearTimeout(this.searchtimer);
            }
            if( this.searchsettings.searchstr.length >= 3 ) {
                this.calcSearchResultExtent();
                this.searchtimer = setTimeout(
                    this.callsearchapi,
                    500
                );
            } else {                
                this.showresult = false;
            }
        },
        callsearchapi: function() {            
            this.error = null;
            this.searchresult.splice(0,this.searchresult.length);
            this.searching = true;
            this.showsearchresult();            
            this.searchfunction(this.searchsettings)
            .then(response=>{
                if( response.data?.error === 1 ) {
                    this.error = 'Bei der Suche ist ein Fehler aufgetreten.';
                } else {
                    for(let element of response.data.data){
                        this.searchresult.push(element);
                    }
                }
            })
            .catch(error=> {
                this.error = 'Bei der Suche ist ein Fehler aufgetreten.' 
                    + ' ' + error.message;
            })
            .finally(()=> {
                this.searching = false;
            });
        },
        refreshsearch: function() {
          this.search();
          this.togglesettings();
        },
        hideresult: function() {
            this.showresult = false;
            window.removeEventListener('resize', this.calcSearchResultExtent);
        },
        showsearchresult: function() {
            if( this.searchsettings.searchstr.length >= 3 ) {
                this.showresult = true;
                window.addEventListener('resize', this.calcSearchResultExtent);
            }
        },
        searchfocusin: function(e) {
            e.preventDefault();
            e.stopPropagation();
            if( this.hidetimer !== null ) {
                clearTimeout(this.hidetimer);
            }
        },
        searchfocusout: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.hidetimer = setTimeout(
                this.hideresult,
                100
            );
        }
    }
};
