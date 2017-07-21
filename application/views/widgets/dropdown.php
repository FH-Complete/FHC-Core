<?php Widget::printStartBlock(${Widget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]))
				{
					$align = "align-middle";
					if (isset(${Widget::HTML_ARG_NAME}[DropdownWidget::MULTIPLE]))
					{
						$align = "align-top";
					}
			?>
				<div class="div-cell <?php echo $align; ?>">
					<label for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>">
						<?php echo ${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]; ?>
					</label>
				</div>
			<?php
				}
			?>
			<div class="div-cell">
				<select
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, DropdownWidget::SIZE); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, DropdownWidget::MULTIPLE, false); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
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
									if ($element->{DropdownWidget::ID_FIELD} == $selectedElement)
									{
										$selected = 'selected';
									}
								}
							}
							else
							{
								if ($element->{DropdownWidget::ID_FIELD} == $selectedElements)
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
			</div>
		</div>
	</div>

<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>