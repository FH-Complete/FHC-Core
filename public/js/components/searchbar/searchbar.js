import person from "./person.js";
import raum from "./raum.js";
import employee from "./employee.js";
import organisationunit from "./organisationunit.js";

export default {
    props: [ "searchoptions", "searchfunction" ],
    data: function() {
      return {
        searchtimer: null,
        hidetimer: null,
        showsettings: false,
        searchsettings: {
            searchstr: '',
            types: []
        },
        showresult: false,        
        searchresult: [],
        searching: false,
        error: null
      };
    },
    components: {
      person: person,
      raum: raum,
      employee: employee,
      organisationunit: organisationunit
    },
    template: `
          <form ref="searchform" class="d-flex me-3" action="javascript:void(0);" 
            @focusin="this.searchfocusin" @focusout="this.searchfocusout">
            <div class="input-group me-2 bg-white">
                <input ref="searchbox" @keyup="this.search" @focus="this.showsearchresult" 
                    v-model="this.searchsettings.searchstr" class="form-control" 
                    type="search" placeholder="Search" aria-label="Search">
                <button ref="settingsbutton" @click="this.togglesettings" class="btn btn-outline-secondary" type="button" id="search-filter"><i class="fas fa-cog"></i></button>
            </div>            
        
            <div v-show="this.showresult" ref="result" 
                 class="searchbar_results" tabindex="-1">
              <div v-if="this.searching">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
              </div>
              <div v-else-if="this.error !== null">{{ this.error }}</div>
              <div v-else-if="this.searchresult.length < 1">Es wurden keine Ergebnisse gefunden.</div>
              <template v-else="" v-for="res in this.searchresult">
                <person v-if="res.type === 'person'" :res="res" :actions="this.searchoptions.actions.person" @actionexecuted="this.hideresult"></person>
                <employee v-else-if="res.type === 'mitarbeiter'" :res="res" :actions="this.searchoptions.actions.employee" @actionexecuted="this.hideresult"></employee>
                <organisationunit v-else-if="res.type === 'organisationunit'" :res="res" :actions="this.searchoptions.actions.organisationunit" @actionexecuted="this.hideresult"></organisationunit>
                <raum v-else-if="res.type === 'raum'" :res="res" :actions="this.searchoptions.actions.raum" @actionexecuted="this.hideresult"></raum>
                <div v-else="">Unbekannter Ergebnistyp: '{{ res.type }}'.</div>
              </template>
            </div>
            <div v-show="this.showsettings" ref="settings" 
                 class="searchbar_settings" tabindex="-1">
              <div class="btn-group" v-if="this.searchoptions.types.length > 0">
                <template v-for="(type, index) in this.searchoptions.types" :key="type">
                  <input type="checkbox" class="btn-check" :id="this.$.uid + 'search_type_' + index" :value="type" v-model="this.searchsettings.types"/>
                  <label class="btn btn-outline-secondary" :for="this.$.uid + 'search_type_' + index">{{ type }}</label>
                </template>
              </div>
              <div class="mb-2"></div>
              <button ref="settingsrefreshsearch" @click="this.refreshsearch" class="btn btn-primary" type="button">Übernehmen</button>
            </div>
        
          </form>
    `,
    beforeMount: function() {
        this.updateSearchOptions();
    },
    methods: {
        updateSearchOptions: function() {
            this.searchsettings.types = [];
            for( const idx in this.searchoptions.types ) {
                this.searchsettings.types.push(this.searchoptions.types[idx]);
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
            var that = this;
            this.error = null;
            this.searchresult = [];
            this.searching = true;
            this.showsearchresult();            
            this.searchfunction(this.searchsettings)
            .then(function(response) {
                that.searchresult = response.data.data;
            })
            .catch(function(error) {
                that.error = 'Bei der Suche ist ein Fehler aufgetreten.' 
                    + ' ' + error.message;
            })
            .finally(function() {
                that.searching = false;
            });
        },
        refreshsearch: function() {
          this.search();
          this.togglesettings();
        },
        calcSearchSettingsExtent: function() {
            var rect = this.$refs.settingsbutton.getBoundingClientRect();
            //console.log(window.innerWidth + ' ' + window.innerHeight + ' ' + JSON.stringify(rect));
            this.$refs.settings.style.top = Math.floor(rect.bottom + 3) + 'px';
            this.$refs.settings.style.right = Math.floor(window.innerWidth - rect.right) + 'px';
            this.$refs.settings.style.width = Math.floor(window.innerWidth * 0.5) + 'px';
            //this.$refs.settings.style.height = Math.floor(window.innerHeight * 0.5) + 'px';  
        },
        togglesettings: function() {
            this.showsettings = !this.showsettings;
            this.calcSearchSettingsExtent();
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
