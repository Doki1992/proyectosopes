<?php

require_once __DIR__ . '/vendor/autoload.php';
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Language\LanguageClient;
use Google\Cloud\Translate\TranslateClient;

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
        $var = analisis($job['mensaje']);        
        //$var = array('url' => $job['mensaje'], 'content' => $var);
        echo(json_encode($var));
        echo(traslate($var));
    }	
    else{
        $var = analyze_sentiment($job['mensaje']);
        $var .= analyze_entities($job['mensaje']);             
        echo(traslate($var));
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
$var = "";
if ($labels) {

    $var .= "Labels:" . PHP_EOL;
    $content = "";
    foreach ($labels as $label) {
        $var .= $label->getDescription() . PHP_EOL;        
    }
} else {
    $var .= 'No label found' . PHP_EOL;
    $var .= "No label found";
}
return $var;
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
    $var = "";
    $var .= 'Document Sentiment:' . PHP_EOL;
    $var .= '  Magnitude: s' .  $sentiment['magnitude'] . PHP_EOL;
    $var .= '  Score: s' .  $sentiment['score'] .PHP_EOL;
    $var .= PHP_EOL;
    foreach ($annotation->sentences() as $sentence) {
        $var .= 'Sentence: s' .  $sentence['text']['content'] .PHP_EOL;
        $var .= 'Sentence Sentiment:' . PHP_EOL;
        $var .= '  Magnitude: s' .  $sentence['sentiment']['magnitude'].PHP_EOL;
        $var .= '  Score: s' .  $sentence['sentiment']['score'].PHP_EOL;
        $var .= PHP_EOL;
    }
    return $var;
}

function traslate($projectId = 'tidy-strand-201401', $text){
    # Instantiates a client
$translate = new TranslateClient([
    'projectId' => $projectId
]);

# The text to translate

# The target language
$target = 'es';

# Translates some text into Russian
$translation = $translate->translate($text, [
    'target' => $target
]);

echo 'Text: ' . $text . '
Translation: ' . $translation['text'];
}




function analyze_entities($text, $projectId = 'tidy-strand-201401')
{
    // Create the Natural Language client
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the analyzeEntities function
    $annotation = $language->analyzeEntities($text);

    // Print out information about each entity
    $entities = $annotation->entities();
    $var = "";
    foreach ($entities as $entity) {
        $var .= 'Name: s' .  $entity['name'] .PHP_EOL;
        $var .= 'Type: s' .  $entity['type'] .PHP_EOL;
        $var .= 'Salience: s' .  $entity['salience'] .PHP_EOL;
        if (array_key_exists('wikipedia_url', $entity['metadata'])) {
            $var .= 'Wikipedia URL: s' .  $entity['metadata']['wikipedia_url'].PHP_EOL;
        }
        if (array_key_exists('mid', $entity['metadata'])) {
            $var .= 'Knowledge Graph MID: s' .  $entity['metadata']['mid'].PHP_EOL;
        }
        $var .= PHP_EOL;
    }
    return $var;
}