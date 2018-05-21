<?php

require_once __DIR__ . '/vendor/autoload.php';
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
$valu = rand(1,3);
define("RABBITMQ_HOST", '35.229.95.28');
define("RABBITMQ_PORT", 5672);
define("RABBITMQ_USERNAME", "guest");
define("RABBITMQ_PASSWORD", "guest");
define("RABBITMQ_QUEUE_NAME", $valu);

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
    RABBITMQ_HOST, 
    RABBITMQ_PORT, 
    RABBITMQ_USERNAME, 
    RABBITMQ_PASSWORD
);


$channel = $connection->channel();
# Create the queue if it doesnt already exist.
$channel->queue_declare(
    $queue = RABBITMQ_QUEUE_NAME,
    $passive = false,
    $durable = true,
    $exclusive = false,
    $auto_delete = false,
    $nowait = false,
    $arguments = null,
    $ticket = null
);


$callback = function($msg){
    $job = json_decode($msg->body, $assocForm=true);
#$m = new MongoClient();
#$bd = $m->practica;
#$colección = $bd->pr;
#$colección->insert([ 'user' => $job['user'], 'mensaje' => $job['mensaje'], 'tipo' => $job['tipo'] );

#$cliente = new MongoDB\Client("mongodb://localhost:27017", [
#	'username' => 'mike',
#	'password' => 'mike',
#	'authSource' => 'admin',	
#	'db' => 'admin'
#]);
#$colección = $cliente->admin->beers;
#$resultado = $colección->insertOne([ 'user' => $job['user'], 'mensaje' => $job['mensaje'], 'tipo' => $job['tipo']] );

   # echo " [x] Done", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    if($job['type'] == 'Image'){
        analisis($job['mensaje']);
    }	
    else{
        analyze_sentiment($job['mensaje']);
    }
};

$channel->basic_qos(null, 1, null);

$channel->basic_consume(
    $queue = RABBITMQ_QUEUE_NAME,
    $consumer_tag = '',
    $no_local = false,
    $no_ack = false,
    $exclusive = false,
    $nowait = false,
    $callback
);

while (count($channel->callbacks)) 
{
	    
    $channel->wait();
}

$channel->close();
$connection->close();


function analisis($fileName)
{
    $imageAnnotator = new ImageAnnotatorClient();

# the name of the image file to annotate
$fileName = $fileName;
$fileName1 = $fileName;
# prepare the image to be annotated
$image = file_get_contents($fileName1);

# performs label detection on the image file
$response = $imageAnnotator->labelDetection($image);
$labels = $response->getLabelAnnotations();

if ($labels) {
    echo("Labels:" . PHP_EOL);
    $content = "";
    foreach ($labels as $label) {
        echo($label->getDescription() . PHP_EOL);
        $content .= $label->getDescription() . PHP_EOL;
    }
} else {
    echo('No label found' . PHP_EOL);
    $content = "No label found";
}

}

function analyze_sentiment($text, $projectId = 'tidy-strand-201401')
{
    // Create the Natural Language client
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the analyzeSentiment function
    $annotation = $language->analyzeSentiment($text);

    // Print document and sentence sentiment information
    $sentiment = $annotation->sentiment();
    printf('Document Sentiment:' . PHP_EOL);
    printf('  Magnitude: %s' . PHP_EOL, $sentiment['magnitude']);
    printf('  Score: %s' . PHP_EOL, $sentiment['score']);
    printf(PHP_EOL);
    foreach ($annotation->sentences() as $sentence) {
        printf('Sentence: %s' . PHP_EOL, $sentence['text']['content']);
        printf('Sentence Sentiment:' . PHP_EOL);
        printf('  Magnitude: %s' . PHP_EOL, $sentence['sentiment']['magnitude']);
        printf('  Score: %s' . PHP_EOL, $sentence['sentiment']['score']);
        printf(PHP_EOL);
    }
}