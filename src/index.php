<?php

use Slim\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$config = ['settings' => [
	'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

$app->get('/history', function (Request $request, $response) {
	return $response->write(file_get_contents(__DIR__ . '/../var/history.json'));
});
$app->delete('/history', function (Request $request, $response) {
	file_put_contents(__DIR__ . '/../var/history.json', '[]');
	return $response->write('');
});

function addToHistory(Request $request)
{
	$fileAddress = __DIR__ . '/../var/history.json';
	$jsonArray = json_decode(file_get_contents($fileAddress), true);


	$path = $request->getUri()->getPath();
	if ($request->getUri()->getQuery() !== '') {
		$path .= '?' . $request->getUri()->getQuery();
	}

	$item = [
		'method' => $request->getMethod(),
		'endpoint' => $path,
		'headers' => $request->getHeaders(),
		'body' => $request->getBody()->__toString()
	];
	$jsonArray[] = $item;

	file_put_contents($fileAddress, json_encode($jsonArray));
}

$app->any('/[{params:.*}]', function (Request $request, $response) {
	addToHistory($request);
	return $response->write('');
});

// Run app
$app->run();
