<?php

namespace Thrift\Helper;

use Thrift\ClassLoader\ThriftClassLoader;

class TRequestHelper
{
    protected $loader;

    protected $baseDir;

    protected $serverDir;

    protected $baseNamespace;

    protected $serverNamespace;

    protected $swooleServer;

    public function loader($baseNamespace, $baseDir, $serverNamespace, $serverDir)
    {
        $this->baseDir = $baseDir;
        $this->baseNamespace = $baseNamespace;

        $loader = new ThriftClassLoader();
        $loader->registerNamespace($baseNamespace, $baseDir . '/gen-php');
        $loader->registerDefinition($baseNamespace, $baseDir . '/gen-php');

        $loader->registerNamespace($serverNamespace, $serverDir);

        $loader->register();

        $this->loader = $loader;

        return $this;
    }

    public function getLoader()
    {
        return $this->loader;
    }

}
