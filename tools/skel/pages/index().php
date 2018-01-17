<?php echo '<?php' ?>
$data = DB::select('select * from `<?php echo $table; ?>`');
<?php foreach ($belongsTo as $relation) : ?>
$<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?> = DB::selectPairs('select `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_COLUMN_NAME']; ?>`,`<?php echo $findDisplayField($relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']); ?>` from `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?>`');
<?php endforeach; ?>
