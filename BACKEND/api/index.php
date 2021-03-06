<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

function addHeaders(Response $response) : Response {
    $response = $response->withHeader("Content-Type", "application/json")
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "*")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS");
        // ->withHeader("Access-Control-Expose-Headers", "Authorization");

    return $response;
}

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // permet lecture des body en JSON

$app->get('/api/hello/{login}', function (Request $request, Response $response, $args) {
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    $clients = $clientRepository->findAll();

    $response->getBody()->write($args['login']);
    return $response;
});

$app->get('/api/client/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    $client = $clientRepository->find($id);

    $data = array(
        'id' => $client->getIdClient(),
        'lastname' => $client->getNom(),
        'firstame' => $client->getPrenom(),
        'login' => $client->getLogin(),
        'password' => $client->getPassword(),
    );
    $response = addHeaders($response);
    $response->getBody()->write(json_encode($data));
    return $response;
});

$app->post('/api/client/login', function (Request $request, Response $response, $args) {
    $body = $request->getParsedBody(); // Parse le body
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    
    $client = $clientRepository->findOneBy(array(
        'login' => $body['login'],
        'password' => $body['password']
    ));

    $response = addHeaders($response);
    if($client) {
        $data = array(
            'id' => $client->getIdClient(),
            'lastname' => $client->getNom(),
            'firstname' => $client->getPrenom(),
            'login' => $client->getLogin(),
            'password' => $client->getPassword(),
        );
        $response->getBody()->write(json_encode($data));
    } else {
        $response->withStatus(401);
    }
    return $response;
});

$app->post('/api/client', function (Request $request, Response $response, $args) {
    $body = $request->getParsedBody(); // Parse le body
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    
    $client = new Client();
    $client->setNom($body['lastname']);
    $client->setPrenom($body['firstname']);
    $client->setLogin($body['login']);
    $client->setPassword($body['password']);

    $entityManager->persist($client);
    $entityManager->flush();
    
    $response = addHeaders($response);
    $response->withStatus(200);
    return $response;
});

$app->put('/api/client/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $body = $request->getParsedBody(); // Parse le body
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    $client = $clientRepository->find($id);
    
    $client->setNom($body['lastname']);
    $client->setPrenom($body['firstname']);
    $client->setLogin($body['login']);
    $client->setPassword($body['password']);
    
    $entityManager->flush();

    $response = addHeaders($response);
    $response->whithStatus(200);
    return $response;
});

$app->delete('/api/client/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    global $entityManager;
    $clientRepository = $entityManager->getRepository('Client');
    $client = $clientRepository->find($id);

    $entityManager->remove($client);
    $entityManager->flush();
    
    $response = addHeaders($response);
    $response->whithStatus(200);
    return $response;
});

$app->run();

// /!\ enlever vendor du softdeploy.sh et reecrire deploy.sh /!\ \\
