export default {
    data: function() {
      return {          
          availHeight: 0,
          topheight: 0,
          bottomheight: 0,
          mousePosY: 0, 
          resize: false,
          vsplitter: null,
          vsplitterOffset: 0,
          selfOffsetTop: 0
      };  
    },
    template: `
    <div ref="verticalsplit">
        <div ref="toppanel" class="verticalsplitted" 
             :style="{height: this.topheightcss}">
            <slot name="top">
                <p>Top Panel</p>
            </slot>
        </div>
        <div ref="vsplitter" class="verticalsplitter" 
             :class="this.topOrBottomClass" @mousedown="this.dragStart">
            <div class="splitactions" :class="this.topOrBottomClass">
                <span @click="this.collapseTop" class="splitaction">
                    <i class="fas fa-angle-up"></i>
                </span>
                <span @dblclick="this.showBoth" class="splitaction resize">
                    <i class="fas fa-grip-horizontal"></i>
                </span>
                <span @click="this.collapseBottom" class="splitaction">
                    <i class="fas fa-angle-down"></i>
                </span>
            </div>
        </div>
        <div ref="bottompanel" class="verticalsplitted"
             :style="{height: this.bottomheightcss}">
            <slot name="bottom">
                <p/>
            </slot>
        </div>
    </div>
    `,
    mounted: function() {
        this.calcHeights();
        this.trackVerticalSplitterOffsetTop();
        window.addEventListener('resize', this.calcHeights);
    },
    updated: function() {
        this.trackVerticalSplitterOffsetTop();
    },
    methods: {
        calcHeights: function() {
            var windowheight = window.innerHeight;
            var oldavailHeight = this.availHeight;
            this.selfOffsetTop = this.$refs.verticalsplit.offsetTop;
            this.availHeight = windowheight - this.selfOffsetTop - this.$refs.vsplitter.offsetHeight;
            if( (this.topheight === 0 && this.bottomheight === 0) || oldavailHeight === 0 ) {
                this.topheight = Math.floor(this.availHeight/2);
            } else {
                this.topheight = Math.floor( ((((this.topheight * 100) / oldavailHeight) / 100) * this.availHeight) );
            }
            this.bottomheight = this.availHeight - this.topheight;
        },
        collapseTop: function() {
            this.calcHeights();
            this.topheight = 0;
            this.bottomheight = this.availHeight;
        },
        collapseBottom: function() {
            this.calcHeights();
            this.topheight = this.availHeight;
            this.bottomheight = 0;
        },
        showBoth: function() {
            this.topheight = Math.floor(this.availHeight/2);
            this.bottomheight = Math.floor(this.availHeight/2);
        },
        isCollapsed: function() {
            if( this.topheight === 0 ) {
                return 'top';
            } else if ( this.bottomheight === 0 ) {
                return 'bottom';
            } else {
                return false;
            }
        },
        dragStart: function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.addEventListener('mouseup', this.dragEnd);
            window.addEventListener('mousemove', this.drag);
            this.resize = true;
            this.mousePosY = e.clientY;
        },
        drag: function(e) {
            if( !this.resize ) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            var offsetY = e.clientY - this.mousePosY;
            this.topheight = this.topheight + offsetY;
            if( this.topheight < 0 ) {
                this.topheight = 0;
            }
            if( this.topheight > this.availHeight ) {
                this.topheight = this.availHeight;
            }
            this.bottomheight = this.availHeight - this.topheight;
            this.mousePosY = e.clientY;
        },
        dragEnd: function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.removeEventListener('mousemove', this.drag);
            window.removeEventListener('mouseup', this.dragEnd);
            this.resize = false;
            this.mousePosY = e.clientY;
        },
        trackVerticalSplitterOffsetTop: function() {
            this.vsplitterOffset = this.$refs.vsplitter.offsetTop;
        }
    },
    computed: {
        topOrBottomClass: function() {
            return ((this.vsplitterOffset - this.selfOffsetTop) <= Math.floor(this.availHeight/2))
                        ? 'top'
                        : 'bottom';
        },
        topheightcss: function() {
            return this.topheight + 'px';
        },
        bottomheightcss: function() {
            return this.bottomheight + 'px';
        }
    }
};