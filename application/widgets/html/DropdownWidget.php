<?php

/**
 * It exends the Widget class to represent an HTML dropdown
 */
class DropdownWidget extends HTMLWidget
{
	// The name of the element of the data array given to the view
	// this element is an array of elements to be place inside the dropdown
	const WIDGET_DATA_ELEMENTS_ARRAY_NAME = 'ELEMENTS_ARRAY';
	// Name of the property that will be used to store the value attribute of the option tag
	const ID_FIELD = 'id';
	// Name of the property that will be used to store the value between the option tags
	const DESCRIPTION_FIELD = 'description';
	// The name of the element of the data array given to the view
	// this element is used to tell what element of the dropdown is selected
	const SELECTED_ELEMENT = 'selectedElement';
	// Default HTML value
	const HTML_DEFAULT_VALUE = 'null';

	const SIZE = 'size'; // size of the dropdown
	const MULTIPLE = 'multiple'; // multiple attribute

	// Alias of $this->_args[HTMLWidget::HTML_ARG_NAME] for a better code readability
    protected $htmlParameters;

	/**
	 *
	 */
	public function __construct($name, $args = array(), $htmlArgs = array())
	{
		parent::__construct($name, $args, $htmlArgs);

		// If the selectd element is not set then set it to HTML_DEFAULT_VALUE
		if (!isset($this->_args[DropdownWidget::SELECTED_ELEMENT]))
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = DropdownWidget::HTML_DEFAULT_VALUE;
		}

		$this->htmlParameters =& $this->_args[HTMLWidget::HTML_ARG_NAME]; // Reference for a better code readability

		// By default is not a multiple dropdown
		unset($this->htmlParameters[DropdownWidget::MULTIPLE]);
	}

    /**
     * Set this dropdown as multiple:
     * - Setting the multiple attribute
     * - Adding square brackets to the name
     */
    public function setMultiple()
    {
		$this->htmlParameters[DropdownWidget::MULTIPLE] = DropdownWidget::MULTIPLE;
		$this->htmlParameters[HTMLWidget::HTML_NAME] .= '[]';
    }

    /**
     * Checks if this object is a multiple dropdown
	 */
    public function isMultipleDropdown()
    {
		$isMultipleDropdown = false;

		if (isset($this->htmlParameters[DropdownWidget::MULTIPLE])
			&& $this->htmlParameters[DropdownWidget::MULTIPLE] == DropdownWidget::MULTIPLE)
		{
			$isMultipleDropdown = true;
		}

		return $isMultipleDropdown;
    }

    /**
	 * Add the correct select to the model used to load a list of elemets for this dropdown
	 * @param model $model the model used to load elements
	 * @param string $idName the name of the field that will used to be the value of the option tag
	 * @param string $descriptionName the name of the field that will used to be displayed in the dropdown
	 */
	protected function addSelectToModel($model, $idName, $descriptionName)
	{
		$model->addSelect(
			sprintf(
				'%s AS %s, %s AS %s',
				$idName,
				DropdownWidget::ID_FIELD,
				$descriptionName,
				DropdownWidget::DESCRIPTION_FIELD
			)
		);
	}

	/**
	 * Set the array used to populate the dropdown
	 * @param array $elements list used to populate this dropdown
	 * @param boolean $emptyElement if an empty element must be added at the beginning of the dropdown
	 * @param string $stdDescription description of the empty element
	 * @param string $noDataDescription description if no data are found
	 * @param string $id value of the attribute value of the empty element
	 */
	protected function setElementsArray(
		$elements, $emptyElement = false, $stdDescription = '' , $noDataDescription = 'No data found' , $id = DropdownWidget::HTML_DEFAULT_VALUE
	)
	{
		$tmpElements = array();

		if (isError($elements))
		{
			if (is_object($elements) && isset($elements->retval))
			{
				show_error(getError($elements));
			}
			else if (is_string($elements))
			{
				show_error($elements);
			}
			else
			{
				show_error('Generic error occurred');
			}
		}
		else
		{
			if ($emptyElement === true && $this->isMultipleDropdown() == false)
			{
				$tmpElements = $this->addElementAtBeginning(
					$elements,
					$stdDescription,
					$noDataDescription,
					$id
				);
			}
			else
			{
				$tmpElements = $elements->retval;
			}

			$this->_args[DropdownWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME] = $tmpElements;
		}
	}

	/**
     * Adds an element to the beginning of the array
     */
	protected function addElementAtBeginning($elements, $stdDescription, $noDataDescription, $id)
	{
		$element = new stdClass();
		$element->{DropdownWidget::ID_FIELD} = $id;
		$element->{DropdownWidget::DESCRIPTION_FIELD} = $stdDescription;

		if (!hasData($elements))
		{
			$element->{DropdownWidget::DESCRIPTION_FIELD} = $noDataDescription;
		}

		array_unshift($elements->retval, $element);

		return $elements->retval;
	}

	/**
	 * Loads the dropdown view with all the elements to be displayed
	 */
	protected function loadDropDownView()
	{
		$this->view('widgets/dropdown', $this->_args);
	}
}
