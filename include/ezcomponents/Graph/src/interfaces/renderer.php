<?php
/**
 * File containing the abstract ezcGraphRenderer class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Abstract class to transform the basic chart components. To be extended by
 * three- and twodimensional renderers.
 *
 * @version 1.3
 * @package Graph
 */
abstract class ezcGraphRenderer
{

    /**
     * Driver used to render results
     * 
     * @var ezcGraphDriver
     */
    protected $driver;

    /**
     * Axis space used for the x axis
     * 
     * @var float
     */
    protected $xAxisSpace = false;
    
    /**
     * Axis space used for the y axis
     * 
     * @var float
     */
    protected $yAxisSpace = false;

    /**
     * Context sensitive references to chart elements to use for referencing 
     * image elements depending on the output driver, like image maps, etc.
     * 
     * @var array
     */
    protected $elements = array();

    /**
     * Set renderers driver
     * 
     * @param ezcGraphDriver $driver Output driver
     * @return void
     */
    public function setDriver( ezcGraphDriver $driver )
    {
        $this->driver = $driver;
    }

    /**
     * Adds a element reference for context
     * 
     * @param ezcGraphContext $context Dataoint context
     * @param mixed $reference Driver dependant reference
     * @return void
     */
    protected function addElementReference( ezcGraphContext $context, $reference )
    {
        $this->elements['data'][$context->dataset][$context->datapoint][] = $reference;
    }

    /**
     * Return all chart element references
     * 
     * @return array chart element references
     */
    public function getElementReferences()
    {
        return $this->elements;
    }

    /**
     * __get 
     * 
     * @param string $propertyName 
     * @throws ezcBasePropertyNotFoundException
     *          If a the value for the property options is not an instance of
     * @return mixed
     * @ignore
     */
    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'xAxisSpace':
            case 'yAxisSpace':
                return $this->$propertyName;
            case 'elements':
                return $this->elements;
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
    }

    /**
     * Draw pie segment
     *
     * Draws a single pie segment
     * 
     * @param ezcGraphBoundings $boundings Chart boundings
     * @param ezcGraphContext $context Context of call
     * @param ezcGraphColor $color Color of pie segment
     * @param float $startAngle Start angle
     * @param float $endAngle End angle
     * @param mixed $label Label of pie segment
     * @param bool $moveOut Move out from middle for hilighting
     * @return void
     */
    abstract public function drawPieSegment(
        ezcGraphBoundings $boundings,
        ezcGraphContext $context,
        ezcGraphColor $color,
        $startAngle = .0,
        $endAngle = 360.,
        $label = false,
        $moveOut = false
    );
    
    /**
     * Draw bar
     *
     * Draws a bar as a data element in a line chart
     * 
     * @param ezcGraphBoundings $boundings Chart boundings
     * @param ezcGraphContext $context Context of call
     * @param ezcGraphColor $color Color of line
     * @param ezcGraphCoordinate $position Position of data point
     * @param float $stepSize Space which can be used for bars
     * @param int $dataNumber Number of dataset
     * @param int $dataCount Count of datasets in chart
     * @param int $symbol Symbol to draw for line
     * @param float $axisPosition Position of axis for drawing filled lines
     * @return void
     */
    abstract public function drawBar(
        ezcGraphBoundings $boundings,
        ezcGraphContext $context,
        ezcGraphColor $color,
        ezcGraphCoordinate $position,
        $stepSize,
        $dataNumber = 1,
        $dataCount = 1,
        $symbol = ezcGraph::NO_SYMBOL,
        $axisPosition = 0.
    );
    
    /**
     * Draw data line
     *
     * Draws a line as a data element in a line chart
     * 
     * @param ezcGraphBoundings $boundings Chart boundings
     * @param ezcGraphContext $context Context of call
     * @param ezcGraphColor $color Color of line
     * @param ezcGraphCoordinate $start Starting point
     * @param ezcGraphCoordinate $end Ending point
     * @param int $dataNumber Number of dataset
     * @param int $dataCount Count of datasets in chart
     * @param int $symbol Symbol to draw for line
     * @param ezcGraphColor $symbolColor Color of the symbol, defaults to linecolor
     * @param ezcGraphColor $fillColor Color to fill line with
     * @param float $axisPosition Position of axis for drawing filled lines
     * @param float $thickness Line thickness
     * @return void
     */
    abstract public function drawDataLine(
        ezcGraphBoundings $boundings,
        ezcGraphContext $context,
        ezcGraphColor $color,
        ezcGraphCoordinate $start,
        ezcGraphCoordinate $end,
        $dataNumber = 1,
        $dataCount = 1,
        $symbol = ezcGraph::NO_SYMBOL,
        ezcGraphColor $symbolColor = null,
        ezcGraphColor $fillColor = null,
        $axisPosition = 0.,
        $thickness = 1.
    );

    /**
     * Draws a highlight textbox for a datapoint.
     *
     * A highlight textbox for line and bar charts means a box with the current 
     * value in the graph.
     * 
     * @param ezcGraphBoundings $boundings Chart boundings
     * @param ezcGraphContext $context Context of call
     * @param ezcGraphCoordinate $end Ending point
     * @param float $axisPosition Position of axis for drawing filled lines
     * @param int $dataNumber Number of dataset
     * @param int $dataCount Count of datasets in chart
     * @param ezcGraphFontOptions $font Font used for highlight string
     * @param string $text Acutual value
     * @param int $size Size of highlight text
     * @param ezcGraphColor $markLines
     * @return void
     */
    abstract public function drawDataHighlightText(
        ezcGraphBoundings $boundings,
        ezcGraphContext $context,
        ezcGraphCoordinate $end,
        $axisPosition = 0.,
        $dataNumber = 1,
        $dataCount = 1,
        ezcGraphFontOptions $font,
        $text,
        $size,
        ezcGraphColor $markLines = null
    );
    
    /**
     * Draw legend
     *
     * Will draw a legend in the bounding box
     * 
     * @param ezcGraphBoundings $boundings Bounding of legend
     * @param ezcGraphChartElementLegend $legend Legend to draw
     * @param int $type Type of legend: Protrait or landscape
     * @return void
     */
    abstract public function drawLegend(
        ezcGraphBoundings $boundings,
        ezcGraphChartElementLegend $legend,
        $type = ezcGraph::VERTICAL
    );
    
    /**
     * Draw box
     *
     * Box are wrapping each major chart element and draw border, background
     * and title to each chart element.
     *
     * Optionally a padding and margin for each box can be defined.
     * 
     * @param ezcGraphBoundings $boundings Boundings of the box
     * @param ezcGraphColor $background Background color
     * @param ezcGraphColor $borderColor Border color
     * @param int $borderWidth Border width
     * @param int $margin Margin
     * @param int $padding Padding
     * @param mixed $title Title of the box
     * @param int $titleSize Size of title in the box
     * @return ezcGraphBoundings Remaining inner boundings
     */
    abstract public function drawBox(
        ezcGraphBoundings $boundings,
        ezcGraphColor $background = null,
        ezcGraphColor $borderColor = null,
        $borderWidth = 0,
        $margin = 0,
        $padding = 0,
        $title = false,
        $titleSize = 16 
    );
    
    /**
     * Draw text
     *
     * Draws the provided text in the boundings
     * 
     * @param ezcGraphBoundings $boundings Boundings of text
     * @param string $text Text
     * @param int $align Alignement of text
     * @param ezcGraphRotation $rotation
     * @return void
     */
    abstract public function drawText(
        ezcGraphBoundings $boundings,
        $text,
        $align = ezcGraph::LEFT,
        ezcGraphRotation $rotation = null
    );

    /**
     * Draw axis
     *
     * Draws an axis form the provided start point to the end point. A specific 
     * angle of the axis is not required.
     *
     * For the labeleing of the axis a sorted array with major steps and an 
     * array with minor steps is expected, which are build like this:
     *  array(
     *      array(
     *          'position' => (float),
     *          'label' => (string),
     *      )
     *  )
     * where the label is optional.
     *
     * The label renderer class defines how the labels are rendered. For more
     * documentation on this topic have a look at the basic label renderer 
     * class.
     *
     * Additionally it can be specified if a major and minor grid are rendered 
     * by defining a color for them. Teh axis label is used to add a caption 
     * for the axis.
     * 
     * @param ezcGraphBoundings $boundings Boundings of axis
     * @param ezcGraphCoordinate $start Start point of axis
     * @param ezcGraphCoordinate $end Endpoint of axis
     * @param ezcGraphChartElementAxis $axis Axis to render
     * @param ezcGraphAxisLabelRenderer $labelClass Used label renderer
     * @return void
     */
    abstract public function drawAxis(
        ezcGraphBoundings $boundings,
        ezcGraphCoordinate $start,
        ezcGraphCoordinate $end,
        ezcGraphChartElementAxis $axis,
        ezcGraphAxisLabelRenderer $labelClass = null
    );

    /**
     * Draw background image
     *
     * Draws a background image at the defined position. If repeat is set the
     * background image will be repeated like any texture.
     * 
     * @param ezcGraphBoundings $boundings Boundings for the background image
     * @param string $file Filename of background image
     * @param int $position Position of background image
     * @param int $repeat Type of repetition
     * @return void
     */
    abstract public function drawBackgroundImage(
        ezcGraphBoundings $boundings,
        $file,
        $position = 48, // ezcGraph::CENTER | ezcGraph::MIDDLE
        $repeat = ezcGraph::NO_REPEAT
    );
    
    /**
     * Draw Symbol
     *
     * Draws a single symbol defined by the symbol constants in ezcGraph. for
     * NO_SYMBOL a rect will be drawn.
     * 
     * @param ezcGraphBoundings $boundings Boundings of symbol
     * @param ezcGraphColor $color Color of symbol
     * @param int $symbol Type of symbol
     * @return void
     */
    public function drawSymbol(
        ezcGraphBoundings $boundings,
        ezcGraphColor $color,
        $symbol = ezcGraph::NO_SYMBOL )
    {
        switch ( $symbol )
        {
            case ezcGraph::NO_SYMBOL:
                $return = $this->driver->drawPolygon(
                    array(
                        new ezcGraphCoordinate( $boundings->x0, $boundings->y0 ),
                        new ezcGraphCoordinate( $boundings->x1, $boundings->y0 ),
                        new ezcGraphCoordinate( $boundings->x1, $boundings->y1 ),
                        new ezcGraphCoordinate( $boundings->x0, $boundings->y1 ),
                    ),
                    $color,
                    true
                );

                // Draw optional gleam
                if ( $this->options->legendSymbolGleam !== false )
                {
                    $this->driver->drawPolygon(
                        array(
                            $topLeft = new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x1 - ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                            $bottomRight = new ezcGraphCoordinate( 
                                $boundings->x1 - ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y1 - ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y1 - ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                        ),
                        new ezcGraphLinearGradient(
                            $bottomRight,
                            $topLeft,
                            $color->darken( -$this->options->legendSymbolGleam ),
                            $color->darken( $this->options->legendSymbolGleam )
                        ),
                        true
                    );
                }
                return $return;
            case ezcGraph::DIAMOND:
                $return = $this->driver->drawPolygon(
                    array(
                        new ezcGraphCoordinate( 
                            $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                            $boundings->y0 
                        ),
                        new ezcGraphCoordinate( 
                            $boundings->x1,
                            $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                        ),
                        new ezcGraphCoordinate( 
                            $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                            $boundings->y1 
                        ),
                        new ezcGraphCoordinate( 
                            $boundings->x0,
                            $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                        ),
                    ),
                    $color,
                    true
                );

                // Draw optional gleam
                if ( $this->options->legendSymbolGleam !== false )
                {
                    $this->driver->drawPolygon(
                        array(
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x1 - ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                                $boundings->y1 - ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize 
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                            ),
                        ),
                        new ezcGraphLinearGradient(
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * 0.353553391, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * 0.353553391
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * ( 1 - 0.353553391 ), 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * ( 1 - 0.353553391 )
                            ),
                            $color->darken( -$this->options->legendSymbolGleam ),
                            $color->darken( $this->options->legendSymbolGleam )
                        ),
                        true
                    );
                }
                return $return;
            case ezcGraph::BULLET:
                $return = $this->driver->drawCircle(
                    new ezcGraphCoordinate( 
                        $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                        $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                    ),
                    $boundings->x1 - $boundings->x0,
                    $boundings->y1 - $boundings->y0,
                    $color,
                    true
                );

                // Draw optional gleam
                if ( $this->options->legendSymbolGleam !== false )
                {
                    $this->driver->drawCircle(
                        new ezcGraphCoordinate( 
                            $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                            $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                        ),
                        ( $boundings->x1 - $boundings->x0 ) * $this->options->legendSymbolGleamSize,
                        ( $boundings->y1 - $boundings->y0 ) * $this->options->legendSymbolGleamSize,
                        new ezcGraphLinearGradient(
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * 0.292893219, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * 0.292893219
                            ),
                            new ezcGraphCoordinate( 
                                $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) * 0.707106781, 
                                $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) * 0.707106781
                            ),
                            $color->darken( -$this->options->legendSymbolGleam ),
                            $color->darken( $this->options->legendSymbolGleam )
                        ),
                        true
                    );
                }
                return $return;
            case ezcGraph::CIRCLE:
                return $this->driver->drawCircle(
                    new ezcGraphCoordinate( 
                        $boundings->x0 + ( $boundings->x1 - $boundings->x0 ) / 2, 
                        $boundings->y0 + ( $boundings->y1 - $boundings->y0 ) / 2
                    ),
                    $boundings->x1 - $boundings->x0,
                    $boundings->y1 - $boundings->y0,
                    $color,
                    false
                );
        }
    }

    /**
     * Finish rendering
     *
     * Method is called before the final image is renderer, so that finishing
     * operations can be performed here.
     * 
     * @return void
     */
    abstract protected function finish();

    /**
     * Reset renderer properties
     *
     * Reset all renderer properties, which were calculated during the
     * rendering process, to offer a clean environment for rerendering.
     * 
     * @return void
     */
    protected function resetRenderer()
    {
        $this->xAxisSpace = false;
        $this->yAxisSpace = false;

        // Reset driver, maintaining its configuration
        $driverClass           = get_class( $this->driver );
        $driverOptions         = $this->driver->options;
        $this->driver          = new $driverClass();
        $this->driver->options = $driverOptions;
    }

    /**
     * Finally renders the image 
     * 
     * @param string $file Filename of destination file
     * @return void
     */
    public function render( $file = null )
    {
        $this->finish();

        if ( $file === null )
        {
            $this->driver->renderToOutput();
        }
        else
        {
            $this->driver->render( $file );
        }

        $this->resetRenderer();
    }
}
?>
