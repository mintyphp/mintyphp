<?php echo '<?php' ?>
if (!empty($_POST)) {
    $rows = DB::delete('DELETE FROM `<?php echo $table; ?>` WHERE `id` = ?', $id);
    if (!$rows) {
        Flash::set('danger','<?php echo ucfirst($singularize($humanize($table))); ?> not deleted');
    } else {
        Flash::set('success','<?php echo ucfirst($singularize($humanize($table))); ?> deleted');
    }
    Router::redirect('<?php echo $path; ?>/<?php echo $table; ?>/index');
}
