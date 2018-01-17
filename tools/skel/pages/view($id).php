<?php echo '<?php' ?> 
$data = DB::selectOne('select * from `<?php echo $table; ?>` where `id`=?',$id);
<?php foreach ($belongsTo as $relation) : ?>
$<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?> = DB::selectPairs('select `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_COLUMN_NAME']; ?>`,`<?php echo $findDisplayField($relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']); ?>` from `<?php echo $relation['KEY_COLUMN_USAGE']['REFERENCED_TABLE_NAME']; ?>`');
<?php endforeach; ?>
