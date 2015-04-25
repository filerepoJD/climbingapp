<?php

/**
*versie 27/01/2015
*
*/

require_once '/include/PassHash.php';
require_once '/include/DbHandler.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * User Registration
 * url - /register
 * method - POST
 * params - naam, voornaam, email, username, psw
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('naam','voornaam', 'email', 'username','password'));

            $response = array();

            // reading post params
            $name = $app->request->post('naam');            
            $firstname = $app->request->post('voornaam');
            $email = $app->request->post('email');
            $username = $app->request->post('username');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createUser($name, $firstname ,$email, $username ,$password);
            
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registering";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this user already existed";
            }else if ($res == USERNAME_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this username already existed";
            }
            else if ($res == EMAIL_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
            }
            // echo json response
            echoRespnse(201, $response);
        });

        
/**
 * User Login
 * url - /loginEmail
 * method - POST
 * params - voornaam, email, username, psw
 */
$app->post('/loginEmail', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();
            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLoginEmail($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                     $response["valid"] = true;
                    $response['name'] = $user['name'];
                    $response['firstname'] = $user['firstname'];
                    $response['userID'] = $user['userID'];
                    $response['username'] = $user['username'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });
        

 /**
 * User Login
 * url - /loginUsername
 * method - POST
 * params - voornaam, email, username, psw
 */
$app->post('/loginUsername', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username', 'password'));
            // reading post params
            $username = $app->request()->post('username');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLoginUsername($username, $password)) {
                // get the user by email
                $user = $db->getUserByUsername($username);

                if ($user != NULL) {
                    $response["valid"] = true;
                    $response['name'] = $user['name'];
                    $response['firstname'] = $user['firstname'];
                    $response['username'] = $user['username'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                    $response['userID'] = $user['userID'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }
            echoRespnse(200, $response);
        });


/**
 * User delete
 * url - /deleteUser
 * method - POST
 * params - api
 */
$app->post('/deleteUser', function() use ($app) {
            // check for required params
            verifyRequiredParams(userAPI);

            $response = array();

            // reading post params
            $userAPI = $app->request->post('userAPI');            
                        
            $db = new DbHandler();
            $res = $db->deleteUser ($userAPI);
            
            if ($res == USER_DELETE_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "User successfully deleted";
            } else if ($res == USER_DELETE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while deleting";
            
            }
            // echo json response
            echoRespnse(201, $response);
        });



//----Crag Crud's


      
        /**
 * crags
 * url - /crags
 * method - GET
 * 
 */
$app->get('/crags', 'authenticate', function() {
            global $user_id;    
            // check for required params
            
            $response["valid"] = true;
            $response["crags"] = array();          
            $db = new DbHandler();

            // fetching all routes
            $result = $db->getAllCrags();
            

            // looping through result and preparing tasks array
            while ($crag= $result->fetch_assoc()) {
                $tmp = array();
                $tmp["cragID"] = $crag["ID"];   
                $tmp["naam"] = $crag["naam"];                             
                array_push($response["crags"], $tmp);
            }
             echoRespnse(200, $response);
         });  
        
        
    /**
 * sectors of crag
 * url - /sectorsOfCrag
 * method - POST
 * params - cragID
 */
$app->post('/sectorsOfCrag', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('cragID'));
            $cragID = $app->request()->post('cragID');
            
            $response["valid"] = true;
            $response["sectors"] = array();          
            $db = new DbHandler();

            // fetching all routes
            $result = $db->getAllCragSectors($cragID);

            $response["error"] = false;
            $response["sectors"] = array();

            // looping through result and preparing tasks array
            while ($sector= $result->fetch_assoc()) {
                $tmp = array();
                $tmp["sectorID"] = $sector["ID"];   
                $tmp["naam"] = $sector["naam"];                             
                array_push($response["sectors"], $tmp);
            }
             echoRespnse(200, $response);
         });


        
 /**
 * Listing all routes of a Crag
 * method POST
 * url /routesOfCrag          
 */
$app->post('/routesOfCrag', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('cragID'));
            $cragID = $app->request()->post('cragID');

            $response["error"] = false;
            $response["routes"] = array();
            $db = new DbHandler();
            
            // fetching all routes
            $result = $db->getAllCragRoutes($cragID);

            // looping through result and preparing routes array
            while ($route= $result->fetch_assoc()) {
                $tmp = array();
                $tmp["naam"] = $route["naam"];
                $tmp["niveau"] = $route["niveau"];                
                array_push($response["routes"], $tmp);
            }

            echoRespnse(200, $response);
        });



/**
 * add Crag
 * url - /addCrag
 * method - POST
 * params - naam, locatie
 */
$app->post('/addCrag', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('naam','locatie'));

            $response = array();

            // reading post params
            $name = $app->request->post('naam');            
            $location = $app->request->post('locatie');
            
            $db = new DbHandler();
            $res = $db->createCrag($name, $location );
            
            if ($res == CRAG_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Crag added";
            } else if ($res == CRAG_CREATED_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while adding crag";
            }else if ($res == CRAG_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "A crag with this name already exists";
            }
            
            // echo json response
            echoRespnse(201, $response);
        });


/**
 * Updating existing crag
 * method PUT
 * params cragID, naam, locatie
 * url - /updateCrag/:cragID
 */
$app->put('/updateCrag/:cragID', 'authenticate', function($cragID) use($app) {
            // check for required params
            verifyRequiredParams(array('cragID', 'naam', 'locatie'));

            global $user_id;            
            $cragID = $app->request->put('cragID');
            $naam = $app->request->put('naam');
	       $locatie = $app->request->put('locatie');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateCrag($cragID, $naam, $locatie);
            if ($result) {
                // crag updated successfully
                $response["error"] = false;
                $response["message"] = "crag updated successfully";
            } else {
                // crag failed to update
                $response["error"] = true;
                $response["message"] = "crag failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });





//----Sector Crud's

        
        /**
 * Listing all routes of a sector
 * method POST
 * url /routesOfSector         
 */
$app->post('/routesOfSector', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('sectorID'));
            $sectorID = $app->request()->post('sectorID');

            $response["valid"] = true;
            $response["routes"] = array();
            $db = new DbHandler();
            
            // fetching all routes
            $result = $db->getAllSectorRoutes($sectorID);

            // looping through result and preparing routes array
            while ($route= $result->fetch_assoc()) {
                $tmp = array();
                $tmp["sectorID"] = $route["ID_Sector"];
                $tmp["routeID"] = $route["ID"];
                $tmp["naam"] = $route["naam"];
                $tmp["niveau"] = $route["niveau"];                
                array_push($response["routes"], $tmp);
            }

            echoRespnse(200, $response);
        });
        
        
        
        /**
 * add Sector
 * url - /addSector
 * method - POST
 * params - naam, CragID
 */
$app->post('/addSector', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('naam','CragID'));

            $response = array();

            // reading post params
            $name = $app->request->post('naam');            
            $CragID = $app->request->post('CragID');
            
            $db = new DbHandler();
            $res = $db->createSector($name, $CragID );
            
            if ($res == SECTOR_CREATED_SUCCESSFULLY) {
                $response["status"] = true;
                $response["message"] = "sector added";
            } else if ($res == SECTOR_CREATE_FAILED) {
                $response["status"] = false;
                $response["message"] = "Oops! An error occurred while adding sector";
                        }
            // echo json response
            echoRespnse(201, $response);
        });
        
        

/**
 * Listing all routes of particual user
 * method POST
 * url /routesOfUser          
 */
$app->post('/routesOfUser', 'authenticate', function() {
            global $user_id;    
            // check for required params
            
           
            $response = array();
            
            $response["error"] = false;
            $response["routes"] = array();
            $db = new DbHandler();
            
            // fetching all routes
            $result = $db->getAllPalmaresForUser($user_id);

            // looping through result and preparing routes array
            while ($route= $result->fetch_assoc()) {
                $tmp = array();
                $tmp["naam"] = $route["naam"];
                $tmp["niveau"] = $route["niveau"]; 
                $tmp["Datum"] = $route["Datum"]; 
                array_push($response["routes"], $tmp);
            }

            echoRespnse(200, $response);
        });       
        

        
 /**
 * add Route
 * url - /addRoute
 * method - POST
 * params - ID_Sector, naam, niveau, lengte, opmerking, multipitch, type 
 */
$app->post('/addRoute', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('naam','ID_Sector'));

            $response = array();

            // reading post params
            $naam = $app->request->post('naam');            
            $ID_Sector = $app->request->post('ID_Sector');
            $niveau = $app->request->post('niveau');            
            $lengte = $app->request->post('lengte');
            $opmerking = $app->request->post('opmerking');            
            $multipitch = $app->request->post('multipitch');
            $type = $app->request->post('type');
            
            $db = new DbHandler();
            $res = $db->createCrag($ID_Sector, $naam, $niveau, $lengte, $opmerking, $multipitch, $type );
            
            if ($res == ROUTE_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Route added";
            } else if ($res == ROUTE_CREATED_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while adding route";
            }else if ($res == ROUTE_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "A route with this name in this sector already exists";
            }
            
            // echo json response
            echoRespnse(201, $response);
        });
       
        
     
/**
 * add Palmares Light
 * url - /addPalmaresLight
 * method - POST
 * params - ID_Route, ID_User, datum
 */
$app->post('/addPalmaresLight', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('IDs_Routes','ID_User', 'datum'));

            $response = array();

            // reading post params
            $IDs_Routes = $app->request->post('IDs_Routes');            
            $ID_User = $app->request->post('ID_User');
            $datum = $app->request->post('datum');            
           
            
            $db = new DbHandler();
            $res = $db->createPalmaresRowLight($IDs_Routes,$ID_User, $datum );
            
            if ($res == PALMARESROW_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "Route added to palmares";
            } else if ($res == PALMARESROW_CREATED_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while adding route to palmares";
            }
            
            // echo json response
            echoRespnse(201, $response);
        });        
        
        

        
/*
 * ------------------------ METHODS WITH AUTHENTICATION ------------------------
 */

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks          
 */
$app->get('/tasks', 'authenticate', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllUserTasks($user_id);

            $response["error"] = false;
            $response["tasks"] = array();

            // looping through result and preparing tasks array
            while ($task = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["id"] = $task["id"];
                $tmp["task"] = $task["task"];
                $tmp["status"] = $task["status"];
                $tmp["createdAt"] = $task["created_at"];
                array_push($response["tasks"], $tmp);
            }

            echoRespnse(200, $response);
        });

/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/tasks/:id', 'authenticate', function($task_id) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getTask($task_id, $user_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response["id"] = $result["id"];
                $response["task"] = $result["task"];
                $response["status"] = $result["status"];
                $response["createdAt"] = $result["created_at"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
//$app->post('/tasks', 'authenticate', function() use ($app) {
//            // check for required params
//            verifyRequiredParams(array('task'));
//
//            $response = array();
//            $task = $app->request->post('task');
//
//            global $user_id;
//            $db = new DbHandler();
//
//            // creating new task
//            $task_id = $db->createTask($user_id, $task);
//
//            if ($task_id != NULL) {
//                $response["error"] = false;
//                $response["message"] = "Task created successfully";
//                $response["task_id"] = $task_id;
//                echoRespnse(201, $response);
//            } else {
//                $response["error"] = true;
//                $response["message"] = "Failed to create task. Please try again";
//                echoRespnse(200, $response);
//            }            
//        });

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {
            // check for required params
            verifyRequiredParams(array('task', 'status'));

            global $user_id;            
            $task = $app->request->put('task');
            $status = $app->request->put('status');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateTask($user_id, $task_id, $task, $status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Deleting task. Users can delete only their tasks
 * method DELETE
 * url /tasks
 */
$app->delete('/tasks/:id', 'authenticate', function($task_id) use($app) {
            global $user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteTask($user_id, $task_id);
            if ($result) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "Task deleted succesfully";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Task failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>