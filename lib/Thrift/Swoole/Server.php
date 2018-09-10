<?php

namespace Thrift\Swoole;

use Thrift;

class Server
{
    //1:轮询 3:争抢
    const DISPATCH_MODE = 3;

    protected $ip;
    protected $port;
    protected $worker;

    protected $serviceName;

    protected $processor;

    public function __construct($ip, $port, $serviceName, $processor, $worker = 8)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->worker = $worker <= 0 ? 8 : intval($worker);

        $this->serviceName = $serviceName;

        $this->processor = $processor;
    }

    public function start()
    {
        echo "ThriftServer Start\n";
    }

    public function notice($log)
    {
        echo $log . "\n";
    }

    public function receive($server, $fd, $reactor_id, $data)
    {
        $socket = new Socket();
        $socket->setHandle($fd);
        $socket->buffer = $data;
        $socket->server = $server;
        $protocol = new Thrift\Protocol\TBinaryProtocol($socket, false, false);

        try {
            $protocol->fname = $this->serviceName;
            $this->processor->process($protocol, $protocol);
        } catch (\Exception $e) {
            $this->notice('CODE:' . $e->getCode() . ' MESSAGE:' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function serve()
    {
        if (empty($this->ip) or empty($this->port)) {
            $this->notice('IP | Port Fault!');
            return false;
        }
        $server = new \swoole_server($this->ip, $this->port);

        $server->on('workerStart', [$this, 'start']);
        $server->on('receive', [$this, 'receive']);

        $server->set([
            'worker_num' => $this->worker,
            'dispatch_mode' => self::DISPATCH_MODE, //1: 轮循, 3: 争抢
            'open_length_check' => true, //打开包长检测
            'package_max_length' => 8192000, //最大的请求包长度,8M
            'package_length_type' => 'N', //长度的类型，参见PHP的pack函数
            'package_length_offset' => 0, //第N个字节是包长度的值
            'package_body_offset' => 4, //从第几个字节计算长度
        ]);

        $server->start();

        return true;
    }
}
