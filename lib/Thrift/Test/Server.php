<?php
require_once './vendor/autoload.php';

use Thrift\Helper\TBinSocketServerHelper;

$helper = new TBinSocketServerHelper();

$helper->loader('Rpc\HelloSwoole', './IDL', 'Service\HelloSwoole', './')
       ->server('127.0.0.1', '8091')
       ->start();
