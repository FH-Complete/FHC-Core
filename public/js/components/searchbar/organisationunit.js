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
        <div class="searchbar_result searchbar_organisationunit">
    
          <div class="searchbar_grid">
              <div class="searchbar_icon">
                <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                  <i class="fas fa-sitemap fa-4x"></i>
                </action>
              </div>
              
              <div class="searchbar_data">
                <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                  <span class="fw-bold">{{ res.name }}</span>
                </action>
        
                <div class="mb-3"></div>
        
                <div class="searchbar_table">
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell">übergeordnete OrgEinheit</div>
                    <div class="searchbar_tablecell">
                          {{ res.parentoe_name }}
                    </div>
                  </div>
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell">Gruppen-EMail</div>
                    <div class="searchbar_tablecell">
                        <a :href="this.mailtourl">
                          {{ res.mailgroup }}
                        </a>
                    </div>
                  </div>
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell">Leiter</div>
                    <div class="searchbar_tablecell">
                        <ul class="searchbar_inline_ul" v-if="res.leaders.length > 0">
                          <li v-for="(leader, idx) in res.leaders" :key="idx">{{ leader.name }}</li>
                        </ul>
                        <span v-else="">N.N.</span>                                
                    </div>
                  </div>        
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell">Mitarbeiter-Anzahl</div>
                    <div class="searchbar_tablecell">
                        {{ res.number_of_people }}
                    </div>
                  </div>
        
                </div>
        
                <actions :res="this.res" :actions="this.actions.childactions" @actionexecuted="$emit('actionexecuted')"></actions>
        
              </div>        
          </div>
          
        </div>
    `,
    methods: {
    },
    computed: {
        mailtourl: function() {
            return 'mailto:' + this.res.mailgroup;
        },
        telurl: function() {
            return 'tel:' + this.res.phone;
        }
    }
};