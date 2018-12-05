<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Set credentials */
$cred = set_cred('ddwt18', 'ddwt18');

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

/* Validate authentication */
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
    if (!check_cred($cred)) {
        header("HTTP/1.1 401 Unauthorized");
        $feedback = [
            'type' => 'danger',
            'message' => 'HTTP ERROR 401; The request has not been applied because it lacks valid authentication credentials for the target resource'
        ];
        echo json_encode($feedback);
        exit();
    }
});

/* Create routes */
$router->mount('/api', function() use ($router, $db) {
    http_content_type('application/json');

    /* Fallback route */
    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        $feedback = [
            'type' => 'danger',
            'message' => 'HTTP ERROR 404; Page not found.'
        ];
        echo json_encode($feedback);
        exit();
    });

    /* GET for reading all series */
    $router->get('/series', function() use($db) {
        // Retrieve and output information
        $series = get_series($db);
        echo json_encode($series);
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        $series = get_serieinfo($db, $id);
        echo json_encode($series);
    });

    /* DELETE for removing series */
    $router->delete('/series/(\d+)', function($id) use($db) {
        $feedback = remove_serie($db, $id);
        echo json_encode($feedback);
    });

    /* POST for adding a series */
    $router->post('/series', function() use($db) {
        $feedback = add_serie($db, $_POST);
        echo json_encode($feedback);
    });

    /* PUT for updating a series */
    $router->put('/series/(\d+)', function($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];

        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });

});

/* Run the router */
$router->run();
