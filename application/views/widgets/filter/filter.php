<div>
	<?php $this->view('widgets/filter/selectFields', array('listFields' => $listFields)); ?>
</div>

<br>

<div>
	<?php $this->view('widgets/filter/selectFilters', array('metaData' => $metaData)); ?>
</div>

<br>

<div>
	<?php $this->view('widgets/filter/tableDataset', array('dataset' => $dataset)); ?>
</div>
