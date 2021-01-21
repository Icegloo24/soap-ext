<?php
namespace SoapExt\Middleware\Native;

use SoapExt\Middleware\Interfaces\CachingInterface;

class Cache implements CachingInterface {
    
    private $enabled;
    
    private $target_dir;
    
    private $lifetime;
    
    public function __construct() {
        $this->enabled = true;
        $this->target_dir = sys_get_temp_dir().DIRECTORY_SEPARATOR."WSDL_CACHE";
        $this->lifetime = 120;
    }
    
    
    public function getContent($name): string {
        if($this->hasFile($name)) {
            return file_get_contents($this->getFile($name));
        }
        return '';
    }
    
    
    public function getFile($name): string {
        if(!file_exists($this->target_dir) && !is_dir($this->target_dir)) {
            @mkdir($this->target_dir, 0777, true);
        }
        $filename = $this->target_dir.DIRECTORY_SEPARATOR.'wsdl_'.md5($name).'.cache';
        if(file_exists($filename) && filemtime($filename) + $this->lifetime < time()) {
            unlink($filename);
        }
        
        return $filename;
    }
    
    
    public function hasFile($name): bool {
        return $this->enabled && file_exists($this->getFile($name));
    }


    public function putContent($name, $content) {
        if($this->enabled) {
            if($this->hasFile($name)) {
                unlink($this->getFile($name));
            }
            //print "writeTo: ".$this->getFile($name);
            $file = fopen($this->getFile($name), 'w');
            file_put_contents($this->getFile($name), $content);
            fclose($file);
        }
    }

    
    public function getLifetime(): int {
        return $this->lifetime;
    }
    
    
    public function clearCache(): bool
    {
        if(is_dir($this->target_dir)) {
            foreach(scandir($this->target_dir) as $file) {
                if(strpos($file, 'cache')) {
                    unlink($this->target_dir.DIRECTORY_SEPARATOR.$file);
                }
            }
            foreach(scandir($this->target_dir) as $file) {
                if(strpos($file, 'cache')) {
                    return false;
                }
            }
        }
        return true;
    }

    
}

