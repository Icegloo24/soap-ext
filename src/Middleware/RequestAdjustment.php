<?php
namespace SoapExt\Middleware;

use SoapExt\Middleware\Interfaces\RequestAdjustmentInterface;

class RequestAdjustment implements RequestAdjustmentInterface {
    
    public function modifyRequest(string $request): string
    {
        $dom = new \DOMDocument("1.0");
        $dom->loadXML($request);
        $request = $dom->C14N(false, false, null, null);
        //print "\n$request";
        return $request;
    }

}

