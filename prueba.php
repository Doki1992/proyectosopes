<?php
# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# imports the Google Cloud client library
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Language\LanguageClient;

/*
# instantiates a client
$imageAnnotator = new ImageAnnotatorClient();

# the name of the image file to annotate
$fileName = 'https://cf-cdn.gananci.com/wp-content/uploads/2017/05/felicidad-619x346.jpg';
$fileName1 = 'http://rubenturienzo.com/sites/default/files/styles/flexslider_full/public/29_latristeza.jpg?itok=dMNmqhRI';
# prepare the image to be annotated
$image = file_get_contents($fileName1);

# performs label detection on the image file
$response = $imageAnnotator->labelDetection($image);
$labels = $response->getLabelAnnotations();

if ($labels) {
    echo("Labels:" . PHP_EOL);
    echo(json_encode($labels) .PHP_EOL);
    foreach ($labels as $label) {
        echo($label->getDescription() . PHP_EOL);
    }
} else {
    echo('No label found' . PHP_EOL);
}

*/

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
    foreach ($entities as $entity) {
        printf('Name: %s' . PHP_EOL, $entity['name']);
        printf('Type: %s' . PHP_EOL, $entity['type']);
        printf('Salience: %s' . PHP_EOL, $entity['salience']);
        if (array_key_exists('wikipedia_url', $entity['metadata'])) {
            printf('Wikipedia URL: %s' . PHP_EOL, $entity['metadata']['wikipedia_url']);
        }
        if (array_key_exists('mid', $entity['metadata'])) {
            printf('Knowledge Graph MID: %s' . PHP_EOL, $entity['metadata']['mid']);
        }
        printf(PHP_EOL);
    }
}

analyze_sentiment('I love you');
analyze_entities('I love you');