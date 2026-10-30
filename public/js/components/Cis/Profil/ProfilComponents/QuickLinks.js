export default {
  name: "QuickLinks",
  data() {
    return {};
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    links: {
      type: Array,
      required: true,
    },
  },
  template: `
<div class="card">
    <div class="card-header">
        {{title}}
    </div>
    <div class="card-body">
        <div class="d-flex flex-row align-items-center gap-3 flex-wrap">
            <!-- server links carry a url, client links an action -->
            <component v-for="link in links" :is="link.url ? 'a' : 'div'" :href="link.url" @click="link.action?.()" :type="link.url ? null : 'button'" class="d-flex flex-row gap-2 px-3 py-2 border fhc-primary text-decoration-none">
                <div><i class="fa" :class="link.icon"></i></div>
                {{ $p.t(link.phrase) }}
                <div><i class="fa fa-arrow-up-right-from-square" style="color:var(--fhc-light) !important"></i></div>
            </component>
        </div>
    </div>
</div>`,
};
