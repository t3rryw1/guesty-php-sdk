<?php

namespace Cozy\Lib\Guesty;

use Exceptions\Http\Client\MethodNotAllowedException;
use Monolog\Logger;

class ClientWrapper
{
    /**
     * @var HttpClient
     */
    private $client;
    /** @var Logger */
    private $logger;
    /**@var bool */
    private $dryRun;

    public function __construct(
        string $baseUrl,
        bool $dryRun = true,
        Logger $logger = null)
    {
        $this->client = new HttpClient($baseUrl);
        $this->logger = $logger;
        $this->dryRun = $dryRun;
    }

    /**
     * @param $urlArray
     * @param $header
     * @param $data
     * @return array|mixed
     * @throws LauraException
     */
    public function request(
        $urlArray,
        $header,
        $data,
        $jsonEncode = true,
        $dryRun=true)
    {
        extract($data);
        $template = $urlArray[1];
        if (preg_match_all("/{(.*?)}/", $urlArray[1], $m)) {
            foreach ($m[1] as $i => $varname) {
                $template = str_replace($m[0][$i], sprintf('%s', $$varname), $template);
                unset($data[$varname]);
            }
        }
        //logger logic
        if($this->logger){
            $this->logger->debug("Request - URL: $template",['method'=>$urlArray[0],'data'=>$data]);
        }

        $requestDryRun = $this->dryRun && $dryRun;

        switch (strtolower($urlArray[0])) {

            case "post":
                if($requestDryRun){
                    return null;
                }
                if ($jsonEncode) {
                    $data = json_encode($data);
                }
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
                if($requestDryRun){
                    return null;
                }
                if ($jsonEncode) {
                    $data = json_encode($data);
                }
                $response = $this->client->put($template, $header, $data);
                break;
            case "delete":
                if($requestDryRun){
                    return null;
                }
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

        if($this->logger){
            $this->logger->debug("Response - ",['method'=>$urlArray[0],'data'=>$data]);
        }

        if (is_null($data)) {
            $data = $response;
        }
        return $data;
    }

    public function getLastResponseCode(){
        return $this->client->getResponseCode()??null;
    }
}
