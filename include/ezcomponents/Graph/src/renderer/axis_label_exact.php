<?php
/**
 * File containing the ezcGraphAxisExactLabelRenderer class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Renders axis labels like known from charts drawn in analysis
 *
 * <code>
 *   $chart->xAxis->axisLabelRenderer = new ezcGraphAxisExactLabelRenderer();
 * </code>
 *
 * @property bool $showLastValue
 *           Show the last value on the axis, which will be aligned different 
 *           than all other values, to not interfere with the arrow head of 
 *           the axis.
 *
 * @version 1.3
 * @package Graph
 * @mainclass
 */
class ezcGraphAxisExactLabelRenderer extends ezcGraphAxisLabelRenderer
{

    /**
     * Constructor
     * 
     * @param array $options Default option array
     * @return void
     * @ignore
     */
    public function __construct( array $options = array() )
    {
        $this->properties['showLastValue'] = true;

        parent::__construct( $options );
    }

    /**
     * __set 
     * 
     * @param mixed $propertyName 
     * @param mixed $propertyValue 
     * @throws ezcBaseValueException
     *          If a submitted parameter was out of range or type.
     * @throws ezcBasePropertyNotFoundException
     *          If a the value for the property options is not an instance of
     * @return void
     * @ignore
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'showLastValue':
                if ( !is_bool( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'bool' );
                }

                $this->properties['showLastValue'] = (bool) $propertyValue;
                break;
            default:
                return parent::__set( $propertyName, $propertyValue );
        }
    }

    /**
     * Render Axis labels
     *
     * Render labels for an axis.
     *
     * @param ezcGraphRenderer $renderer Renderer used to draw the chart
     * @param ezcGraphBoundings $boundings Boundings of the axis
     * @param ezcGraphCoordinate $start Axis starting point
     * @param ezcGraphCoordinate $end Axis ending point
     * @param ezcGraphChartElementAxis $axis Axis instance
     * @return void
     */
    public function renderLabels(
        ezcGraphRenderer $renderer,
        ezcGraphBoundings $boundings,
        ezcGraphCoordinate $start,
        ezcGraphCoordinate $end,
        ezcGraphChartElementAxis $axis )
    {
        // receive rendering parameters from axis
        $steps = $axis->getSteps();

        $axisBoundings = new ezcGraphBoundings(
            $start->x, $start->y,
            $end->x, $end->y
        );

        // Determine normalized axis direction
        $direction = new ezcGraphVector(
            $start->x - $end->x,
            $start->y - $end->y
        );
        $direction->unify();
        
        if ( $this->outerGrid )
        {
            $gridBoundings = $boundings;
        }
        else
        {
            $gridBoundings = new ezcGraphBoundings(
                $boundings->x0 + $renderer->xAxisSpace * abs( $direction->y ),
                $boundings->y0 + $renderer->yAxisSpace * abs( $direction->x ),
                $boundings->x1 - $renderer->xAxisSpace * abs( $direction->y ),
                $boundings->y1 - $renderer->yAxisSpace * abs( $direction->x )
            );
        }

        // Draw steps and grid
        foreach ( $steps as $nr => $step )
        {
            $position = new ezcGraphCoordinate(
                $start->x + ( $end->x - $start->x ) * $step->position,
                $start->y + ( $end->y - $start->y ) * $step->position
            );
            $stepSize = new ezcGraphCoordinate(
                $axisBoundings->width * $step->width,
                $axisBoundings->height * $step->width
            );

            if ( ! $step->isZero )
            {
                // major grid
                if ( $axis->majorGrid )
                {
                    $this->drawGrid( 
                        $renderer, 
                        $gridBoundings, 
                        $position,
                        $stepSize,
                        $axis->majorGrid
                    );
                }
                
                // major step
                $this->drawStep( 
                    $renderer, 
                    $position,
                    $direction, 
                    $axis->position, 
                    $this->majorStepSize, 
                    $axis->border
                );
            }

            if ( $this->showLabels )
            {
                switch ( $axis->position )
                {
                    case ezcGraph::RIGHT:
                    case ezcGraph::LEFT:
                        $labelWidth = $axisBoundings->width * 
                            $steps[$nr - $step->isLast]->width /
                            ( $this->showLastValue + 1 );
                        $labelHeight = $renderer->yAxisSpace;
                        break;

                    case ezcGraph::BOTTOM:
                    case ezcGraph::TOP:
                        $labelWidth = $renderer->xAxisSpace;
                        $labelHeight = $axisBoundings->height * 
                            $steps[$nr - $step->isLast]->width /
                            ( $this->showLastValue + 1 );
                        break;
                }

                $showLabel = true;
                switch ( true )
                {
                    case ( !$this->showLastValue && $step->isLast ):
                        // Skip last step if showLastValue is false
                        $showLabel = false;
                        break;
                    // Draw label at top left of step
                    case ( ( $axis->position === ezcGraph::BOTTOM ) &&
                           ( !$step->isLast ) ) ||
                         ( ( $axis->position === ezcGraph::TOP ) &&
                           ( $step->isLast ) ):
                        $labelBoundings = new ezcGraphBoundings(
                            $position->x - $labelWidth + $this->labelPadding,
                            $position->y - $labelHeight + $this->labelPadding,
                            $position->x - $this->labelPadding,
                            $position->y - $this->labelPadding
                        );
                        $alignement = ezcGraph::RIGHT | ezcGraph::BOTTOM;
                        break;
                    // Draw label at bottom right of step
                    case ( ( $axis->position === ezcGraph::LEFT ) &&
                           ( !$step->isLast ) ) ||
                         ( ( $axis->position === ezcGraph::RIGHT ) &&
                           ( $step->isLast ) ):
                        $labelBoundings = new ezcGraphBoundings(
                            $position->x + $this->labelPadding,
                            $position->y + $this->labelPadding,
                            $position->x + $labelWidth - $this->labelPadding,
                            $position->y + $labelHeight - $this->labelPadding
                        );
                        $alignement = ezcGraph::LEFT | ezcGraph::TOP;
                        break;
                    // Draw label at bottom left of step
                    case ( ( $axis->position === ezcGraph::TOP ) &&
                           ( !$step->isLast ) ) ||
                         ( ( $axis->position === ezcGraph::RIGHT ) &&
                           ( !$step->isLast ) ) ||
                         ( ( $axis->position === ezcGraph::BOTTOM ) &&
                           ( $step->isLast ) ) ||
                         ( ( $axis->position === ezcGraph::LEFT ) &&
                           ( $step->isLast ) ):
                        $labelBoundings = new ezcGraphBoundings(
                            $position->x - $labelWidth + $this->labelPadding,
                            $position->y + $this->labelPadding,
                            $position->x - $this->labelPadding,
                            $position->y + $labelHeight - $this->labelPadding
                        );
                        $alignement = ezcGraph::RIGHT | ezcGraph::TOP;
                        break;
                }

                if ( $showLabel )
                {
                    $renderer->drawText(
                        $labelBoundings,
                        $step->label,
                        $alignement
                    );
                }
            }

            if ( !$step->isLast )
            {
                // Iterate over minor steps
                foreach ( $step->childs as $minorStep )
                {
                    $minorStepPosition = new ezcGraphCoordinate(
                        $start->x + ( $end->x - $start->x ) * $minorStep->position,
                        $start->y + ( $end->y - $start->y ) * $minorStep->position
                    );
                    $minorStepSize = new ezcGraphCoordinate(
                        $axisBoundings->width * $minorStep->width,
                        $axisBoundings->height * $minorStep->width
                    );

                    if ( $axis->minorGrid )
                    {
                        $this->drawGrid( 
                            $renderer, 
                            $gridBoundings, 
                            $minorStepPosition,
                            $minorStepSize,
                            $axis->minorGrid
                        );
                    }
                    
                    // major step
                    $this->drawStep( 
                        $renderer, 
                        $minorStepPosition,
                        $direction, 
                        $axis->position, 
                        $this->minorStepSize, 
                        $axis->border
                    );
                }
            }
        }
    }
}
?>
