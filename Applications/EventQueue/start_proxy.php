<?php 
use \Workerman\Worker;
// use \Workerman\Connection\AsyncTcpConnection;
use \Workman\Autoloader;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';

# Create a TCP worker.
$worker = new Worker('tcp://0.0.0.0:8080');
$worker->count = 1;
$worker->name = 'EventQueueProxy';
$worker->onMessage = function($connection, $message) {
  try{
    $data = json_decode($message,true);
    
    $ch = curl_init();
    $get = function() use($ch,$data){

      $params = "{$data['url']}?";
      foreach($data['params'] as $key=>$value){
        $params .= "{$key}={$value}&";
      }

      curl_setopt ( $ch, CURLOPT_URL, substr($params,0,-1));
      curl_setopt ( $ch, CURLOPT_POST, 0 );
      curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 0 );
    };

    $post = function() use($ch,$data){
      curl_setopt ( $ch, CURLOPT_URL, $data['url'] );
      curl_setopt ( $ch, CURLOPT_POST, 1 );
      curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 0 );
      curl_setopt ( $ch, CURLOPT_POSTFIELDS,$data['params'] );
    };

    $action = ['get'=>$get,'post'=>$post];
    $action[$data['method']]();


    curl_exec($ch);
    curl_close($ch);

  }catch(\Exception $e){
    echo $e->getMessage();
  }
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
  Worker::runAll();
}