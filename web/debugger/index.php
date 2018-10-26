<?php
// Change directory to project root
chdir(__DIR__ . '/../..');
// Use default autoload implementation
require 'vendor/mindaphp/core/Loader.php';
// Load the config parameters
require 'config/config.php';
// Debugview class
class DebugView
{
    public static function getRequestCaption($request)
    {
        $parts = array();
        if (isset($request['type'])) {
            $parts[] = '<span class="badge pull-right">' . $request['status'] . '</span>';
        }
        $url = $request['router']['request'];
        if (strlen($url) > 40) {
            $shortUrl = substr($url, 0, 40) . '...';
        } else {
            $shortUrl = $url;
        }
        $parts[] = '<small>' . htmlentities($request['router']['method'] . ' ' . $shortUrl) . '</small>';
        return implode(' ', $parts);
    }

    public static function getRequestList()
    {
        $html = array();
        $html[] = '<ul class="nav nav-pills nav-stacked">';
        $last = count($_SESSION[Debugger::$sessionKey]) - 1;
        foreach ($_SESSION[Debugger::$sessionKey] as $i => $request) {
            $active = ($i == $last ? 'active' : '');
            $html[] = '<li class="' . $active . '"><a href="#debug-request-' . $i . '" data-toggle="tab">';
            $html[] = static::getRequestCaption($request);
            $html[] = '</a></li>';
        }
        $html[] = '</ul>';
        return implode("\n", $html);
    }

    public static function getTabList($i)
    {
        $html = array();
        $html[] = '<ul class="nav nav-pills">';
        $html[] = '<li class="active"><a class="debug-request-routing" href="#debug-request-' . $i . '-routing" data-toggle="tab">Routing</a></li>';
        $html[] = '<li><a class="debug-request-execution" href="#debug-request-' . $i . '-execution" data-toggle="tab">Execution</a></li>';
        $html[] = '<li><a class="debug-request-session" href="#debug-request-' . $i . '-session" data-toggle="tab">Session</a></li>';
        $html[] = '<li><a class="debug-request-queries" href="#debug-request-' . $i . '-queries" data-toggle="tab">Queries</a></li>';
        $html[] = '<li><a class="debug-request-api_calls" href="#debug-request-' . $i . '-api_calls" data-toggle="tab">API calls</a></li>';
        $html[] = '<li><a class="debug-request-cache" href="#debug-request-' . $i . '-cache" data-toggle="tab">Cache</a></li>';
        $html[] = '<li><a class="debug-request-logging" href="#debug-request-' . $i . '-logging" data-toggle="tab">Logging</a></li>';
        $html[] = '</ul>';
        return implode("\n", $html);
    }

    public static function flattenParameters($array, $prefix = '')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if ($prefix) {
                $key = '[' . $key . ']';
            }
            if (is_array($value)) {
                $result = $result+static::flattenParameters($value, $prefix . $key);
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    public static function getRoutingTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane active" id="debug-request-' . $requestId . '-routing">';
        if ($request['router']['method'] == 'GET' && count($request['router']['parameters']['get'])) {
            $html[] = '<div class="alert alert-warning"><strong>Warning:</strong> GET parameters should not be used</div>';
        }
        if ($request['router']['method'] == 'POST' && !$request['router']['csrfOk']) {
            $html[] = '<div class="alert alert-danger"><strong>Error:</strong> CSRF token validation failed</div>';
        }
        $html[] = '<h4>Request</h4>';
        $html[] = '<div class="well well-sm">' . htmlentities($request['router']['method'] . ' ' . $request['router']['request']) . '</div>';
        $html[] = '<h4>Files</h4>';
        $html[] = '<table class="table"><tbody>';
        $html[] = '<tr><th>Action</th><td>' . ($request['router']['actionFile'] ?: '<em>None</em>') . '</td></tr>';
        $html[] = '<tr><th>View</th><td>' . $request['router']['viewFile'] . '</td></tr>';
        $html[] = '<tr><th>Template</th><td>' . ($request['router']['templateFile'] ?: '<em>None</em>') . '</td></tr>';
        $html[] = '</tbody></table>';
        $html[] = '<h4>Parameters</h4>';
        $html[] = '<table class="table"><tbody>';
        if (!count($request['router']['parameters']['url'])) {
            $html[] = '<tr><td colspan="2"><em>None</em></td></tr>';
        } else {
            foreach ($request['router']['parameters']['url'] as $k => $v) {
                $html[] = '<tr><th>' . htmlspecialchars($k) . '</th><td>' . htmlspecialchars($v) . '</td></tr>';
            }
        }

        $html[] = '</tbody></table>';
        if (count($request['router']['parameters']['get'])) {
            $html[] = '<h4>$_GET</h4>';
            $html[] = '<table class="table"><tbody>';
            $request['router']['parameters']['get'] = static::flattenParameters($request['router']['parameters']['get']);
            foreach ($request['router']['parameters']['get'] as $k => $v) {
                $html[] = '<tr><th>' . htmlspecialchars($k) . '</th><td>' . htmlspecialchars($v) . '</td></tr>';
            }
            $html[] = '</tbody></table>';
        }
        if (count($request['router']['parameters']['post'])) {
            $html[] = '<h4>$_POST</h4>';
            $html[] = '<table class="table"><tbody>';
            $request['router']['parameters']['post'] = static::flattenParameters($request['router']['parameters']['post']);
            foreach ($request['router']['parameters']['post'] as $k => $v) {
                $html[] = '<tr><th>' . htmlspecialchars($k) . '</th><td>' . htmlspecialchars($v) . '</td></tr>';
            }
            $html[] = '</tbody></table>';
        }
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getExecutionTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-execution">';
        $html[] = '<div class="row"><div class="col-md-10"><h4>Result</h4>';
        $html[] = '<div class="well well-sm">';
        if (!isset($request['type'])) {
            $html[] = '???';
        } elseif ($request['type'] == 'abort') {
            $html[] = htmlspecialchars('Aborted: Exception, "die()" or "exit" encountered');
        } elseif ($request['type'] == 'ok') {
            $html[] = htmlspecialchars('Rendered page: ' . $request['router']['url']);
        } elseif ($request['type'] == 'redirect') {
            $html[] = htmlspecialchars('Redirected to: ' . $request['redirect']);
        }
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '<div class="col-md-2"><h4>Code</h4>';
        $html[] = '<div class="well well-sm">';
        $html[] = htmlspecialchars($request['status']);
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $time = (int) trim($request['start']);
        $time = date('H:i:s', $time) . sprintf('%.3f', $request['start'] - $time);
        $duration = isset($request['duration']) ? sprintf('%.2f ms', $request['duration'] * 1000) : '???';
        $memory = isset($request['memory']) ? sprintf('%.2f MB', $request['memory'] / 1000000) : '???';
        $html[] = '<table class="table"><thead>';
        $html[] = '<tr><th>Time</th><th>Duration</th><th>Peak memory</th><th>Run as</th></tr>';
        $html[] = '</thead><tbody><tr>';
        $html[] = '<td>' . $time . '</td><td>' . $duration . '</td><td>' . $memory . '</td><td>' . $request['user'] . '</td>';
        $html[] = '</tr></tbody></table>';
        $html[] = '<h4>Classes</h4>';
        $html[] = '<table class="table"><tbody>';
        $total = 0;
        $count = 0;
        foreach ($request['classes'] as $filename) {
            $count++;
            $path = str_replace(realpath(getcwd()) . '/', '', $filename);
            $path = htmlspecialchars($path);
            $size = filesize($filename);
            $total += $size;
            $size = sprintf('%.2f kB', $size / 1000);
            $html[] = '<tr><td>' . $count . '.</td><td>' . $path . '</td><td>' . $size . '</td></tr>';
        }
        $total = sprintf('%.2f kB', $total / 1000);
        $html[] = '<tr><td colspan="2"><strong>Total</strong></td><td><strong>' . $total . '</strong></td></tr>';
        $html[] = '</tbody></table>';
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getSessionTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-session">';
        foreach ($request['session'] as $title => $value) {
            $html[] = '<h4>' . ucfirst($title) . '</h4>';
            $html[] = '<pre>';
            $html[] = $value;
            $html[] = '</pre>';
        }
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getQueriesTabPaneTabList($requestId, $i, $args, $rows)
    {
        $html = array();
        $html[] = '<ul class="nav nav-pills">';
        $args = $args ? '<span class="badge pull-right">' . $args . '</span>' : '';
        $html[] = '<li class="active"><a href="#debug-request-' . $requestId . '-query-' . $i . '-arguments" data-toggle="tab">Arguments' . $args . '</a></li>';
        $html[] = '<li><a href="#debug-request-' . $requestId . '-query-' . $i . '-explain" data-toggle="tab">Explain</a></li>';
        $rows = $rows ? '<span class="badge pull-right">' . $rows . '</span>' : '';
        $html[] = '<li><a href="#debug-request-' . $requestId . '-query-' . $i . '-result" data-toggle="tab">Result' . $rows . '</a></li>';
        $html[] = '</ul>';
        return implode("\n", $html);
    }

    public static function getQueriesTabPaneTabPane($requestId, $query, $queryId, $type)
    {
        $html = array();
        if ($type == 'arguments') {
            $html[] = '<div class="tab-pane active" id="debug-request-' . $requestId . '-query-' . $queryId . '-' . $type . '">';
            $html[] = '<pre style="margin-top:10px;">';
            $html[] = 'PREPARE `query` FROM \'' . $query['equery'] . '\';';
            $params = false;
            foreach ($query[$type] as $i => $argument) {
                if (!$params) {
                    $html[] = '';
                    $params = ' USING ';
                }
                $params .= '@argument' . ($i + 1) . ',';
                $html[] = 'SET @argument' . ($i + 1) . ' = ' . htmlspecialchars(var_export($argument, true)) . ';';
            }
            $params = rtrim($params, ',');
            $html[] = '';
            $html[] = 'EXECUTE `query`' . $params . ';';
            $html[] = 'DEALLOCATE PREPARE `query`;';
            $html[] = '</pre></div>';
        } else {
            $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-query-' . $queryId . '-' . $type . '">';
            $html[] = '<table class="table"><thead>';
            if (is_int($query[$type])) {
                $html[] = '<tr><th>Field</th><th>Value</th></tr>';
                $html[] = '</thead><tbody>';
                $html[] = '<tr><td>Affected rows</td><td>' . $query[$type] . '</td></tr>';
            } else if (is_array($query[$type])) {
                $html[] = '<tr><th>#</th><th>Table</th><th>Field</th><th>Value</th></tr>';
                $html[] = '</thead><tbody>';
                foreach ($query[$type] as $i => $tables) {
                    $f = 0;
                    $fc = array_sum(array_map("count", $tables));
                    foreach ($tables as $table => $fields) {
                        $t = 0;
                        $tc = count($fields);
                        foreach ($fields as $field => $value) {
                            $rowCell = $f ? '' : '<td rowspan="' . $fc . '">' . ($i + 1) . '</td>';
                            $tableCell = $t ? '' : '<td rowspan="' . $tc . '">' . $table . '</td>';
                            $html[] = '<tr>' . $rowCell . $tableCell . '<td>' . $field . '</td><td>' . htmlspecialchars(var_export($value, true)) . '</td></tr>';
                            $t++;
                            $f++;
                        }
                    }
                }
            } else {
                $html[] = '</thead><tbody>';
            }
            $html[] = '</tbody>';
            $html[] = '</table></div>';
        }
        return implode("\n", $html);
    }

    public static function getQueriesTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-queries">';
        $html[] = '<table class="table"><thead>';
        $html[] = '<tr><th>DB</th><th>Duration</th></tr>';
        $html[] = '</thead><tbody>';
        $count = 0;
        $total = 0;
        foreach ($request['queries'] as $i => $query) {
            $count++;
            $total += $query['duration'];
            $html[] = '<tr>';
            $html[] = '<td><a href="#" onclick="$(\'#debug-request-' . $requestId . '-query-' . $i . '\').toggle(); return false;">' . $query['query'] . '</a></td>';
            $html[] = '<td>' . sprintf('%.2f ms', $query['duration'] * 1000) . '</td>';
            $html[] = '</tr>';
            $html[] = '<tr style="display:none;" id="debug-request-' . $requestId . '-query-' . $i . '"><td colspan="5">';

            $html[] = static::getQueriesTabPaneTabList($requestId, $i, count($query['arguments']), is_array($query['result']) ? count($query['result']) : 1);
            $html[] = '<div class="tab-content">';
            $html[] = static::getQueriesTabPaneTabPane($requestId, $query, $i, 'arguments');
            $html[] = static::getQueriesTabPaneTabPane($requestId, $query, $i, 'explain');
            $html[] = static::getQueriesTabPaneTabPane($requestId, $query, $i, 'result');
            $html[] = '</div>';

            $html[] = '</td></tr>';
        }
        $html[] = '<tr><td><strong>' . $count . ' queries</strong></td>';
        $html[] = '<td>' . sprintf('%.2f ms', $total * 1000) . '</td></tr>';
        $html[] = '</tbody></table>';
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getApiCallsTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-api_calls">';
        $html[] = '<table class="table"><thead>';
        $html[] = '<tr><th>URL</th><th class="">Duration</th></tr>';
        $html[] = '</thead><tbody>';
        $count = 0;
        $total = 0;
        foreach ($request['api_calls'] as $i => $call) {
            $count++;
            $total += $call['duration'];
            $url = $call['url'];
            if (strlen($url) > 40) {
                $shortUrl = substr($url, 0, 40) . '...';
            } else {
                $shortUrl = $url;
            }
            $html[] = '<tr>';
            $html[] = '<td><a href="#" onclick="$(\'#debug-request-' . $requestId . '-api_call-' . $i . '\').toggle(); return false;">' . $call['method'] . ' ' . $shortUrl . '<span class="badge pull-right">' . $call['status'] . '</span></a></td>';
            $html[] = '<td>' . sprintf('%.2f ms', $call['duration'] * 1000) . '</td>';
            $html[] = '</tr>';
            $html[] = '<tr style="display:none;" id="debug-request-' . $requestId . '-api_call-' . $i . '"><td colspan="5">';

            $html[] = '<table class="table"><thead>';
            $html[] = '<tr><th>Category</th><th>Key</th><th>Value</th></tr>';
            $html[] = '</thead><tbody>';
            $tables = array();
            $tables['details'] = array();
            $tables['details']['method'] = $call['method'];
            $tables['details']['url'] = '<a href="' . $url . '" target="_blank">Visit</a>';
            $tables['details']['status'] = $call['status'];
            $tables['details']['data_sent'] = $call['data'] ? '<a href="data:text/plain;base64,' . base64_encode($call['data']) . '" target="_blank">' . strlen($call['data']) . ' bytes</a>' : '-';
            $tables['details']['data_received'] = $call['result']['data'] ? '<a href="data:text/plain;base64,' . base64_encode($call['result']['data']) . '" target="_blank">View (' . strlen($call['result']['data']) . ' bytes)</a>' : '-';
            $tables['details']['headers_sent'] = $call['headers'] ? '<a href="data:text/plain;base64,' . base64_encode(var_export($call['headers'], true)) . '" target="_blank">' . count($call['headers']) . ' headers</a>' : '-';
            $tables['details']['headers_received'] = $call['result']['headers'] ? '<a href="data:text/plain;base64,' . base64_encode(var_export($call['result']['headers'], true)) . '" target="_blank">' . count($call['result']['headers']) . ' headers</a>' : '-';
            $tables['timing'] = array_map(function ($v) {return sprintf('%.2f ms', $v * 1000);}, $call['timing']);

            $tables['options'] = $call['options'];
            $tables['headers'] = $call['headers'];
            foreach ($tables as $table => $fields) {
                $t = 0;
                $tc = count($fields);
                foreach ($fields as $field => $value) {
                    $tableCell = $t ? '' : '<td rowspan="' . $tc . '">' . $table . '</td>';
                    $html[] = '<tr>' . $tableCell . '<td>' . $field . '</td><td>' . $value . '</td></tr>';
                    $t++;
                }
            }
            $html[] = '</tbody>';
            $html[] = '</table>';

            $html[] = '</td></tr>';
        }
        $html[] = '<tr><td><strong>' . $count . ' API calls</strong></td>';
        $html[] = '<td>' . sprintf('%.2f ms', $total * 1000) . '</td></tr>';
        $html[] = '</tbody></table>';
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getCacheTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-cache">';
        $html[] = '<table class="table"><thead>';
        $html[] = '<tr><th>Command</th><th>Result</th><th class="">Duration</th></tr>';
        $html[] = '</thead><tbody>';
        $count = 0;
        $total = 0;
        foreach ($request['cache'] as $i => $call) {
            $count++;
            $total += $call['duration'];

            $html[] = '<tr>';
            $html[] = '<td>' . strtoupper($call['command']) . ' ' . implode(' ', $call['arguments']) . '</td>';
            $html[] = '<td>' . $call['result'] . '</td>';
            $html[] = '<td>' . sprintf('%.2f ms', $call['duration'] * 1000) . '</td>';
            $html[] = '</tr>';
        }
        $html[] = '<tr><td colspan="2"><strong>' . $count . ' commands</strong></td>';
        $html[] = '<td>' . sprintf('%.2f ms', $total * 1000) . '</td></tr>';
        $html[] = '</tbody></table>';
        $html[] = '</div>';
        return implode("\n", $html);
    }

    public static function getLoggingTabPane($requestId, $request)
    {
        $html = array();
        $html[] = '<div class="tab-pane" id="debug-request-' . $requestId . '-logging">';
        $html[] = '<br/><pre>';
        foreach ($request['log'] as $log) {
            $html[] = htmlspecialchars($log) . "\n";
        }
        $html[] = '</pre>';
        $html[] = '</div>';
        return implode("\n", $html);
    }
}
Debugger::$enabled = false;
Session::$enabled = true;
// Start the session
Session::start();
?>
<!DOCTYPE html>
<html>
  <head>
    <base href="<?php echo Router::getBaseUrl(); ?>">
    <title>MindaPHP Debugger</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="debugger/img/favicon.ico">
    <!-- Bootstrap -->
    <link href="debugger/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="debugger/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">

    <!-- jDB (necessary for Bootstrap's JavaScript plugins) -->
    <script src="debugger/js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="debugger/js/bootstrap.min.js"></script>

  </head>
  <body>
  <div class="container">

    <div class="row">
      <div class="col-md-4">
        <h3>
          <img src="debugger/img/mindaphp_logo_22x24.png" alt="MindaPHP logo" style="float:left; margin-right:10px;">
          MindaPHP Debugger
        </h3>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <?php echo DebugView::getRequestList(); ?>
      </div>
      <div class="col-md-8">
        <div class="tab-content">
          <?php $last = count($_SESSION[Debugger::$sessionKey]) - 1;?>
          <?php foreach ($_SESSION[Debugger::$sessionKey] as $i => $request): ?>
          <div class="tab-pane <?php echo $i == $last ? 'active' : ''; ?>" id="debug-request-<?php echo $i ?>">
            <?php echo DebugView::getTabList($i); ?>
            <div class="tab-content">
              <?php echo DebugView::getRoutingTabPane($i, $request); ?>
              <?php echo DebugView::getExecutionTabPane($i, $request); ?>
              <?php echo DebugView::getSessionTabPane($i, $request); ?>
              <?php echo DebugView::getQueriesTabPane($i, $request); ?>
              <?php echo DebugView::getApiCallsTabPane($i, $request); ?>
              <?php echo DebugView::getCacheTabPane($i, $request); ?>
              <?php echo DebugView::getLoggingTabPane($i, $request); ?>
            </div>
          </div>
          <?php endforeach;?>
        </div>
        <script>
        $(function () {
          var classes=[];
          $('#debug-request-<?php echo $last; ?> a[data-toggle="tab"]').each(function (e) {
            classes.push($(this).attr('class'));
          });
          $(classes).each(function (i,c) {
        	$('a[data-toggle="tab"].'+c).on('shown.bs.tab', function () {
              $('a[data-toggle="tab"].'+c).each(function () {
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
