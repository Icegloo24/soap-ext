<?php
namespace SoapExt\Middleware\Tools;

trait UriResolver
{
    
    protected function resolve(string $base, string $reroot)
    {
        if(strpos($reroot, "://") !== false) {
            return $reroot;
        }
        $base = explode("/", $base);
        if(strlen($reroot) > 0) {
            array_pop($base);
        }
        foreach(explode("/", $reroot) as $substr) {
            if(strpos($reroot, '/') === 0 && strlen($substr) > 0) {
                while(in_array($substr, $base)) {
                    array_pop($base);
                }
            }
            if($substr == "..") {
                array_pop($base);
            }else {
                array_push($base, $substr);
            }
        }
        return implode("/", $base);
    }
    
}

