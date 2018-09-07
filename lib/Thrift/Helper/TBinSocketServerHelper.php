<?php

namespace Thrift\Helper;

use Thrift\Swoole\Server;

class TBinSocketServerHelper extends TRequestHelper
{
    public function server($ip, $port, $handler = '', $worker = 8)
    {
        if (empty($this->loader)) {
            throw new \Exception('请先调用 loader 方法！');
            return false;
        }

        if (empty($this->baseNamespace)){
            throw new \Exception('请先调用 loader 方法！');
            return false;
        }

        $serviceName = explode("\\", $this->baseNamespace);
        $serviceName = $serviceName[count($serviceName) - 1];

        $handler = $this->serverNamespace . "\\" . $handler . 'Handler';
        if (! class_exists($handler)) {
            throw new \Exception('请先调用 loader 方法！');
            return false;
        }
        $handler = new $handler();

        $processor = $this->baseNamespace . "\\" . $serviceName . 'Processor';
        if (! class_exists($processor)) {
            throw new \Exception('请先调用 loader 方法！');
            return false;
        }
        $processor = new $processor($handler);

        $this->swooleServer = new Server($ip, $port, $serviceName, $processor, $worker);

        if (empty($this->swooleServer)) {
            throw new \Exception('请先调用 loader 方法！');
            return false;
        }

        return $this;
    }

    public function start()
    {
        $this->swooleServer->serve();
    }
}
