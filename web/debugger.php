<?php 
class Debugger
{
  static function getRequestCaption($request)
  {
    $parts = array();
    $parts[] = date('H:i:s',$request['start']);
    $parts[] = strtolower($request['router']['method']).' '.htmlentities($request['router']['url']);
    
    if (!isset($request['type'])) {
      $parts[] ='???';
    } else {
      $parts[] = $request['type'];
      $parts[] = round($request['duration']*1000).' ms ';
      $parts[] = round($request['memory']/1000000).' MB';
    }
    return implode(' - ',$parts);
  }
  
  static function getRequestList()
  {
    $html = array();
    $html[] ='<ul class="nav nav-pills nav-stacked">';
    foreach ($_SESSION['debugger'] as $i=>$request) {
      $active = ($i==0?'active':'');
      $html[] ='<li class="'.$active.'"><a href="#debug-request-'.$i.'" data-toggle="tab">';
      $html[] =self::getRequestCaption($request);
      $html[] ='</a></li>';
    }
    $html[] ='</ul>';
    return implode("\n",$html);
  }
  
  static function getTabList($i)
  {
    $html = array();
    $html[] ='<ul class="nav nav-pills">';
    $html[] ='<li class="active"><a class="debug-request-routing" href="#debug-request-'.$i.'-routing" data-toggle="tab">Routing</a></li>';
    $html[] ='<li><a class="debug-request-execution" href="#debug-request-'.$i.'-execution" data-toggle="tab">Execution</a></li>';
    $html[] ='<li><a class="debug-request-queries" href="#debug-request-'.$i.'-queries" data-toggle="tab">Queries</a></li>';
    $html[] ='<li><a class="debug-request-logging" href="#debug-request-'.$i.'-logging" data-toggle="tab">Logging</a></li>';
    $html[] ='</ul>';
    return implode("\n",$html);
  }
  
  static function getRoutingTabPane($requestId,$request)
  {
    $html = array();
    $html[] ='<div class="tab-pane active" id="debug-request-'.$requestId.'-routing">';
    if ($request['router']['method']=='GET' && count($request['router']['parameters']['get'])) {
      $html[] ='<div class="alert alert-warning"><strong>Warning:</strong> GET parameters should not be used</div>';    
    }
    if ($request['router']['method']=='POST' && !$request['router']['csrfOk']) {
      $html[] ='<div class="alert alert-danger"><strong>Error:</strong> CSRF token validation failed</div>';
    }
    $method = $request['router']['method'];
    $html[] ='<h4>Request</h4>';
    $html[] ='<div class="well well-sm">'.$method.' '.htmlentities($request['router']['request']).'</div>';
    if ($request['router']['redirect']) {
      $html[] ='<h4>Redirect</h4>';
      $html[] ='<div class="well well-sm">'.$method.' '.htmlentities($request['router']['redirect']).'</div>';
    }
    $path = $request['router']['dir'].$request['router']['view'].'.'.$request['router']['template'].'.php';
    $html[] ='<h4>Target</h4>';
    $html[] ='<div class="well well-sm">'.$method.' '.htmlentities($path).'</div>';
    $html[] ='<h4>Files</h4>';
    $html[] ='<table class="table"><tbody>';
    $html[] ='<tr><th>Action</th><td>'.($request['router']['actionFile']?:'<em>None</em>').'</td></tr>';
    $html[] ='<tr><th>View</th><td>'.$request['router']['viewFile'].'</td></tr>';
    $html[] ='<tr><th>Template</th><td>'.($request['router']['templateFile']?:'<em>None</em>').'</td></tr>';
    $html[] ='</tbody></table>';
    $html[] ='<h4>$parameters</h4>';
    $html[] ='<table class="table"><tbody>';
    if (!count($request['router']['parameters']['url'])) {
      $html[] ='<tr><td colspan="2"><em>None</em></td></tr>';
    } else foreach ($request['router']['parameters']['url'] as $k=>$v) {
      $html[] ='<tr><th>'.htmlspecialchars($k).'</th><td>'.htmlspecialchars($v).'</td></tr>';
    }
    $html[] ='</tbody></table>';
    if (count($request['router']['parameters']['get'])) {
      $html[] ='<h4>$_GET</h4>';
      $html[] ='<table class="table"><tbody>';
      foreach ($request['router']['parameters']['get'] as $k=>$v) {
        $html[] ='<tr><th>'.htmlspecialchars($k).'</th><td>'.htmlspecialchars($v).'</td></tr>';
      }
      $html[] ='</tbody></table>';
    }
    if (count($request['router']['parameters']['post'])) {
      $html[] ='<h4>$_POST</h4>';
      $html[] ='<table class="table"><tbody>';
      foreach ($request['router']['parameters']['post'] as $k=>$v) {
        $html[] ='<tr><th>'.htmlspecialchars($k).'</th><td>'.htmlspecialchars($v).'</td></tr>';
      }
      $html[] ='</tbody></table>';
    }
    $html[] ='</div>';
    return implode("\n",$html);
  }
  
  static function getExecutionTabPane($requestId,$request)
  {
    $html = array();
    $html[] ='<div class="tab-pane" id="debug-request-'.$requestId.'-execution">';
    $html[] ='<h4>Result</h4>';
    $html[] ='<div class="well well-sm">';
    if (!isset($request['type'])) {
      $html[] = '???';
    } elseif ($request['type']=='abort') {
      $html[] = htmlspecialchars('Aborted: Exception, "die()" or "exit" encountered');
    } elseif ($request['type']=='ok') {
      $html[] = htmlspecialchars('Rendered page: '.$request['router']['url']);
    } elseif ($request['type']=='redirect') {
      $html[] = htmlspecialchars('Redirected to: '.$request['redirect']);
    }
    $html[] ='</div>';
    list($time,$micro) = explode('.',$request['start']);
    $time = date('H:i:s',$time).'.'.substr($micro,0,3);
    $duration = isset($request['duration'])?sprintf('%.2f ms',$request['duration']*1000):'???';
    $memory = isset($request['memory'])?sprintf('%.2f MB',$request['memory']/1000000):'???';
    $html[] ='<table class="table"><thead>';
    $html[] ='<tr><th>Time</th><th>Duration</th><th>Peak memory</th><th>Run as</th></tr>';
    $html[] ='</thead><tbody><tr>';
    $html[] ='<td>'.$time.'</td><td>'.$duration.'</td><td>'.$memory.'</td><td>'.$request['user'].'</td>';
    $html[] ='</tr></tbody></table>';    
    $html[] ='<h4>Files</h4>';
    $html[] ='<table class="table"><tbody>';
    if (!isset($request['files'])) {
      $html[] ='<tr><td colspan="3"><em>None</em></td></tr>';
    } else {
      $total = 0;
      $count = 0;
      foreach ($request['files'] as $filename) {
        $count++;
        $path = str_replace(realpath(__DIR__.'/..'),'..',$filename);
        $path = htmlspecialchars($path);
        $size = filesize($filename); 
        $total+= $size;
        $size = sprintf('%.2f kB',$size/1000); 
        $html[] ='<tr><td>'.$count.'</td><td>'.$path.'</td><td>'.$size.'</td></tr>';
      }
      $total = sprintf('%.2f kB',$total/1000);
      $html[] ='<tr><td></td><td><strong>'.$count.' files</strong></td><td><strong>'.$total.'</strong></td></tr>';
    }
    $html[] ='</tbody></table>';
    $html[] ='</div>';
    return implode("\n",$html);
  }
  
  static function getQueriesTabPaneTabList($requestId,$i,$args,$rows)
  {
    $html = array();
    $html[] ='<ul class="nav nav-pills">';
    $args = $args?'<span class="badge pull-right">'.$args.'</span>':'';
    $html[] ='<li class="active"><a href="#debug-request-'.$requestId.'-query-'.$i.'-arguments" data-toggle="tab">Arguments'.$args.'</a></li>';
    $html[] ='<li><a href="#debug-request-'.$requestId.'-query-'.$i.'-explain" data-toggle="tab">Explain</a></li>';
    $rows = $rows?'<span class="badge pull-right">'.$rows.'</span>':'';
    $html[] ='<li><a href="#debug-request-'.$requestId.'-query-'.$i.'-result" data-toggle="tab">Result'.$rows.'</a></li>';
    $html[] ='</ul>';
    return implode("\n",$html);
  }
  
  static function getQueriesTabPaneTabPane($requestId,$query,$i,$type)
  {
    $html = array();
    if ($type=='arguments') {
      $html[] = '<div class="tab-pane active" id="debug-request-'.$requestId.'-query-'.$i.'-'.$type.'">';
      $html[] = '<pre style="margin-top:10px;">';
      $html[] = 'PREPARE `query` FROM \''.$query['equery'].'\';';
      $params = false;
      foreach ($query[$type] as $i=>$argument) {
        if (!$params) {
          $html[] = '';
          $params = ' USING ';
        }
        $params.= '@argument'.($i+1).',';
        $html[] = 'SET @argument'.($i+1).' = '.var_export($argument,true).';';
      }
      $params = rtrim($params,',');
      $html[] = '';
      $html[] = 'EXECUTE `query`'.$params.';';
      $html[] = 'DEALLOCATE PREPARE `query`;';
      $html[] = '</pre></div>';
    } else {
      $html[] = '<div class="tab-pane" id="debug-request-'.$requestId.'-query-'.$i.'-'.$type.'">';
      $html[] = '<table class="table"><thead>';
      $html[] = '<tr><th>#</th><th>Field</th><th>Value</th></tr>';
      $html[] = '</thead><tbody>';
      foreach ($query[$type] as $r=>$row) {
        $f=0;
        $fc = count($row);
        foreach ($row as $field=>$value) {
          $html[] = '<tr>'.($f?'':'<td rowspan="'.$fc.'">'.($r+1).'</td>').'<td>'.$field.'</td><td>'.var_export($value,true).'</td></tr>';
          $f++;
        }
      }
      $html[] = '</tbody>';
      $html[] = '</table></div>';
    }
    return implode("\n",$html);
  }
  
  static function getQueriesTabPane($requestId,$request)
  {
    $html = array();
    $html[] = '<div class="tab-pane" id="debug-request-'.$requestId.'-queries">';
    $html[] = '<table class="table"><thead>';
    $html[] = '<tr><th>Query</th><th>Duration</th></tr>';
    $html[] = '</thead><tbody>';
    $count = 0;
    $total = 0;
    foreach ($request['queries'] as $i=>$query) {
      $count++;
      $total+= $query['duration'];
      $html[] = '<tr>';
      $html[] = '<td><a href="#" onclick="$(\'#debug-request-'.$requestId.'-query-'.$i.'\').toggle(); return false;">'.$query['query'].'</a>';
      $html[] = '<td>'.sprintf('%.2f ms',$query['duration']*1000).'</td>';
      $html[] = '</tr>';
      $html[] = '<tr style="display:none;" id="debug-request-'.$requestId.'-query-'.$i.'"><td colspan="5">';
      
      $html[] = self::getQueriesTabPaneTabList($requestId,$i,count($query['arguments']),count($query['result']));
      $html[] = '<div class="tab-content">';
      $html[] = self::getQueriesTabPaneTabPane($requestId,$query,$i,'arguments');
      $html[] = self::getQueriesTabPaneTabPane($requestId,$query,$i,'explain');
      $html[] = self::getQueriesTabPaneTabPane($requestId,$query,$i,'result');
      $html[] = '</div>';
      
      $html[] = '</td></tr>';
    }
    $html[] = '<tr><td><strong>'.$count.' queries</strong></td>';
    $html[] = '<td>'.sprintf('%.2f ms',$total*1000).'</td></tr>';
    $html[] = '</tbody></table>';
    $html[] = '</div>';
    return implode("\n",$html);
  }
  
  static function getLoggingTabPane($requestId,$request)
  {
    $html = array();
    $html[] ='<div class="tab-pane" id="debug-request-'.$requestId.'-logging">';
    $html[] ='<br/><pre>';
    foreach ($request['log'] as $log) {
       $html[] =htmlspecialchars($log)."\n";
    }
    $html[] ='</pre>';
    $html[] ='</div>';
    return implode("\n",$html);
  }
}

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
  <div class="container">
  
    <div class="row">
      <div class="col-md-4">
        <h3>MindaPHP Debugger</h3>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-4">
        <?php echo Debugger::getRequestList(); ?>
      </div>
      <div class="col-md-8">
        <div class="tab-content">
          <?php foreach ($_SESSION['debugger'] as $i=>$request): ?>
          <div class="tab-pane <?php echo $i==0?'active':''; ?>" id="debug-request-<?php echo $i ?>">
            <?php echo Debugger::getTabList($i); ?>
            <div class="tab-content">
              <?php echo Debugger::getRoutingTabPane($i,$request); ?>
              <?php echo Debugger::getExecutionTabPane($i,$request); ?>
              <?php echo Debugger::getQueriesTabPane($i,$request); ?>
              <?php echo Debugger::getLoggingTabPane($i,$request); ?>
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
      </div>
    </div>
    
  </div>
</body>
</html>