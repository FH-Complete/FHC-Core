import action from "./action.js";
import actions from "./actions.js";

export default {
    props: [ "res", "actions" ],
    components: {
        action: action, 
        actions: actions
    },
    emits: [ 'actionexecuted' ],
    template: /*html*/`
        <div class="searchbar_result searchbar_raum">
          <div class="searchbar_grid">
            <div class="searchbar_icon">
              <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                <i class="fas fa-door-open fa-4x"></i>
              </action>
            </div>
            <div class="searchbar_data">
              <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                <span class="fw-bold">{{ res.ort_kurzbz }}</span>
              </action>
        
              <div class="mb-3"></div>
        
              <div class="searchbar_table">
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell searchbar_label">Standort</div>
                  <div class="searchbar_tablecell searchbar_value">{{ res.standort }}</div>
                </div>
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell searchbar_label">Sitzplätze</div>
                  <div class="searchbar_tablecell searchbar_value">{{ res.sitzplaetze }}</div>
                </div>
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell searchbar_label">Gebäude</div>
                  <div class="searchbar_tablecell searchbar_value">{{ res.building }}</div>
                </div>
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell searchbar_label">Zusatz Informationen</div>
                  <div class="searchbar_tablecell searchbar_value"><div v-html="res.austattung.replace('<br />','')"></div></div>
                </div>
              </div>
        
              <actions :res="this.res" :actions="this.actions.childactions" @actionexecuted="$emit('actionexecuted')"></actions>
        
            </div>
          </div>
        
        </div>
    `,
    methods: {
    }
};