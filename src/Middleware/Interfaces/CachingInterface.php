<?php
namespace SoapExt\Middleware\Interfaces;

interface CachingInterface {
    
    public function hasFile($name): bool;
    
    public function getFile($name): string;
    
    public function getContent($name): string;
    
    public function putContent($content, $name);
    
    public function getLifetime(): int;
    
    public function clearCache(): bool;
    
}
