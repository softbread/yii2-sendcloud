<?php

namespace SendCloud\Util;

use yii\httpclient\Client;

class HttpClient
{
    public function __construct($host)
    {
        $this->host = $host;
    }
    
    public function post($url, array $header, $param)
    {
        $client = new Client(['baseUrl' => $this->host]);
        
        $request = $client->createRequest()
                          ->setUrl($url)
                          ->setMethod('post')
                          ->setData($param);
        
        if (!empty($header)) {
            $request->setHeaders($header);
        }
        
        return $request->send();
    }
    
    public function mutilpost($url, $body, array $header)
    {
        $client = new Client(['baseUrl' => $this->host]);
        $request = $client->createRequest()
                          ->setUrl($url)
                          ->setMethod('post')
                          ->setContent($body);
        
        if (!empty($header)) {
            $request->setHeaders($header);
        }
        
        return $request->send();
    }
}
