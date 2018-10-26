<?php
$query = isset($_POST['q']) ? $_POST['q'] : '';

$results = array();
if ($query) {
    if (!($results = Cache::get($query))) {
        $result = Curl::call('GET', 'http://www.bing.com/search', array('q' => $query));
        if ($result['status'] == 200) {

            $dom = new DOMDocument();
            @$dom->loadHTML($result['data']);

            $xpath = new DOMXpath($dom);
            $elements = $xpath->query('//ol["b_results"]/li[@class="b_algo"]//h2/a');

            foreach ($elements as $element) {
                $text = $element->nodeValue;
                $link = $element->getAttribute("href");
                $results[] = compact('text', 'link');
            }
            Cache::set($query, $results, 60);
        }
    }
}
