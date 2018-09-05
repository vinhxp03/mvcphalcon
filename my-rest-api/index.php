<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;

// Use Loader() to autoload our model
$loader = new Loader();

$loader->registerNamespaces(
    [
        'Store\Toys' => __DIR__ . '/models/',
    ]
);

$loader->register();

$di = new FactoryDefault();

// Set up the database service
$di->set(
    'db',
    function () {
        return new PdoMysql(
            [
                'host'     => 'localhost',
                'username' => 'root',
                'password' => '',
                'dbname'   => 'robots',
            ]
        );
    }
);

$app = new Micro($di);

// Retrieves all robots
$app->get(
    '/api/robots',
    function () use ($app) {
    	// Operation to fetch robot with name $name
        //$phql = "SELECT * FROM Store\Toys\Robot ORDER BY name";

        //$robots = $app->modelsManager->executeQuery($phql);

        $robots = Store\Toys\Robot::find();

        $data = [];

        foreach ($robots as $robot) {
            $data[] = [
                'id'   => $robot->id,
                'name' => $robot->name,
            ];
        }

        echo json_encode($data);
    }
);

// Searches for robots with $name in their name
$app->get(
    '/api/robots/search/{name}',
    function ($name) use ($app) {
        // Operation to fetch robot with name $name
    	$phql = "select * from Store\Toys\Robot where name like :name: order by name";

    	$robots = $app->modelsManager->executeQuery(
            $phql,
            [
                'name' => '%' . $name . '%'
            ]
        );


    	$data = [];

    	foreach ($robots as $robot) {
    		$data[] = [
    			'id' => $robot->id,
    			'name' => $robot->name,
    		];
    	}

    	echo json_encode($data);
    }
);

// Retrieves robots based on primary key
$app->get(
    '/api/robots/{id:[0-9]+}',
    function ($id) use ($app) {
        // Operation to fetch robot with id $id
        $phql = 'select * from Store\Toys\Robot where id = :id:';

        $robot = $app->modelsManager->executeQuery($phql, [
        	'id' => $id
        ])->getFirst();

        // Create a response
        $response = new Response();

        if ($robot == false) {
        	$response->setJsonContent([
        		'status' => 'Not Found!'
        	]);
        } else {
        	$response->setJsonContent([
        		'status' => 'Found!',
        		'data' => [
        			'id' => $robot->id,
        			'name' => $robot->name
        		]
        	]);
        }

        return $response;
    }
);

// Adds a new robot
$app->post(
    '/api/robots',
    function () use ($app) {
        // Operation to create a fresh robot
    	$robot = $app->request->getJsonRawBody();

    	$phql = 'insert into Store\Toys\Robot (name, type, year) values (:name:, :type:, :year:)';

    	$status = $app->modelsManager->executeQuery($phql, [
    		'name' => $robot->name,
    		'type' => $robot->type,
    		'year' => $robot->year
    	]);

    	// Create a response
    	$response = new Response();

    	// Check if insertion was successful
    	if ($status->success()) {
    		// Change the HTTP status
    		$response->setStatusCode(201, 'Created');

    		$robot->id = $status->getModel()->id;

    		$response->setJsonContent([
    			'status' => 'OK',
    			'data' => $robot
    		]);
    	} else {
    		// Change the HTTP status
    		$response->setStatusCode(409, 'Conflict');

    		// Sent error to the client
    		$error = [];

    		foreach ($stauts->getMessages() as $message) {
    			$error[] = $message->getMessage();
    		}

    		$response->setJsonContent([
    			'status' => 'Error',
    			'messages' => $error
    		]);
    	}

    	return $response;
    }
);

// Updates robots based on primary key
$app->put(
    '/api/robots/{id:[0-9]+}',
    function ($id) use ($app) {
        // Operation to update a robot with id $id
        $robot = $app->request->getJsonRawBody();

        $phql = "update Store\Toys\Robot set name = :name: where id = :id:";

        $status = $app->modelsManager->executeQuery($phql, [
        	'id' => $id,
        	'name' => $robot->name
        ]);

        // Create a response
        $response = new Response();

        // Check if the insertion was sucessful
        if ($status->success() === true) {
            $response->setJsonContent([
        		'status' => 'OK'
        	]);
        } else {
        	// Change the HTTP status
        	$response->setStatusCode(409, 'Conflict');

        	$error = [];

        	foreach ($status->getMessages as $message) {
        		$error[] = $message->getMessage();
        	}

        	$response->setJsonContent([
        		'status' => 'Error',
        		'error' => $error
        	]);
        }

        return $response;
    }
);

// Deletes robots based on primary key
$app->delete(
    '/api/robots/{id:[0-9]+}',
    function ($id) use ($app) {
        // Operation to delete the robot with id $id
        $phql = "delete from Store\Toys\Robot where id = :id:";

        $status = $app->modelsManager->executeQuery($phql, [
        	'id' => $id
        ]);

        // Create a response
        $response = new Response();

        // Check if the robot deleted
        if ($status->success()) {
        	$response->setJsonContent([
        		'status' => 'Deleted'
        	]);
        } else {
        	// Change the HTTP status
        	$response->setStatusCode(409, 'Conflict');

        	$error = [];

        	foreach ($status->getMessages() as $message) {
        		$error = $message->getMessage();
        	}

        	$response->setJsonContent([
        		'status' => 'Error',
        		'data' => $error
        	]);
        }

        return $response;
    }
);

$app->handle();