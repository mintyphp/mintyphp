<?php

use MintyPHP\Cache;
use MintyPHP\Curl;

/** @var string $query */
$query = isset($_POST['q']) ? $_POST['q'] : '';

$results = [];
if ($query) {
    $results = Cache::get($query);
    if (!$results) {
        $results = [];
        $result = Curl::call('GET', 'http://www.bing.com/search', array('q' => $query));
        if ($result['status'] == 200) {

            $dom = new DOMDocument();
            @$dom->loadHTML($result['data']);

            $xpath = new DOMXpath($dom);
            $elements = $xpath->query('//ol["b_results"]/li[@class="b_algo"]//h2/a');

            /**
             * @var DOMNodeList<DOMElement> $elements
             * @var DOMElement $element
             */
            foreach ($elements as $element) {
                $text = $element->nodeValue;
                $link = $element->getAttribute("href");
                $results[] = ['text' => $text, 'link' => $link];
            }
            Cache::set($query, $results, 60);
        }
    }
}
