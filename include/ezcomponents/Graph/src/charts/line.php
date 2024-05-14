<?php
/**
 * File containing the ezcGraphLineChart class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class for line charts. Can make use of an unlimited amount of datasets and 
 * will display them as lines by default.
 * X axis:
 *  - Labeled axis
 *  - Centered axis label renderer
 * Y axis:
 *  - Numeric axis
 *  - Exact axis label renderer
 *
 * <code>
 *  // Create a new line chart
 *  $chart = new ezcGraphLineChart();
 *
 *  // Add data to line chart
 *  $chart->data['sample dataset'] = new ezcGraphArrayDataSet(
 *      array(
 *          '100' => 1.2,
 *          '200' => 43.2,
 *          '300' => -34.14,
 *          '350' => 65,
 *          '400' => 123,
 *      )   
 *  );
 *
 *  // Render chart with default 2d renderer and default SVG driver
 *  $chart->render( 500, 200, 'line_chart.svg' );
 * </code>
 *
 * Each chart consists of several chart elements which represents logical 
 * parts of the chart and can be formatted independently. The line chart
 * consists of:
 *  - title ( {@link ezcGraphChartElementText} )
 *  - legend ( {@link ezcGraphChartElementLegend} )
 *  - background ( {@link ezcGraphChartElementBackground} )
 *  - xAxis ( {@link ezcGraphChartElementLabeledAxis} )
 *  - yAxis ( {@link ezcGraphChartElementNumericAxis} )
 *
 * The type of the axis may be changed and all elements can be configured by
 * accessing them as properties of the chart:
 *
 * <code>
 *  $chart->legend->position = ezcGraph::RIGHT;
 * </code>
 *
 * The chart itself also offers several options to configure the appearance.
 * The extended configure options are available in 
 * {@link ezcGraphLineChartOptions} extending the {@link ezcGraphChartOptions}.
 *
 * @property ezcGraphLineChartOptions $options
 *           Chart options class
 *
 * @version 1.3
 * @package Graph
 * @mainclass
 */
class ezcGraphLineChart extends ezcGraphChart
{
    /**
     * Array with additional axis for the chart
     * 
     * @var ezcGraphAxisContainer
     */
    protected $additionalAxis;

    /**
     * Constructor
     * 
     * @param array $options Default option array
     * @return void
     * @ignore
     */
    public function __construct( array $options = array() )
    {
        $this->additionalAxis = new ezcGraphAxisContainer( $this );

        $this->options = new ezcGraphLineChartOptions( $options );
        $this->options->highlightFont = $this->options->font;

        parent::__construct();

        $this->addElement( 'xAxis', new ezcGraphChartElementLabeledAxis() );
        $this->elements['xAxis']->position = ezcGraph::LEFT;

        $this->addElement( 'yAxis', new ezcGraphChartElementNumericAxis() );
        $this->elements['yAxis']->position = ezcGraph::BOTTOM;
    }

    /**
     * __get 
     * 
     * @param mixed $propertyName 
     * @throws ezcBasePropertyNotFoundException
     *          If a the value for the property options is not an instance of
     * @return mixed
     * @ignore
     */
    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'additionalAxis':
                return $this->additionalAxis;
        }

        return parent::__get( $propertyName );
    }

    /**
     * Options write access
     * 
     * @throws ezcBasePropertyNotFoundException
     *          If Option could not be found
     * @throws ezcBaseValueException
     *          If value is out of range
     * @param mixed $propertyName   Option name
     * @param mixed $propertyValue  Option value;
     * @return mixed
     * @ignore
     */
    public function __set( $propertyName, $propertyValue ) 
    {
        switch ( $propertyName ) {
            case 'xAxis':
                if ( $propertyValue instanceof ezcGraphChartElementAxis )
                {
                    $this->addElement( 'xAxis', $propertyValue );
                    $this->elements['xAxis']->position = ezcGraph::LEFT;
                }
                else
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'ezcGraphChartElementAxis' );
                }
                break;
            case 'yAxis':
                if ( $propertyValue instanceof ezcGraphChartElementAxis )
                {
                    $this->addElement( 'yAxis', $propertyValue );
                    $this->elements['yAxis']->position = ezcGraph::BOTTOM;
                }
                else
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'ezcGraphChartElementAxis' );
                }
                break;
            default:
                parent::__set( $propertyName, $propertyValue );
        }
    }

    /**
     * Set colors and border for this element
     * 
     * @param ezcGraphPalette $palette Palette
     * @return void
     */
    public function setFromPalette( ezcGraphPalette $palette )
    {
        foreach ( $this->additionalAxis as $element )
        {
            $element->setFromPalette( $palette );
        }

        parent::setFromPalette( $palette );
    }

    /**
     * Render the assigned data
     *
     * Will renderer all charts data in the remaining boundings after drawing 
     * all other chart elements. The data will be rendered depending on the 
     * settings in the dataset.
     * 
     * @param ezcGraphRenderer $renderer Renderer
     * @param ezcGraphBoundings $boundings Remaining boundings
     * @return void
     */
    protected function renderData( ezcGraphRenderer $renderer, ezcGraphBoundings $boundings )
    {
        // Apply axis space
        $xAxisSpace = ( $boundings->x1 - $boundings->x0 ) * $this->yAxis->axisSpace;
        $yAxisSpace = ( $boundings->y1 - $boundings->y0 ) * $this->xAxis->axisSpace;

        $boundings->x0 += $xAxisSpace;
        $boundings->x1 -= $xAxisSpace;

        $boundings->y0 += $yAxisSpace;
        $boundings->y1 -= $yAxisSpace;

        $yAxisNullPosition = $this->elements['yAxis']->getCoordinate( false );

        // Initialize counters
        $nr = array();
        $count = array();

        foreach ( $this->data as $data )
        {
            if ( !isset( $nr[$data->displayType->default] ) )
            {
                $nr[$data->displayType->default] = 0;
                $count[$data->displayType->default] = 0;
            }

            $nr[$data->displayType->default]++;
            $count[$data->displayType->default]++;
        }

        $checkedRegularSteps = false;

        // Display data
        foreach ( $this->data as $datasetName => $data )
        {
            --$nr[$data->displayType->default];

            // Check which axis should be used
            $xAxis = ( $data->xAxis->default ? $data->xAxis->default: $this->elements['xAxis'] );
            $yAxis = ( $data->yAxis->default ? $data->yAxis->default: $this->elements['yAxis'] );

            // Determine fill color for dataset
            if ( $this->options->fillLines !== false )
            {
                $fillColor = clone $data->color->default;
                $fillColor->alpha = (int) round( ( 255 - $fillColor->alpha ) * ( $this->options->fillLines / 255 ) );
            }
            else
            {
                $fillColor = null;
            }

            // Ensure regular steps on axis when used with bar charts and
            // precalculate some values use to render bar charts
            //
            // Called only once and only when bars should be rendered
            if ( ( $checkedRegularSteps === false ) &&
                 ( $data->displayType->default === ezcGraph::BAR ) )
            {
                $steps = $xAxis->getSteps();

                $stepWidth = null;
                foreach ( $steps as $step )
                {
                    if ( $stepWidth === null )
                    {
                        $stepWidth = $step->width;
                    } 
                    elseif ( $step->width !== $stepWidth )
                    {
                        throw new ezcGraphUnregularStepsException();
                    }
                }

                $step = reset( $steps );
                if ( count( $step->childs ) )
                {
                    // Keep this for BC reasons
                    $barCount = ( $xAxis->getMajorStepCount() + 1 ) * ( $xAxis->getMinorStepCount() - 1 );
                    $stepWidth = 1 / $barCount;
                }

                $checkedRegularSteps = true;
                $width = $xAxis->axisLabelRenderer->modifyChartDataPosition( 
                    $yAxis->axisLabelRenderer->modifyChartDataPosition(
                        new ezcGraphCoordinate(
                            ( $boundings->x1 - $boundings->x0 ) * $stepWidth,
                            0 
                        )
                    )
                )->x;
            }

            // Draw lines for dataset
            $lastPoint = false;
            foreach ( $data as $key => $value )
            {
                // Calculate point in chart
                $point = $xAxis->axisLabelRenderer->modifyChartDataPosition( 
                    $yAxis->axisLabelRenderer->modifyChartDataPosition(
                        new ezcGraphCoordinate( 
                            $xAxis->getCoordinate( $key ),
                            $yAxis->getCoordinate( $value )
                        )
                    )
                );

                // Render depending on display type of dataset
                switch ( true )
                {
                    case $data->displayType->default === ezcGraph::LINE:
                        $renderer->drawDataLine(
                            $boundings,
                            new ezcGraphContext( $datasetName, $key, $data->url[$key] ),
                            $data->color->default,
                            ( $lastPoint === false ? $point : $lastPoint ),
                            $point,
                            $nr[$data->displayType->default],
                            $count[$data->displayType->default],
                            $data->symbol[$key],
                            $data->color[$key],
                            $fillColor,
                            $yAxisNullPosition,
                            ( $data->lineThickness->default ? $data->lineThickness->default : $this->options->lineThickness )
                        );
                        break;
                    case ( $data->displayType->default === ezcGraph::BAR ) &&
                         $this->options->stackBars :
                        // Check if a bar has already been stacked
                        if ( !isset( $stackedValue[(int) ( $point->x * 10000 )][(int) $value > 0] ) )
                        {
                            $start = new ezcGraphCoordinate(
                                $point->x,
                                $yAxisNullPosition
                            );

                            $stackedValue[(int) ( $point->x * 10000 )][(int) $value > 0] = $value;
                        }
                        else
                        {
                            $start = $xAxis->axisLabelRenderer->modifyChartDataPosition( 
                                $yAxis->axisLabelRenderer->modifyChartDataPosition(
                                    new ezcGraphCoordinate( 
                                        $xAxis->getCoordinate( $key ),
                                        $yAxis->getCoordinate( $stackedValue[(int) ( $point->x * 10000 )][(int) $value > 0] )
                                    )
                                )
                            );

                            $point = $xAxis->axisLabelRenderer->modifyChartDataPosition( 
                                $yAxis->axisLabelRenderer->modifyChartDataPosition(
                                    new ezcGraphCoordinate( 
                                        $xAxis->getCoordinate( $key ),
                                        $yAxis->getCoordinate( $stackedValue[(int) ( $point->x * 10000 )][(int) $value > 0] += $value )
                                    )
                                )
                            );
                        }

                        // Force one symbol for each stacked bar
                        if ( !isset( $stackedSymbol[(int) ( $point->x * 10000 )] ) )
                        {
                            $stackedSymbol[(int) ( $point->x * 10000 )] = $data->symbol[$key];
                        }

                        // Store stacked value for next iteration
                        $stacked[(int) ( $point->x * 10000 )][$point->y / abs( $point->y )] = $point;

                        $renderer->drawStackedBar(
                            $boundings,
                            new ezcGraphContext( $datasetName, $key, $data->url[$key] ),
                            $data->color->default,
                            $start,
                            $point,
                            $width,
                            $stackedSymbol[(int) ( $point->x * 10000 )],
                            $yAxisNullPosition
                        );
                        break;
                    case $data->displayType->default === ezcGraph::BAR:
                        $renderer->drawBar(
                            $boundings,
                            new ezcGraphContext( $datasetName, $key, $data->url[$key] ),
                            $data->color[$key],
                            $point,
                            $width,
                            $nr[$data->displayType->default],
                            $count[$data->displayType->default],
                            $data->symbol[$key],
                            $yAxisNullPosition
                        );
                        break;
                    default:
                        throw new ezcGraphInvalidDisplayTypeException( $data->displayType->default );
                        break;
                }

                // Render highlight string if requested
                if ( $data->highlight[$key] )
                {
                    $renderer->drawDataHighlightText(
                        $boundings,
                        new ezcGraphContext( $datasetName, $key, $data->url[$key] ),
                        $point,
                        $yAxisNullPosition,
                        $nr[$data->displayType->default],
                        $count[$data->displayType->default],
                        $this->options->highlightFont,
                        ( $data->highlightValue[$key] ? $data->highlightValue[$key] : $value ),
                        $this->options->highlightSize + $this->options->highlightFont->padding * 2,
                        ( $this->options->highlightLines ? $data->color[$key] : null )
                    );
                }
    
                // Store last point, used to connect lines in line chart.
                $lastPoint = $point;
            }
        }
    }

    /**
     * Returns the default display type of the current chart type.
     * 
     * @return int Display type
     */
    public function getDefaultDisplayType()
    {
        return ezcGraph::LINE;
    }

    /**
     * Check if renderer supports features requested by some special chart
     * options.
     * 
     * @throws ezcBaseValueException
     *         If some feature is not supported
     *
     * @return void
     */
    protected function checkRenderer()
    {
        // When stacked bars are enabled, check if renderer supports them
        if ( $this->options->stackBars )
        {
            if ( !$this->renderer instanceof ezcGraphStackedBarsRenderer )
            {
                throw new ezcBaseValueException( 'renderer', $this->renderer, 'ezcGraphStackedBarsRenderer' );
            }
        }
    }

    /**
     * Aggregate and calculate value boundings on axis.
     * 
     * @return void
     */
    protected function setAxisValues()
    {
        // Virtual data set build for agrregated values sums for bar charts
        $virtualBarSumDataSet = array( array(), array() );

        // Calculate axis scaling and labeling
        foreach ( $this->data as $dataset )
        {
            $nr = 0;
            $labels = array();
            $values = array();
            foreach ( $dataset as $label => $value )
            {
                $labels[] = $label;
                $values[] = $value;

                // Build sum of all bars
                if ( $this->options->stackBars &&
                     ( $dataset->displayType->default === ezcGraph::BAR ) )
                {
                    if ( !isset( $virtualBarSumDataSet[(int) $value >= 0][$nr] ) )
                    {
                        $virtualBarSumDataSet[(int) $value >= 0][$nr++] = $value;
                    }
                    else
                    {
                        $virtualBarSumDataSet[(int) $value >= 0][$nr++] += $value;
                    }
                }
            }

            // Check if data has been associated with another custom axis, use
            // default axis otherwise.
            if ( $dataset->xAxis->default )
            {
                $dataset->xAxis->default->addData( $labels );
            }
            else
            {
                $this->elements['xAxis']->addData( $labels );
            }

            if ( $dataset->yAxis->default )
            {
                $dataset->yAxis->default->addData( $values );
            }
            else
            {
                $this->elements['yAxis']->addData( $values );
            }
        }

        // Also use stacked bar values as base for y axis value span
        // calculation
        if ( $this->options->stackBars )
        {
            $this->elements['yAxis']->addData( $virtualBarSumDataSet[0] );
            $this->elements['yAxis']->addData( $virtualBarSumDataSet[1] );
        }

        // There should always be something assigned to the main x and y axis.
        if ( !$this->elements['xAxis']->initialized ||
             !$this->elements['yAxis']->initialized )
        {
            throw new ezcGraphNoDataException();
        }

        // Calculate boundings from assigned data
        $this->elements['xAxis']->calculateAxisBoundings();
        $this->elements['yAxis']->calculateAxisBoundings();
    }

    /**
     * Renders the basic elements of this chart type
     * 
     * @param int $width 
     * @param int $height 
     * @return void
     */
    protected function renderElements( $width, $height )
    {
        if ( !count( $this->data ) )
        {
            throw new ezcGraphNoDataException();
        }

        // Check if renderer supports requested features
        $this->checkRenderer();

        // Set values form datasets on axis to calculate correct spans
        $this->setAxisValues();

        // Generate legend
        $this->elements['legend']->generateFromDataSets( $this->data );

        // Get boundings from parameters
        $this->options->width = $width;
        $this->options->height = $height;

        // Set image properties in driver
        $this->driver->options->width = $width;
        $this->driver->options->height = $height;

        // Render subelements
        $boundings = new ezcGraphBoundings();
        $boundings->x1 = $this->options->width;
        $boundings->y1 = $this->options->height;

        $boundings = $this->elements['xAxis']->axisLabelRenderer->modifyChartBoundings( 
            $this->elements['yAxis']->axisLabelRenderer->modifyChartBoundings(
                $boundings, new ezcGraphCoordinate( 1, 0 )
            ), new ezcGraphCoordinate( -1, 0 )
        );

        // Render subelements
        foreach ( $this->elements as $name => $element )
        {
            // Skip element, if it should not get rendered
            if ( $this->renderElement[$name] === false )
            {
                continue;
            }

            // Special settings for special elements
            switch ( $name )
            {
                case 'xAxis':
                    // get Position of 0 on the Y-axis for orientation of the x-axis
                    $element->nullPosition = $this->elements['yAxis']->getCoordinate( false );
                    break;
                case 'yAxis':
                    // get Position of 0 on the X-axis for orientation of the y-axis
                    $element->nullPosition = $this->elements['xAxis']->getCoordinate( false );
                    break;
            }

            $this->driver->options->font = $element->font;
            $boundings = $element->render( $this->renderer, $boundings );
        }

        // Render additional axis
        foreach ( $this->additionalAxis as $element )
        {
            if ( $element->initialized )
            {
                // Calculate all required step sizes if values has been
                // assigned to axis.
                $element->calculateAxisBoundings();
            }
            else
            {
                // Do not render any axis labels, if no values were assigned
                // and no step sizes were defined.
                $element->axisLabelRenderer = new ezcGraphAxisNoLabelRenderer();
            }

            $this->driver->options->font = $element->font;
            $element->nullPosition = $element->chartPosition;
            $boundings = $element->render( $this->renderer, $boundings );
        }

        // Render graph
        $this->renderData( $this->renderer, $boundings );
    }

    /**
     * Render the line chart
     *
     * Renders the chart into a file or stream. The width and height are 
     * needed to specify the dimensions of the resulting image. For direct
     * output use 'php://stdout' as output file.
     * 
     * @param int $width Image width
     * @param int $height Image height
     * @param string $file Output file
     * @apichange
     * @return void
     */
    public function render( $width, $height, $file = null )
    {
        $this->renderElements( $width, $height );

        if ( !empty( $file ) )
        {
            $this->renderer->render( $file );
        }

        $this->renderedFile = $file;
    }

    /**
     * Renders this chart to direct output
     * 
     * Does the same as ezcGraphChart::render(), but renders directly to 
     * output and not into a file.
     *
     * @param int $width
     * @param int $height
     * @apichange 
     * @return void
     */
    public function renderToOutput( $width, $height )
    {
        // @TODO: merge this function with render an deprecate ommit of third 
        // argument in render() when API break is possible
        $this->renderElements( $width, $height );
        $this->renderer->render( null );
    }
}
?>
