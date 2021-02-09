<?php
namespace SoapExt\Middleware\Native;

use SoapExt\Middleware\Interfaces\CurlInterface;

class Curl implements CurlInterface {
    
    const USER_AGENT = 'PHP-SOAP/\Soap-Ext\SoapClient';
    const ERROR_MAP = [
        1=>'PROTOCOL',
        3=>'FORMAT',
        5=>'CURL',
        6=>'CURL',
        7=>'CURL',
        9=>'CURL',
        28=>'TIMEOUT',
        35=>'CURL',
        41=>'COMPRESS',
        51=>'VERIFICATION',
        52=>'EMPTY',
        53=>'SSL',
        54=>'SSL',
        55=>'CURL',
        56=>'EMPTY',
        58=>'SSL',
        59=>'SSL',
        60=>'SSL',
        61=>'ENCODING',
        65=>'CURL',
        66=>'SSL',
        67=>'SSL',
        77=>'SSL',
        80=>'SSL',
    ];
    
    private $debug = false;
    
    private $ch;
    
    private $lastResponse="";
    
    private $lastResponseHeader="";
    
    private $lastResponseBody="";
    
    private $lastErrorMessage="";
    
    private $lastError="";
    
    public function __construct(array $options = array()) {
        if(!isset($options['user_agent'])) {
            $options['user_agent'] = self::USER_AGENT;
        }
        $this->ch = curl_init();
        $curlOptions = array(
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FAILONERROR => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => true,
            CURLOPT_USERAGENT => $options['user_agent'],
            CURLINFO_HEADER_OUT => true,
        );
        curl_setopt_array($this->ch, $curlOptions);
        
        if(isset($options['connection_timeout'])) {
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $options['connection_timeout']);
        }
        if(isset($options['timeout'])) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $options['timeout']);
        }
        if(isset($options['proxy_host'])) {
            $port = isset($options['proxy_port']) ? $options['proxy_port'] : 8080;
            curl_setopt($this->ch, CURLOPT_PROXY, $options['proxy_host'] . ':' . $port);
        }
        if(isset($options['proxy_user'])) {
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $options['proxy_user'] . ':' . $options['proxy_password']);
        }
        if(isset($options['login'])) {
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($this->ch, CURLOPT_USERPWD, $options['login'].':'.$options['password']);
        }
        if(isset($options['local_cert'])) {
            curl_setopt($this->ch, CURLOPT_SSLCERT, $options['local_cert']);
            curl_setopt($this->ch, CURLOPT_SSLCERTPASSWD, $options['passphrase']);
        }
        if (isset($options['compression']) && !($options['compression'] & SOAP_COMPRESSION_ACCEPT)) {
            curl_setopt($this->ch, CURLOPT_ENCODING, 'identity');
        }
        if(isset($options['debug'])) {
            $this->debug = $options['debug'];
        }
        if($this->debug) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 8);
        }
    }
    
    
    public function __destruct() {
        curl_close($this->ch);
    }
    
    
    public function execute($location, $request = null, $requestHeaders = array()): bool {
        
        curl_setopt($this->ch, CURLOPT_URL, $location);
        
        if(!is_null($request)) {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request);
        }
        
        if(count($requestHeaders) > 0) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $requestHeaders);
        }
        
        $this->lastResponse = curl_exec($this->ch);
        
        if($this->lastResponse === false) {
            $this->lastError = isset(self::ERROR_MAP[curl_errno($this->ch)])?self::ERROR_MAP[curl_errno($this->ch)]:"HTTP";
            $this->lastErrorMessage = curl_error($this->ch);
            return false;
        }
        
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $this->lastResponseHeader = substr($this->lastResponse, 0, $header_size);
        $this->lastResponseBody = substr($this->lastResponse, $header_size);
        
        return true;
    }
    
    public function resolveUri(string $base, string $reroot): string
    {
        if(strpos($reroot, "https://") !== false || strpos($reroot, "http://") !== false) {
            return $reroot;
        }
        $base = explode("/", $base);
        if(strlen($reroot) > 0) {
            array_pop($base);
        }
        foreach(explode("/", $reroot) as $substr) {
            if($substr == "..") {
                array_pop($base);
            }else {
                array_push($base, $substr);
            }
        }
        return implode("/", $base);
    }
    
    public function getLastResponse(): string {
        return $this->lastResponse;
    }
    
    public function getLastResponseHeader(): string {
        return $this->lastResponseHeader;
    }
    
    public function getLastResponseBody(): string {
        return $this->lastResponseBody;
    }
    
    public function getLastError(): string {
        return $this->lastError;
    }
    
    public function getLastErrorMessage(): string {
        return $this->lastErrorMessage;
    }
    
    public function getLastRequestHeader(): string {
        return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
    }
}
