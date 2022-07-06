<?php

namespace Cozy\Lib\Guesty;

use Exceptions\Http\Client\MethodNotAllowedException;

class ClientWrapper
{
    /**
     * @var HttpClient
     */
    private $client;
    private $logger;

    public function __construct($baseUrl,$token, $logger=null)
    {
        $this->client = new HttpClient($baseUrl);
        $this->logger= $logger;
    }

    public function updateToken($token){
        $this->header = array(
            "Authorization: Basic $token",
            "Content-Type: application/json"
        );
        return $this;
    }

    /**
     * @param $urlArray
     * @param $header
     * @param $data
     * @return array|mixed
     * @throws LauraException
     */
    public function request($urlArray, $header, $data)
    {
        extract($data);
        $template = $urlArray[1];
        if (preg_match_all("/{(.*?)}/", $urlArray[1], $m)) {
            foreach ($m[1] as $i => $varname) {
                $template = str_replace($m[0][$i], sprintf('%s', $$varname), $template);
                unset($data[$varname]);
            }
        }

        switch (strtolower($urlArray[0])) {

            case "post":
                $data = json_encode($data);
                $response = $this->client->post($template, $header, $data);
                break;
            case "get":
                if ($data) {
                    $query = '?' . http_build_query($data);
                } else {
                    $query = '';
                }
                $response = $this->client->get($template . $query, $header);
                break;
            case "put":
                $data = json_encode($data);
                $response = $this->client->put($template, $header, $data);
                break;
            case "delete":
                if ($data) {
                    $query = '?' . http_build_query($data);
                } else {
                    $query = '';
                }
                $response = $this->client->delete($template . $query, $header);
                break;
            default:
                throw new MethodNotAllowedException("invalid request type");
        }

        if (is_bool($response) && $response == true) {
            return true;
        }

        $data = json_decode($response, true);

        if (is_null($data)) {
            $data = $response;
        }
        return $data;
    }
}
