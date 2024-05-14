import action from "./action.js";
import actions from "./actions.js";

export default {
    props: [ "res", "actions" ],
    components: {
        action: action, 
        actions: actions
    },
    emits: [ 'actionexecuted' ],
    template: `
        <div class="searchbar_result searchbar_raum">
        <pre>{{JSON.stringify(actions,null,2)}}</pre>
        <pre>{{JSON.stringify(res,null,2)}}</pre>
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
                  <div class="searchbar_tablecell">Gebäude</div>
                  <div class="searchbar_tablecell">{{ res.building }}</div>
                </div>
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell">Stockwerk</div>
                  <div class="searchbar_tablecell">{{ res.floor }}</div>
                </div>
                <div class="searchbar_tablerow">
                  <div class="searchbar_tablecell">Raumnummer</div>
                  <div class="searchbar_tablecell">{{ res.room_number }}</div>
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