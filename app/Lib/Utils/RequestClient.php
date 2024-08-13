<?php


namespace App\Lib\Utils;

use GuzzleHttp\Exception\ClientException;
use Hyperf\DbConnection\Db;
use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;
use Hyperf\Utils\Network;
use Hyperf\Utils\Parallel;

class RequestClient
{

    private $stack;

    private $client;

    private $postType;

    private $ip;

    public function __construct()
    {
        $this->stack = (new HandlerStackFactory())->create();
        $this->client = make(Client::class, [
            'config' => [
                'handler' => $this->stack,
            ],
        ]);
        $this->ip = Network::ip();
    }

    public function get(string $url, array $data = [], array $headers = [], int $time_out = 15)
    {
        $urlData = [];
        $t1 = microtime(true);
        $urlData['url'] = $url;
        $urlData['ip'] = Network::ip();
        if (is_array($data)) {
            $urlData['params'] = json_encode($data,JSON_UNESCAPED_SLASHES);
        } else {
            $urlData['params'] = $data;
        }
        try {
            $urlInfo = $this->pathInfo($url);
            $options = [];
            $options['base_uri'] = $url;
            $options['timeout'] = $time_out;
            !empty($headers) && $options['headers'] = $headers;
            !empty($data) && $options['query'] = $data;
            $response = $this->client->get($urlInfo['path'], $options);
            $t2 = microtime(true);
            $urlData['use_time'] = round($t2-$t1,3);
            $urlData['response'] = $response->getBody()->getContents();
            //Db::table('request_log')->insert($urlData);
            return $urlData['response'];
        } catch (ClientException $e) {
            $t2 = microtime(true);
            $urlData['use_time'] = round($t2-$t1,3);
            $urlData['response'] = $e->getResponse()->getBody()->getContents();
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function pathInfo($url): array
    {
        return parse_url($url);
    }

    public function post(string $url, $data, array $headers = [], int $time_out = 15)
    {
        $urlData = [];
        $t1 = microtime(true);
        $urlData['url'] = $url;
        $urlData['ip'] = Network::ip();
        if (is_array($data)) {
            $urlData['params'] = json_encode($data,JSON_UNESCAPED_SLASHES);
        } else {
            $urlData['params'] = $data;
        }
        try {
            $options = [];
            $options['base_uri'] = $url;
            $options['timeout'] = $time_out;
            $options['max_connections'] = 50;
            !empty($data) && $options[$this->postType] = $data;
            !empty($headers) && $options['headers'] = $headers;
            $http_response = $this->client->post($url, $options);
            $t2 = microtime(true);
            $urlData['use_time'] = round($t2-$t1,3);
            $urlData['response'] = $http_response->getBody()->getContents();
            //Db::table('request_log')->insert($urlData);
            return $urlData['response'];
        } catch (ClientException $e) {
            $t2 = microtime(true);
            $urlData['use_time'] = round($t2-$t1,3);
            $urlData['response'] = $e->getResponse()->getBody()->getContents();
            //Db::table('request_log')->insert($urlData);
            return $urlData['response'];
        }
    }

    /**
     * 设置post请求类型
     * @param string $type  form_params/json
     * @return object
     */
    public function setPostType(string $type):object
    {
        $this->postType = $type;
        return $this;
    }

}
