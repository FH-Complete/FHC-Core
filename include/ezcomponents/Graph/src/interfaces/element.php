<?php
/**
 * File containing the abstract ezcGraphChartElement class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class for basic chart elements
 * 
 * @property string $title
 *           Title of chart element.
 * @property ezcGraphColor $background
 *           Background color of chart element.
 * @property ezcGraphColor $border
 *           Border color of chart element.
 * @property int $padding
 *           Distance between border and content of element.
 * @property int $margin
 *           Distance between outer boundings and border of an element.
 * @property int $borderWidth
 *           Border width.
 * @property int $position
 *           Integer defining the elements position in the chart.
 * @property int $maxTitleHeight
 *           Maximum size of the title.
 * @property float $portraitTitleSize
 *           Percentage of boundings which are used for the title with 
 *           position left, right or center.
 * @property float $landscapeTitleSize
 *           Percentage of boundings which are used for the title with 
 *           position top or bottom.
 * @property ezcGraphFontOptions $font
 *           Font used for this element.
 * @property-read bool $fontCloned
 *                Indicates if font configuration was already cloned for this 
 *                specific element.
 * @property-read ezcGraphBoundings $boundings
 *                Boundings of this elements.
 *
 * @version 1.3
 * @package Graph
 */
abstract class ezcGraphChartElement extends ezcBaseOptions
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
        $this->properties['title'] = false;
        $this->properties['background'] = false;
        $this->properties['boundings'] = new ezcGraphBoundings();
        $this->properties['border'] = false;
        $this->properties['borderWidth'] = 0;
        $this->properties['padding'] = 0;
        $this->properties['margin'] = 0;
        $this->properties['position'] = ezcGraph::LEFT;
        $this->properties['maxTitleHeight'] = 16;
        $this->properties['portraitTitleSize'] = .15;
        $this->properties['landscapeTitleSize'] = .2;
        $this->properties['font'] = new ezcGraphFontOptions();
        $this->properties['fontCloned'] = false;

        parent::__construct( $options );
    }

    /**
     * Set colors and border fro this element
     * 
     * @param ezcGraphPalette $palette Palette
     * @return void
     */
    public function setFromPalette( ezcGraphPalette $palette )
    {
        $this->properties['border'] = $palette->elementBorderColor;
        $this->properties['borderWidth'] = $palette->elementBorderWidth;
        $this->properties['background'] = $palette->elementBackground;
        $this->properties['padding'] = $palette->padding;
        $this->properties['margin'] = $palette->margin;
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
            case 'title':
                $this->properties['title'] = (string) $propertyValue;
                break;
            case 'background':
                $this->properties['background'] = ezcGraphColor::create( $propertyValue );
                break;
            case 'border':
                $this->properties['border'] = ezcGraphColor::create( $propertyValue );
                break;
            case 'padding':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int >= 0' );
                }

                $this->properties['padding'] = (int) $propertyValue;
                break;
            case 'margin':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int >= 0' );
                }

                $this->properties['margin'] = (int) $propertyValue;
                break;
            case 'borderWidth':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int >= 0' );
                }

                $this->properties['borderWidth'] = (int) $propertyValue;
                break;
            case 'font':
                if ( $propertyValue instanceof ezcGraphFontOptions )
                {
                    $this->properties['font'] = $propertyValue;
                }
                elseif ( is_string( $propertyValue ) )
                {
                    if ( !$this->fontCloned )
                    {
                        $this->properties['font'] = clone $this->font;
                        $this->properties['fontCloned'] = true;
                    }

                    $this->properties['font']->path = $propertyValue;
                }
                else
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'ezcGraphFontOptions' );
                }
                break;
            case 'position':
                $positions = array(
                    ezcGraph::TOP,
                    ezcGraph::BOTTOM,
                    ezcGraph::LEFT,
                    ezcGraph::RIGHT,
                );

                if ( in_array( $propertyValue, $positions, true ) )
                {
                    $this->properties['position'] = $propertyValue;
                }
                else 
                {
                    throw new ezcBaseValueException( 'position', $propertyValue, 'integer' );
                }
                break;
            case 'maxTitleHeight':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'int >= 0' );
                }

                $this->properties['maxTitleHeight'] = (int) $propertyValue;
                break;
            case 'portraitTitleSize':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) ||
                     ( $propertyValue > 1 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, '0 <= float <= 1' );
                }

                $this->properties['portraitTitleSize'] = (float) $propertyValue;
                break;
            case 'landscapeTitleSize':
                if ( !is_numeric( $propertyValue ) ||
                     ( $propertyValue < 0 ) ||
                     ( $propertyValue > 1 ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, '0 <= float <= 1' );
                }

                $this->properties['landscapeTitleSize'] = (float) $propertyValue;
                break;
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
                break;
        }
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
            case 'font':
                // Clone font configuration when requested for this element
                if ( !$this->fontCloned )
                {
                    $this->properties['font'] = clone $this->properties['font'];
                    $this->properties['fontCloned'] = true;
                }
                return $this->properties['font'];
            default:
                return parent::__get( $propertyName );
        }
    }

    /**
     * Renders this chart element
     *
     * This method receives and returns a part of the canvas where it can be 
     * rendered on.
     * 
     * @param ezcGraphRenderer $renderer
     * @param ezcGraphBoundings $boundings
     * @return ezcGraphBoundings Part of canvas, which is still free to draw on
     */
    abstract public function render( ezcGraphRenderer $renderer, ezcGraphBoundings $boundings );

    /**
     * Returns calculated boundings based on available percentual space of 
     * given bounding box specified in the elements options and direction of 
     * the box.
     * 
     * @param ezcGraphBoundings $boundings 
     * @param int $direction 
     * @return ezcGraphBoundings
     */
    protected function getTitleSize( ezcGraphBoundings $boundings, $direction = ezcGraph::HORIZONTAL )
    {
        if ( $direction === ezcGraph::HORIZONTAL )
        {
            return min(
                $this->maxTitleHeight,
                ( $boundings->y1 - $boundings->y0 ) * $this->landscapeTitleSize
            );
        }
        else
        {
            return min(
                $this->maxTitleHeight,
                ( $boundings->y1 - $boundings->y0 ) * $this->portraitTitleSize
            );
        }
    }
}

?>
