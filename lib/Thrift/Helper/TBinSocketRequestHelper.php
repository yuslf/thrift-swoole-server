<?php

namespace Thrift\Helper;

use Thrift\Transport\TSocket;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;

class TBinSocketRequestHelper extends TRequestHelper
{
    protected $ip;

    protected $port;

    protected $socket;

    public function client($ip, $port)
    {
        if (empty($this->namespace)) {
            throw new \Exception('没有设置Thrift客户端命名空间，请先调用 initLoader 方法！');
            return false;
        }

        $this->ip = $ip;
        $this->port = $port;

        $this->socket = new TSocket($this->ip, $this->port);
        $this->transport = new TFramedTransport($this->socket);

        $this->protocol = new TBinaryProtocol($this->transport);

        $namespace = explode("\\", $this->namespace);

        $client = $this->namespace . "\\" . $namespace[count($namespace) - 1] . 'Client';

        if (! class_exists($client)) {
            throw new \Exception('Thrift客户端不存在，请先调用 initLoader 方法！');
            return false;
        }

        $this->client = new $client($this->protocol);

        return $this;
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function __call($func, $args)
    {
        if (empty($this->client)) {
            throw new \Exception('Thrift客户端不存在，请先调用 initLoader 和 initClient 方法！');
            return false;
        }

        if (! method_exists($this->client, $func)) {
            throw new \Exception('Thrift客户端方法:' . $func . ' 不存在！');
            return false;
        }

        $is_once = ! $this->transport->isOpen();

        if ($is_once) {
            $this->transport->open();
        }

        $ret = call_user_func_array(array($this->client, $func), $args);

        if ($is_once) {
            $this->transport->close();
        }

        return $ret;
    }

    public function open()
    {
        if (! $this->transport->isOpen()) {
            $this->transport->open();
        }

        return $this;
    }

    public function close()
    {
        if ($this->transport->isOpen()) {
            $this->transport->close();
        }

        return $this;
    }

    public function struct($name, $val)
    {
        $struct = $this->namespace . "\\" . $name;
        return new $struct($val);
    }
}
