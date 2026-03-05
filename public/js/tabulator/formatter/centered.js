export function centeredFormatter (cell) 
{
	const val = cell.getValue()
	return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">'+val+'</div>'
}