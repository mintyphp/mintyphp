<?php echo '<?php' ?>
<?php foreach ($belongsTo as $relation) : ?>
$<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?> = DB::selectPairs('select `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_COLUMN_NAME']; ?>`,`<?php echo $findDisplayField($relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']); ?>` from `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?>`');
<?php endforeach; ?>
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;
<?php foreach ($belongsTo as $relation) : ?>
	if (!isset($<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?>[$data['<?php echo $table; ?>']['<?php echo $relation['KEY_COLUMN_USAGE']['COLUMN_NAME']; ?>']])) $errors['<?php echo $table; ?>[<?php echo $relation['KEY_COLUMN_USAGE']['COLUMN_NAME']; ?>]']='<?php echo ucfirst($singularize($humanize($relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']))); ?> not found';
<?php endforeach; ?>
	if (!isset($errors)) {
		$id = DB::insert('INSERT INTO `<?php echo $table; ?>` (<?php echo implode(', ',array_map(function($field){ return '`'.$field['COLUMNS']['COLUMN_NAME'].'`'; }, $fields)); ?>) VALUES (<?php echo implode(', ',array_map(function(){ return '?'; }, $fields)); ?>)', <?php echo implode(', ',array_map(function($field) use ($table) { return "\$data['$table']['".$field['COLUMNS']['COLUMN_NAME']."']"; }, $fields)); ?>);
		if ($id) {
			Flash::set('success','<?php echo ucfirst($singularize($humanize($table))); ?> saved');
			Router::redirect('<?php echo $path; ?>/<?php echo $table; ?>/index');
		}
	}
	Flash::set('danger','<?php echo ucfirst($singularize($humanize($table))); ?> not saved');
} else {
	$data = array('<?php echo $table; ?>'=>array(<?php echo implode(', ',array_map(function($field){ return "'".$field['COLUMNS']['COLUMN_NAME']."'".'=>'.var_export($field['COLUMNS']['COLUMN_DEFAULT'],true); }, $fields)); ?>));
}
