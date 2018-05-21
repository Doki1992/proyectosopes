<?php
# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# imports the Google Cloud client library
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

# instantiates a client
$imageAnnotator = new ImageAnnotatorClient();

# the name of the image file to annotate
$fileName = 'https://www.google.com/imgres?imgurl=https%3A%2F%2Fwww.chiquipedia.com%2Fimagenes%2Fimagenes-flores19.jpg&imgrefurl=https%3A%2F%2Fwww.chiquipedia.com%2Fimagenes-bonitas%2F&docid=oBYNPiKCxvhIUM&tbnid=b8KYXYJ6GVxtuM%3A&vet=10ahUKEwjegfeJ_JXbAhWSxFkKHYMHBH4QMwiaASgBMAE..i&w=1024&h=535&client=ubuntu&bih=670&biw=1317&q=imagenes&ved=0ahUKEwjegfeJ_JXbAhWSxFkKHYMHBH4QMwiaASgBMAE&iact=mrc&uact=8';

# prepare the image to be annotated
$image = file_get_contents($fileName);

# performs label detection on the image file
$response = $imageAnnotator->labelDetection($image);
$labels = $response->getLabelAnnotations();

if ($labels) {
    echo("Labels:" . PHP_EOL);
    foreach ($labels as $label) {
        echo($label->getDescription() . PHP_EOL);
    }
} else {
    echo('No label found' . PHP_EOL);
}