<select id="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>" name="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_NAME]; ?>">
	<?php foreach(${DropdownWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME} as $element): ?>
		<?php
			$selected = '';
			if ($element->{DropdownWidget::ID_FIELD} === ${DropdownWidget::SELECTED_ELEMENT})
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $element->{DropdownWidget::ID_FIELD}; ?>" <?php echo $selected; ?>>
			<?php echo $element->{DropdownWidget::DESCRIPTION_FIELD}; ?>
		</option>
	<?php endforeach; ?>
</select>