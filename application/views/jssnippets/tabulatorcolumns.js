let result = [];
let current;

<?php foreach ($snippets as $path) { ?>

current = (tabulatorcolumns => {
<?php $this->load->view($path); ?>
})(result);

if (current) {
	if (Array.isArray(current))
		result = result.concat(current);
	else
		result.push(current);
}

<?php } ?>

export default result;