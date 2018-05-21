<?php 
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('172.17.0.3', 5672, 'guest', 'guest');
$channel = $connection->channel();

try{
  $channel->queue_declare('2', false, true, false, false);  
}
catch(Exception $e)
{
  echo($e->getMessage());
}

$msg1 = getMessage();

$msg = new AMQPMessage(json_encode($msg1, JSON_UNESCAPED_SLASHES), array('delivery_mode' => 2));
$channel->basic_publish($msg, '', '2');

$channel->close();
$connection->close();

echo('llego, server1');

function getMessage()
{
  $type = $_GET['id'];
  switch ($type) {
    case 1:
      $vect = array(
        'user' => 'mike', 
        'mensaje' => genMensaje(),
        'type' => 'text',
      );
      return $vect;
      break;
    case 2:
      $vect = array(
        'user' => 'mike', 
        'mensaje' => getMessage(),
        'type' => 'Image',
      );
      return $vect;
      break;      
    case 3:
    $vect = array(
        'user' => 'mike', 
        'mensaje' => getMessage(),
        'type' => 'Image',
      );
    return $vect;
      break;            
    default:
      # code...
      break;
  }
}

function genMensaje(){
  $cont = rand(1,3);
  if ($cont == 1){
    return "hola mundo";
  }
  else if($cont == 2){
    return "te odio";
  }
  else if($cont == 3){
    return "te amo";
  }

  return "fijo";
}

function getImage(){
  $cont = rand(1,3);
  if ($cont == 1){
    return 'https://cf-cdn.gananci.com/wp-content/uploads/2017/05/felicidad-619x346.jpg';
  }
  else if($cont == 2){
    return $fileName1 = 'http://rubenturienzo.com/sites/default/files/styles/flexslider_full/public/29_latristeza.jpg?itok=dMNmqhRI';
  }
  else if($cont == 3){
    return $fileName1 = 'http://rubenturienzo.com/sites/default/files/styles/flexslider_full/public/29_latristeza.jpg?itok=dMNmqhRI'; 
  } 
}