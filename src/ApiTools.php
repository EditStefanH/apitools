<?php 
namespace EditStefanH\Apitools;

use PhpParser\Node\Expr\Cast\String_;

class ApiTools
{
    private $headers;
    private $accessToken;
    private $endPointURL;
        
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        array $headers = [], 
        string $accessToken = '',
        string $endPointURL = ''
    )
    {
        $this->headers = $headers ?? [];
        $this->accessToken = $accessToken ?? '';
        $this->endPointURL = $endPointURL ?? $this->endPointURL;
    }
    
    /**
     * setHeaders
     *
     * @param  array $headers
     * @return void
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
    }
    
    /**
     * setAccessToken
     *
     * @param  string $accessToken
     * @return void
     */
    public function setAccessToken(string $accessToken) {
        $this->accessToken = $accessToken;
    }
    
    /**
     * setEndPointURL
     *
     * @param  string $endPointURL
     * @return void
     */
    public function setEndPointURL(string $endPointURL) {
        $this->endPointURL = $endPointURL;
    }

    /**
     * getHeaders
     *
     * @param  mixed $extraHeaders
     * @return mixed
     */
    private function getHeaders($extraHeaders = [], $token = '') : mixed {
        $headers = $this->headers;
        if ($token != '')  {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        return array_merge($headers, $extraHeaders);
    } 
    
    /**
     * sendRequestProcess
     *
     * @param  mixed $parameters
     * @return void
     */
    public function sendRequestProcess($parameters) {
        $parameters = [
            'action' => $parameters['action'],
            'body' => $parameters['body'] ?? [],
            'method' => $parameters['method'] ?? 'POST',
            'extraHeaders' => $parameters['extraHeaders'] ?? [],
            'bodyType' => $parameters['bodyType'] ?? 'json',
            'showOutput' => $parameters['showOutput'] ?? false ,
            'token' => $parameters['token'] ?? $this->accessToken
        ];
        return $this->sendRequest($parameters);
    }
        
    /**
     * sendRequest
     *
     * @param  array $parameters
     * @return Array
     */
    public function sendRequest(array $parameters) : Array
    {
        $action = $parameters['action'];
        $body = $parameters['body'];
        $method = $parameters['method'] ?? 'POST'; 
        $extraHeaders = $parameters['extraHeaders'] ?? [];
        $bodyType = $parameters['bodyType'] ?? 'json';
        $showOutput = $parameters['showOutput'] ?? false;
        $token = $parameters['token'] ?? $this->accessToken;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endPointURL . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        
        if ($action != 'generateapptoken') {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'Bearer ' . $token);
        }

        if ($bodyType == 'json') {
            $body = json_encode($body);
        } else if ($bodyType == 'http') {
            $body = http_build_query($body);
        } else {
            $body = $body;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders($extraHeaders, $token));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $result = json_decode(curl_exec($ch));
        if ($showOutput) {
            var_dump($result);
        }
        curl_close($ch);
        
        if ((!isset($result->Success)|| $result->Success == false)) {
            echo 'Error: ' . $result->Message . '<br>';
        }
        return isset($result->Result) ? (array) $result->Result : [];
    }
}