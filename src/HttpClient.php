<?php

namespace Cozy\Lib\Guesty;

use Exceptions\IO\Network\UnexpectedResponseException;


class HttpClient
{
    private $baseUrl;
    private $responseCode;

    function getResponseCode()
    {
        return $this->responseCode;
    }

    function setResponseCode($code)
    {
        $this->responseCode = $code;
    }

    public function __construct($baseUrl = null)
    {
        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }
    }

    /**
     * @param $url
     * @param $headerArray
     * @return bool|string
     * @throws UnexpectedResponseException
     */
    public function get($url, $headerArray)
    {
        if ($this->baseUrl) {
            $url = trim($this->baseUrl . '/', "/") . "/" . ltrim($url, '/');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $server_output = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new UnexpectedResponseException(curl_error($ch));
        }
        $this->setResponseCode(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

        curl_close($ch);
        return $server_output;
    }

    /**
     * @param $url
     * @param $headerArray
     * @param $data
     * @return bool|string
     * @throws UnexpectedResponseException
     */
    public function post($url, $headerArray, $data)
    {
        if ($this->baseUrl) {
            $url = trim($this->baseUrl . '/', '/') . "/" . ltrim($url, '/');
        }

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $server_output = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new UnexpectedResponseException(curl_error($ch));
        }

        $this->setResponseCode(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

        curl_close($ch);

        if (strcasecmp($server_output, 'ok') == 0) {
            return true;
        }

        return $server_output;
    }

    /**
     * @param $url
     * @param $headerArray
     * @param $data
     * @return bool|string
     * @throws UnexpectedResponseException
     */
    public function put($url, $headerArray, $data)
    {
        if ($this->baseUrl) {
            $url = trim($this->baseUrl . '/', '/') . "/" . ltrim($url, '/');
        }

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $server_output = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new UnexpectedResponseException(curl_error($ch));
        }

        $this->setResponseCode(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

        curl_close($ch);

        if (strcasecmp($server_output, 'ok') == 0) {
            return true;
        }

        return $server_output;
    }

    /**
     * @param $url
     * @param $headerArray
     * @return bool|string
     * @throws UnexpectedResponseException
     */
    public function delete($url, $headerArray)
    {
        if ($this->baseUrl) {
            $url = trim($this->baseUrl . '/') . "/" . ltrim($url, '/');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $server_output = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new UnexpectedResponseException(curl_error($ch));
        }
        $this->setResponseCode(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

        curl_close($ch);

        if (strcasecmp($server_output, 'ok') == 0) {
            return true;
        }

        return $server_output;
    }
}
