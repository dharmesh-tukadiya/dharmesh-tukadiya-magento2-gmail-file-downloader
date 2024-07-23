<?php

namespace DnTukadiya\GmailFileDownloader\CURL;

use \Magento\Framework\HTTP\Client\Curl;

class Request
{
    private Curl $curl;
    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }
    public function send(array $data)
    {
        $method = $data['method'] ?? "GET";
        $url = $data['url'];
        $headers = $data['headers'] ?? [];
        $params = $data['params'] ?? [];
        $curl = $this->curl;
        $curl->setHeaders($headers);
        if ($method == "POST") {
            $curl->post($url, $params);
        } elseif ($method == "GET") {
            $url = $url . "?" . http_build_query($params);
            $curl->get($url);
        }
        return $curl->getBody();
    }
}
