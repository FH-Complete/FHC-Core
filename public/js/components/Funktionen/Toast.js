export const Toast = {
	name: 'Toast',
    props: {
        title: {
            text: String,
            default: "<<Text goes here>>",
        },
        type: {
            text: String,
            default: "success",
        }
    },
    expose: ['show', 'hide'],
    setup(props) {

        let toastEle = Vue.ref(null);
        let thisToastObj;

        Vue.onMounted(() => {
            thisToastObj = new bootstrap.Toast(toastEle.value);
        });

        const show = () => {
            thisToastObj.show();
        }

        const hide = () => {
            thisToastObj.hide();
        }

        const backgroundColor = Vue.computed(() => {
            return props.type == "success" ? "bg-primary"  : "bg-danger"
        })

        return { show, hide, toastEle, backgroundColor };

    },
    template: `
    <div class="toast align-items-center text-white border-0 " :class="backgroundColor" role="alert" aria-live="assertive" aria-atomic="true" ref="toastEle">
        <div class="d-flex">
            <div class="toast-body">
            <slot name="body"></slot>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>`
}
