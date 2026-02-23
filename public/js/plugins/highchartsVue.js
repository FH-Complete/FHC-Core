function doCopy (copy, original, copyArray) {
    // Callback function to iterate on array or object elements
    function callback (value, key) {
      // Copy the contents of objects
      if (Highcharts.isObject(value, !copyArray) &&
        !Highcharts.isClass(value) &&
        !Highcharts.isDOMElement(value)
      ) {
        copy[key] = doCopy(copy[key] || Highcharts.isArray(value) ? [] : {}, value, copyArray)
      } else {
        // Primitives are copied over directly
        copy[key] = original[key]
      }
    }
  
    if (Highcharts.isArray(original)) {
      original.forEach(callback)
    } else {
      Highcharts.objectEach(original, callback)
    }
    return copy
  }
  
const copyObject = function (obj, copyArray) {
    return doCopy({}, obj, copyArray)
}

const highchartsPlugin = {

    install(app, options) {
        
        function destroyChart() {
            if (this.chart) {
                this.chart.destroy()
              }
        }

        function generateVueComponent(Highcharts, VueVersion) {
            const VUE_MAJOR = VueVersion.split('.')[0]
            const VERSION_DEPENDENT_PROPS = VUE_MAJOR < 3
                ? {
                // Fallback options for Vue v2 to keep backward compatibility.
                render: (createElement) => createElement('div', {
                    ref: 'chart'
                }),
                beforeDestroy: destroyChart
                // The new Vue's 3 syntax.
                } : {
                    render () { 
                        return Vue.h('div', { ref: 'chart' }) 
                    },
                    beforeUnmount: destroyChart
                }
    
            return {
                template: '<div ref="chart"></div>',
                props: {
                    constructorType: {
                        type: String,
                        default: 'chart'
                    },
                    options: {
                        type: Object,
                        required: true
                    },
                    callback: Function,
                    updateArgs: {
                        type: Array,
                        default: () => [true, true]
                    },
                    highcharts: {
                        type: Object
                    },
                    deepCopyOnUpdate: {
                        type: Boolean,
                        default: true
                    }
                },
                watch: {
                    options: {
                        handler (newValue) {
                        this.chart.update(copyObject(newValue, this.deepCopyOnUpdate), ...this.updateArgs)
                        },
                        deep: true
                    }
                },
                mounted () {
                    let HC = this.highcharts || Highcharts
    
                    // Check whether the chart configuration object is passed, as well as the constructor is valid.
                    if (this.options && HC[this.constructorType]) {
                        this.chart = HC[this.constructorType](
                        this.$refs.chart,
                        copyObject(this.options, true), // Always pass the deep copy when generating a chart. #80
                        this.callback ? this.callback : null
                        )
                    } else {
                        (!this.options) ? console.warn('The "options" parameter was not passed.') : console.warn(`'${this.constructorType}' constructor-type is incorrect. Sometimes this error is caused by the fact, that the corresponding module wasn't imported.`)
                    }
                },
                ...VERSION_DEPENDENT_PROPS
            }
        }

        app.component(
            options.tagName || 'highcharts',
            generateVueComponent(options.highcharts || Highcharts, Vue.version)
        )
    },

}

export default highchartsPlugin