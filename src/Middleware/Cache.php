<?php
namespace SoapExt\Middleware;

use SoapExt\Middleware\Interfaces\CachingInterface;

class Cache implements CachingInterface {
    
    private $enabled;
    
    private $target_dir;
    
    private $lifetime;
    
    public function __construct() {
        $this->enabled = false;
        $this->target_dir = ini_get('soap.wsdl_cache_dir');
        $this->lifetime = 120;
    }
    
    
    public function getContent($name): string {
        if($this->hasFile($name)) {
            return file_get_contents($this->getFile($name));
        }
        return '';
    }
    
    
    public function getFile($name): string {
        //print "\n" . $this->target_dir.DIRECTORY_SEPARATOR.'wsdl_'.md5($name).'.cache'."\n";
        return $this->target_dir.DIRECTORY_SEPARATOR.'wsdl_'.md5($name).'.cache';
    }
    
    
    public function hasFile($name): bool {
        return $this->enabled && file_exists($this->getFile($name)) && filemtime($this->getFile($name)) + $this->lifetime > time();
    }


    public function putContent($name, $content) {
        if($this->enabled) {
            if($this->hasFile($name)) {
                unlink($this->getFile($name));
            }
            $file = fopen($this->getFile($name), 'w');
            file_put_contents($this->getFile($name), $content);
            fclose($file);
        }
    }
    
    
    private function createFile($name) {
        
    }

    
    public function getLifetime(): int {
        return $this->lifetime;
    }
    
}

