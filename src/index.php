<?php

use Slim\Http\Request;
use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

$config = ['settings' => [
	'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

$app->get('/history', function (Request $request, Response $response) {
	return $response->withJson(json_decode(file_get_contents(__DIR__ . '/../var/history.json'), true));
});
$app->delete('/history', function (Request $request, Response $response) {
	file_put_contents(__DIR__ . '/../var/history.json', '[]');
	return $response->write('');
});
$app->put('/configure-endpoint', function (Request $request, Response $response) {
	$jsonData = $request->getParsedBody();
	addToConfiguredEndpoints(
		$jsonData['method'],
		$jsonData['endpoint'],
		$jsonData['returnHttpCode'],
		$jsonData['returnBody']
	);
	return $response->write('');
});
$app->delete('/configure-endpoint', function (Request $request, Response $response) {
	file_put_contents(__DIR__ . '/../var/configured-endpoints.json', '[]');
	return $response->write('');
});

function addToConfiguredEndpoints(
	string $method,
	string $endpoint,
	int $returnHttpCode,
	string $returnBody
)
{
	$fileAddress = __DIR__ . '/../var/configured-endpoints.json';
	$jsonArray = json_decode(file_get_contents($fileAddress), true);

	if (empty(findConfiguredEndpoint($method, $endpoint)) === false) {
		return;
	}

	$jsonArray[] = [
		'method' => $method,
		'endpoint' => $endpoint,
		'returnHttpCode' => $returnHttpCode,
		'returnBody' => $returnBody,
	];

	file_put_contents($fileAddress, json_encode($jsonArray));
}

function findConfiguredEndpoint(string $method, string $endpoint)
{
	$fileAddress = __DIR__ . '/../var/configured-endpoints.json';
	$configuredEndpoints = json_decode(file_get_contents($fileAddress), true);

	foreach ($configuredEndpoints as $configuredEndpoint) {
		if ($configuredEndpoint['method'] === $method && $configuredEndpoint['endpoint'] === $endpoint) {
			return $configuredEndpoint;
		}
	}

	return [];
}

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

$app->any('/[{params:.*}]', function (Request $request, Response $response) {
	addToHistory($request);

	$configuredEndpoint = findConfiguredEndpoint($request->getMethod(), $request->getUri()->getPath());

	if (empty($configuredEndpoint) === false) {
		return $response->withStatus($configuredEndpoint['returnHttpCode'])->write($configuredEndpoint['returnBody']);
	}

	return $response->write('');
});

// Run app
$app->run();
