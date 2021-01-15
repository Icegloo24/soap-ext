<?php
namespace SoapExt\Test;

use SoapExt\Middleware\Interfaces\CachingInterface;

class TestCache implements CachingInterface {
    
    private $location;
    
    public function __construct()
    {
        $this->location = __DIR__."/../../tests/fixtures/wsdl/";
    }
    
    public function hasFile($name): bool
    {
        return file_exists($this->location.$name);
    }

    public function getLifetime(): int
    {
        return 999;
    }

    public function getContent($name): string
    {
        return file_get_contents($this->location.$name);
    }

    public function putContent($content, $name)
    {}

    public function getFile($name): string
    {
        return $this->location.$name;
    }

}

