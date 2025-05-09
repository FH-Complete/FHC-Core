export const Modal = {
	name: 'Modal',
    props: {
        type: String,
        title: String,
        noscroll: Boolean
    },
    expose: ['show', 'hide'],
    setup(props, { emit }) {
       
        let modalEle = Vue.ref(null);
        let thisModalObj;

        Vue.onMounted(() => {
            thisModalObj = new bootstrap.Modal(modalEle.value);
        });
        const show = () => {
            thisModalObj.show();
        }
        function hide() {
            thisModalObj.hide();
        }

        return { modalEle, show, hide };
    },

    template:`
    <div class="modal fade " id="customModal" tabindex="-1" aria-labelledby=""
        aria-hidden="true" ref="modalEle">
        <div class="modal-dialog modal-lg" :class="(!noscroll) ? 'modal-dialog-scrollable' : ''">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customModalLabel">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <slot name="body" />
                </div>
                <div class="modal-footer">
                    <slot name="footer"></slot>
                    <!-- button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        OK
                    </button-->
                </div>
            </div>
        </div>
    </div>`
};
