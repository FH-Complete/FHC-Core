<?php
	if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::DESCRIPTION]))
	{
?>
	<label for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>">
		<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::DESCRIPTION); ?>
	</label>
<?php
	}
?>
<select
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, DropdownWidget::SIZE); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, DropdownWidget::MULTIPLE, false); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REQUIRED); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::MAX_VALUE); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::MIN_VALUE); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
>
	<?php
		$elements = ${DropdownWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME};
		$selectedElements = ${DropdownWidget::SELECTED_ELEMENT};
		
		foreach($elements as $element)
		{
			$selected = '';
			
			if (is_array($selectedElements))
			{
				foreach($selectedElements as $selectedElement)
				{
					if ($element->{DropdownWidget::ID_FIELD} === $selectedElement)
					{
						$selected = 'selected';
					}
				}
			}
			else
			{
				if ($element->{DropdownWidget::ID_FIELD} === $selectedElements)
				{
					$selected = 'selected';
				}
			}
	?>
		<option value="<?php echo $element->{DropdownWidget::ID_FIELD}; ?>" <?php echo $selected; ?>>
			<?php echo $element->{DropdownWidget::DESCRIPTION_FIELD}; ?>
		</option>
	<?php
		}
	?>
</select>