<?php 
// Load the helper functions
require '../lib/debugger.php';

session_start('mindaphp'); 
?>
<!DOCTYPE html>
<html>
  <head>
    <title>MindaPHP</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link href="/css/font-awesome.min.css" rel="stylesheet">
    <link href="/css/default.css" rel="stylesheet">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/js/bootstrap.min.js"></script>
    
  </head>
  <body>
  <div class="row">
  <div class="col-md-4">
  <h3>MindaPHP Debugger</h3>
  </div>
  </div>
  <div class="row">
  <div class="col-md-4">
  
<ul class="nav nav-pills nav-stacked">
<?php foreach ($_SESSION['debugger'] as $i=>$request): ?>
<li class="<?php echo $i==0?'active':''; ?>"><a href="#debug-request-<?php echo $i ?>" data-toggle="tab">
  <?php echo Debugger::formatRequest($request); ?>
</a></li>
<?php endforeach; ?>
</ul>

  </div>
  <div class="col-md-8">
  
<div class="tab-content">
<?php foreach ($_SESSION['debugger'] as $i=>$request): ?>
<div class="tab-pane <?php echo $i==0?'active':''; ?>" id="debug-request-<?php echo $i ?>">
<ul class="nav nav-pills">
  <li class="active"><a class="debug-request-routing" href="#debug-request-<?php echo $i ?>-routing" data-toggle="tab">Routing</a></li>
  <li><a class="debug-request-execution" href="#debug-request-<?php echo $i ?>-execution" data-toggle="tab">Execution</a></li>
  <li><a class="debug-request-queries" href="#debug-request-<?php echo $i ?>-queries" data-toggle="tab">Queries</a></li>
  <li><a class="debug-request-logging" href="#debug-request-<?php echo $i ?>-logging" data-toggle="tab">Logging</a></li>
</ul>
<div class="tab-content">

<div class="tab-pane active" id="debug-request-<?php echo $i ?>-routing">
<?php if ($request['router']['method']=='GET' && count($request['router']['parameters']['get'])):?>
<div class="alert alert-warning"><strong>Warning:</strong> GET parameters should not be used</div>
<?php endif; ?>
<?php if ($request['router']['method']=='POST' && !$request['router']['csrfOk']):?>
<div class="alert alert-danger"><strong>Error:</strong> CSRF token validation failed</div>
<?php endif; ?>
<h4>Request</h4>
<div class="well well-sm">
<?php echo $request['router']['method'].' '.htmlentities($request['router']['request']); ?>
</div>
<?php if ($request['router']['redirect']):?>
<h4>Redirect</h4>
<div class="well well-sm">
<?php echo $request['router']['method'].' '.$request['router']['redirect']; ?><br/>
</div>
<?php endif;?>
<h4>Target</h4>
<div class="well well-sm">
<?php echo $request['router']['method'].' '.htmlentities($request['router']['dir'].$request['router']['view'].'.'.$request['router']['template'].'.php'); ?>
</div>
<h4>$parameters</h4>
<table class="table"><tbody>
<?php if (!count($request['router']['parameters']['url'])):?>
<tr><td colspan="2"><em>None</em></td></tr>
<?php else: foreach ($request['router']['parameters']['url'] as $k=>$v): ?>
<tr><th><?php echo $k; ?></th><td><?php echo htmlspecialchars($v); ?></td></tr>
<?php endforeach; endif;?>
</tbody></table>
<?php if (count($request['router']['parameters']['get'])):?>
<h4>$_GET</h4>
<table class="table"><tbody>
<?php foreach ($request['router']['parameters']['get'] as $k=>$v): ?>
<tr><th><?php echo $k; ?></th><td><?php echo htmlspecialchars($v); ?></td></tr>
<?php endforeach;?>
</tbody></table>
<?php endif;?>
<?php if (count($request['router']['parameters']['post'])):?>
<h4>$_POST</h4>
<table class="table"><tbody>
<?php foreach ($request['router']['parameters']['post'] as $k=>$v): ?>
<tr><th><?php echo $k; ?></th><td><?php echo htmlspecialchars($v); ?></td></tr>
<?php endforeach;?>
</tbody></table>
<?php endif;?>
</div>

<div class="tab-pane" id="debug-request-<?php echo $i ?>-execution">
<h4>Result</h4>
<div class="well well-sm">
<?php if ($request['type']=='ok'):?>
<?php echo htmlspecialchars('Render page: '.$request['router']['url']); ?>
<?php elseif ($request['type']=='redirect'):?>
<?php echo htmlspecialchars('Redirect to: '.$request['redirect']); ?>
<?php endif; ?>
</div>
<table class="table"><thead>
<tr>
  <th>Time</th>
  <th>Duration</th>
  <th>Peak memory</th>
</tr>
</thead><tbody>
<tr>
  <td><?php list($time,$micro) = explode('.',$request['start']); echo date('H:i:s',$time).'.'.substr($micro,0,3); ?></td>
  <td><?php echo sprintf('%.2f',$request['duration']*1000); ?> ms</td>
  <td><?php echo sprintf('%.2f',$request['memory']/1000000); ?> MB</td>
</tr>
</tbody></table>
<h4>File paths</h4>
<table class="table"><tbody>
<tr><th>Action</th><td><?php echo $request['router']['actionFile']?:'<em>None</em>'; ?></td></tr>
<tr><th>View</th><td><?php echo $request['router']['viewFile']; ?></td></tr>
<tr><th>Template</th><td><?php echo $request['router']['templateFile']?:'<em>None</em>'; ?></td></tr>
</tbody></table>
</div>

<div class="tab-pane" id="debug-request-<?php echo $i ?>-queries">
<?php foreach ($request['queries'] as $j=>$query): ?>
<?php echo "-"?><br/>
<?php foreach ($query as $k=>$v): ?>
<?php echo "$k=>$v"; ?><br/>
<?php endforeach; ?>
<?php endforeach; ?>
</div>

<div class="tab-pane" id="debug-request-<?php echo $i ?>-logging">
<br/>
<pre>
<?php foreach ($request['log'] as $k=>$v): ?>
<?php echo $v."\n"; ?>
<?php endforeach; ?>
</pre>
</div>

</div>
</div>
<?php endforeach; ?>
</div>

<script>
$(function () {
  var classes=[];
  $('#debug-request-0 a[data-toggle="tab"]').each(function (e) { 
    classes.push($(this).attr('class')); 
  });
  $(classes).each(function (i,c) {
	$('a[data-toggle="tab"].'+c).on('shown.bs.tab', function (e) {
  	  $('a[data-toggle="tab"].'+c).each(function (e) {
	    $(this).tab('show');
	  });
    });
  });
});
</script>

</div></div>
</body>
</html>