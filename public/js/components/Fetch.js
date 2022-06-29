export const CoreFetchCmpt = {
    props: ["apifunction"],
    data: function() {
      return {
        loading: false,
        error: null,
        data: []
      };
    },
    template: `
    <div>
        <slot v-if="loading" name="loading">
            <p>Loading ...</p>
        </slot>
        <slot v-else-if="error" name="error" v-bind:error="error">
            <pre>{{ JSON.stringify(error, null, 4) }}</pre>
        </slot>
        <slot v-else name="data" v-bind:data="data">
            <pre>{{ JSON.stringify(data, null, 4) }}</pre>
        </slot>
    </div>
    `,
    created: function () {
        this.fetchData();
    },
    methods: {
        fetchData: function() {
            var that = this;
            
            this.loading = true;
            this.error = null;
            
            this.apifunction()
            .then(function(response) {
                that.data = response.data;
                that.$emit('datafetched', that.data.data);
            })
            .catch(function(error) {
                that.error = error;
            })
            .finally(function() {
                that.loading = false;
            });
        }
    }
};