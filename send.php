<?php 
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('172.17.0.3', 5672, 'guest', 'guest');
$channel = $connection->channel();

try{
	$channel->queue_declare('1', false, true, false, false);	
}
catch(Exception $e)
{
	echo($e->getMessage());
}

$msg1 = array(
	'user' => 'mike',
	'mensaje' => 'hola mundo 1',
	'tipo' => 'generico'
);
$msg = new AMQPMessage(json_encode($msg1, JSON_UNESCAPED_SLASHES), array('delivery_mode' => 2));
$channel->basic_publish($msg, '', '2');

$channel->close();
$connection->close();

echo('llego, server1');
