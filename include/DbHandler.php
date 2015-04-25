<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 * versie 27/01/2015
 */
class DbHandler {
// testje
    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $Naam User name
 	* @param String $Voornaam User First name
     * @param String $email User login email id
     * @param String $password User login password
     * @param String $username User login 
     */
    public function createUser($Naam, $Voornaam, $email, $username, $psw) {
        //require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExistsEmail($email)) {
            if (!$this->isUserExistsUsername($username)) {
            
                // Generating password hash
                $password_hash = PassHash::hash($psw);

                // Generating API key
                $api_key = $this->generateApiKey();

                // insert query
                $status = 1;
                $stmt = $this->conn->prepare("INSERT INTO tbl_User(Naam, Voornaam, email, username, psw, api_key, status) values(?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $Naam, $Voornaam, $email, $username, $psw, $api_key, $status);

                $result = $stmt->execute();

                $stmt->close();

                // Check for successful insertion
                if ($result) {
                    // User successfully inserted
                    return USER_CREATED_SUCCESSFULLY;
                } else {
                    // Failed to create user
                    return USER_CREATE_FAILED;
                }
            }
            else{
                // User with same username already existed in the db
                return USERNAME_ALREADY_EXISTED;
            }
        }
        else {
            // User with same email already existed in the db
            return EMAIL_ALREADY_EXISTED;
        }

        return $response;
    }

    /**
     * Checking user login by username
     * @param String $username User login username
     * @param String $psw User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($username, $psw) {
        // fetching user by username
        $stmt = $this->conn->prepare("SELECT psw FROM tbl_User WHERE username = ?");

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the username
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the username
            return FALSE;
        }
    }


/**
     * Checking user login by email
     * @param String $email User login email
     * @param String $psw User login password
     * @return boolean User login status success/fail
     */
    public function checkLoginEmail($email, $password) {
        // fetching user by username
        $stmt = $this->conn->prepare("SELECT psw FROM tbl_User WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

             if ($password_hash == $password) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    
    /**
     * Checking user login by username
     * @param String $username User login username
     * @param String $psw User login password
     * @return boolean User login status success/fail
     */
public function checkLoginUsername($username, $password) {
        // fetching user by username
        $stmt = $this->conn->prepare("SELECT psw FROM tbl_User WHERE username = ?");

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if ($password_hash == $password) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExistsEmail($email ) {
        $stmt = $this->conn->prepare("SELECT ID from tbl_User WHERE email = ?");
        $stmt->bind_param("s", $email );
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

 /**
     * Checking for duplicate user by username 
     * @param String $username to check in db
     * @return boolean
     */
    private function isUserExistsUsername($username) {
        $stmt = $this->conn->prepare("SELECT ID from tbl_User WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT ID, Naam, Voornaam, email, username, api_key, status, created_at FROM tbl_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID, $Naam, $Voornaam, $email, $username, $api_key, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $Naam;
		 $user["firstname"] = $Voornaam;
            $user["email"] = $email;
		 $user["username"] = $username;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $user["userID"] = $ID;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

 /**
     * Fetching user by username
     * @param String $username 
     */
    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT  ID, Naam, Voornaam, email, username, api_key, status, created_at FROM tbl_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID,$Naam, $Voornaam, $email, $username, $api_key, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $Naam;
            $user["firstname"] = $Voornaam;
            $user["email"] = $email;
            $user["username"] = $username;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $user["userID"] = $ID;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }


    /**
     * Fetching user api key
     * @param String $ID user ID primary key in tbl_User
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM tbl_User WHERE ID = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT ID FROM tbl_User WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT ID from tbl_User WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

 /**
     * Delete user by userAPI
     * @param String $userAPI UserAPI
     * 
     */
    public function deleteUser($userAPI) {
        // fetching user by username
        $stmt = $this->conn->prepare("Delete * FROM tbl_User WHERE userAPI = ?");
        $stmt->bind_param("s", $userAPI);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($num_affected_rows > 0)
		{
			return USER_DELETE_SUCCESSFULLY;
		}
        else{
            return USER_DELETE_FAILED;          
        }
    }



/* ------------- `Palmares` table methods ------------------ */

    /**
     * Creating new palmares row
     * @param String $ID_Route 
     * @param String $ID_User
    * @param String $ID_Type
    * @param String $Datum string 
    * @param String $Notes text
     */
    public function createPalmaresRow($ID_User, $ID_Route, $ID_Type, $Datum, $Notes) 
{
        $stmt = $this->conn->prepare("INSERT INTO tbl_Palmares(ID_Route, ID_User, ID_Type, Datum, Notes) VALUES(?,?,?,?,?)");
        $stmt->bind_param("iiiss", $ID_User, $ID_Route, $ID_Type, $Datum, $Notes );
        $result = $stmt->execute();
        $stmt->close();

       // Check for successful insertion
            if ($result) {
                // palmares row successfully inserted
                return PALMARES-ROW_CREATED_SUCCESSFULLY;
            } else {
                // Failed to add palmares
                return PALMARES-ROW_CREATED_FAILED;
            } 
    }
    
     /**
     * Creating new palmares row with minimal params
     * @param array $ID_Route 
     * @param String $ID_User    
    * @param String $Datum string     
     */
    public function createPalmaresRowLight($IDs_Routes,$ID_User, $Datum) 
{
        
        
        $myArray = explode(',', $IDs_Routes);
        foreach ($myArray as &$routeID) {
              $stmt = $this->conn->prepare("INSERT INTO tbl_Palmares(ID_Route, ID_User, Datum) VALUES(?,?,?)");
                $stmt->bind_param("iis",  $routeID, $ID_User,  $Datum);
                $result = $stmt->execute();
                $stmt->close();
        }

       
       // Check for successful insertion
            if ($result) {
                // palmares row successfully inserted
                return PALMARESROW_CREATED_SUCCESSFULLY;
            } else {
                // Failed to add palmares
                return PALMARESROW_CREATE_FAILED;
            } 
    }


/**
     * Deleting a palmares row
     * @param Int $Palm_ID  id of the palmares to delete
     */
    public function deletePalmares($Palm_ID) {
        $stmt = $this->conn->prepare("DELETE FROM tbl_Palmares tp WHERE tp.ID = ?");
        $stmt->bind_param("i", $Palm_ID);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


/**
     * Fetching all palmares of user
     * @param Int $User_ID id of the user
     */
    public function getAllPalmaresForUser($User_ID) {
        $stmt = $this->conn->prepare
("SELECT tr.ID, tr.naam, tr.niveau, tr.opmerking, tr.multipitch, tr.lengte, tr.type, tp.Datum   
FROM tbl_Palmares tp, tbl_Routes tr
WHERE tp.ID_User = ? AND tp.ID_Route = tr.ID");
        $stmt->bind_param("i", $User_ID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }



/**
     * Update palmares row
     */
//TODO


/* ------------- `Crags` table methods ------------------ */

    /**
     * Creating new crags row
     * @param String $naam string
     * @param String $locatie string
      */
    public function createCrag($naam, $locatie) 
{
        //todo: controleren of crag nog niet bestaat
        if (!$this->isCragExistsName($naam)){
        $stmt = $this->conn->prepare("INSERT INTO tbl_Crags(naam, locatie) VALUES(?,?)");
        $stmt->bind_param("ss", $naam, $locatie);
        $result = $stmt->execute();
        $stmt->close();

       // Check for successful insertion
            if ($result) {
                // crag row successfully inserted
                return CRAG_CREATED_SUCCESSFULLY;
            } else {
                // Failed to add palmares
                return CRAG_CREATED_FAILED;
            } 
        }else 
        {
             return CRAG_ALREADY_EXISTED;            
        }
    }


/**
     * Fetching Crag by ID
     * @param String $ID_Crag CRAG ID
     */
    public function getCragByID($ID) {
        $stmt = $this->conn->prepare("SELECT ID, naam, locatie, FROM tbl Crags WHERE ID = ?");
        $stmt->bind_param("s", $ID_Crag);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID, $naam, $locatie);
            $stmt->fetch();
            $crag = array();
 		 $crag["ID"] = $ID;            
		 $crag["naam"] = $naam;
		 $crag["locatie"] = $locatie;
            $stmt->close();
            return $crag ;
        } else {
            return NULL;
        }
    }


/**
     * Fetching Crag by naam
     * @param String $Crag_naam CRAG naam
     */
    public function getCragByNaam($Crag_naam) {
        $stmt = $this->conn->prepare("SELECT ID, naam, locatie FROM tbl_crags WHERE naam = ?");
        $stmt->bind_param("s", $Crag_naam );
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID, $naam, $locatie);
            $stmt->fetch();
            $crag = array();
 		 $crag["ID"] = $ID;            
		 $crag["naam"] = $naam;
		 $crag["locatie"] = $locatie;
            $stmt->close();
            return $crag ;
        } else {
            return NULL;
        }
    }

/**
     * Checking for duplicate crag by crag naam
     * @param String $naam crag naam to check in db
     * @return boolean
     */
    private function isCragExistsName($name ) {
        $stmt = $this->conn->prepare("SELECT ID, naam, locatie FROM tbl_crags WHERE naam = ?");
        $stmt->bind_param("s", $name );
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
    
    
 /**
     * Updating crag
     * @param Int $ID_Crag  id of the task
     * @param String $naam  naam crag
     * @param String $locatie locatie crag
     */
    public function updateCrag($ID_Crag, $naam, $locatie ) 
{
        $stmt = $this->conn->prepare("UPDATE tbl_Crags tc SET  tc.naam = ?, tc.locatie = ? WHERE tc.ID = ? ");
        $stmt->bind_param("ssi", $naam, $locatie , $ID_Crag);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


 /**
     * Deleting a crag
     * @param Int $Crag_id id of the crag to delete
     */
    public function deleteCRAG($ID_Crag) {
        $stmt = $this->conn->prepare("DELETE FROM tbl_Crags tc WHERE tc.ID = ?");
        $stmt->bind_param("i", $ID_Crag);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


 /**
     * Fetching all crag routes
     * @param String $Crag_id id of the crag
     */
    public function getAllCragRoutes($Crag_id) {
        $stmt = $this->conn->prepare("SELECT tr.ID, tr.naam, tr.niveau, tr.opmerking, tr.multipitch, tr.lengte, tr.type   FROM tbl_Routes tr, tbl_Sector ts WHERE ts.ID_Crag = ? AND tr.ID_Sector = ts.ID");
        $stmt->bind_param("i", $Crag_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

      
    
    /**
     * Fetching all crag sectors
     * @param String $Crag_id id of the crag
     */
    public function getAllCragSectors($Crag_id) {
        $stmt = $this->conn->prepare("SELECT * from tbl_sector WHERE ID_Crag = ?");
        $stmt->bind_param("i", $Crag_id);
        $stmt->execute();
        $sectors = $stmt->get_result();
        $stmt->close();
        return $sectors;
    }
       
    
    /**
     * Fetching all crags
     * @param String $Crag_id id of the crag
     */
    public function getAllCrags() {
        $stmt = $this->conn->prepare("SELECT * from tbl_crags");       
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }



/* ------------- `Sector` table methods ------------------ */

  /**
     * Fetching all sector routes
     * @param String $sector_id id of the crag
     */
    public function getAllSectorRoutes($sector_id) {
        $stmt = $this->conn->prepare("SELECT tr.ID, tr.naam, tr.niveau, tr.opmerking, tr.multipitch, tr.lengte, tr.type, tr.ID_Sector  FROM tbl_Routes tr, tbl_Sector ts WHERE ts.ID = ? AND tr.ID_Sector = ts.ID");
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $routes = $stmt->get_result();
        $stmt->close();
        return $routes;
    }

    /**
     * Fetching all sector routes
     * @param String $sector_id id of the crag
     */
    public function createSector($CragID, $naam) {
        $stmt = $this->conn->prepare("INSERT INTO tbl_sector (ID_Crag, naam) VALUES(?,?)");
        $stmt->bind_param("is", $CragID, $naam);
        $result = $stmt->execute();
        $stmt->close();

       // Check for successful insertion
            if ($result) {
                // sector row successfully inserted
                return SECTOR_CREATED_SUCCESSFULLY;
            } else {
                // Failed to add sector
                return SECTOR_CREATED_FAILED;
            } 
    }


/* ------------- `Routes` table methods ------------------ */
    
 
    /**
     * Creating new route row
     * @param Int $ID_Sector 
     * @param String $naam 
     * @param Int $niveau 
     * @param Int $lengte 
     * @param String $opmerking 
     * @param Int $multipitch 
     * @param Int $type 
      */
    public function createRoute($ID_Sector, $naam, $niveau, $lengte, $opmerking, $multipitch, $type) 
{
        
        if (!$this->isRouteExistsName($naam, $ID_Sector)){
        $stmt = $this->conn->prepare("INSERT INTO tbl_routes(ID_Sector, naam, niveau, lengte, opmerking, multipitch, type ) VALUES(?,?,?,?,?,?,?)");
        $stmt->bind_param("isiisii", $ID_Sector, $naam, $niveau, $lengte, $opmerking, $multipitch, $type);
        $result = $stmt->execute();
        $stmt->close();

       // Check for successful insertion
            if ($result) {
                // route row successfully inserted
                return ROUTE_CREATED_SUCCESSFULLY;
            } else {
                // Failed to add route
                return ROUTE_CREATED_FAILED;
            } 
        }else 
        {
             return CRAG_ALREADY_EXISTED;            
        }
    }


/**
     * Fetching Route by ID
     * @param String $ID route ID
     */
    public function getRouteByID($ID) {
        $stmt = $this->conn->prepare("SELECT ID, ID_Sector, naam, niveau, lengte, opmerking, multipitch, type FROM tbl_routes WHERE ID = ?");
        $stmt->bind_param("i", $ID );
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID, $ID_Sector, $naam, $niveau, $lengte, $opmerking, $multipitch, $type);
            $stmt->fetch();
            $route = array();
 		 $route["ID"] = $ID;            
		 $route["ID_Sector"] = $ID_Sector;
		 $route["naam"] = $naam;
                 $route["niveau"] = $niveau;
                 $route["lengte"] = $lengte;
                 $route["opmerking"] = $opmerking;
                 $route["multipitch"] = $multipitch;
                 $route["type"] = $type;
            $stmt->close();
            return $crag ;
        } else {
            return NULL;
        }
    }


/**
     * Fetching Route by naam
     * @param String $naam route naam
     */
    public function getRouteByNaam($naam) {
        $stmt = $this->conn->prepare("SELECT ID, ID_Sector, naam, niveau, lengte, opmerking, multipitch, type FROM tbl_routes WHERE naam = ?");
        $stmt->bind_param("s", $naam );
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($ID, $ID_Sector, $naam, $niveau, $lengte, $opmerking, $multipitch, $type);
            $stmt->fetch();
            $route = array();
 		 $route["ID"] = $ID;            
		 $route["ID_Sector"] = $ID_Sector;
		 $route["naam"] = $naam;
                 $route["niveau"] = $niveau;
                 $route["lengte"] = $lengte;
                 $route["opmerking"] = $opmerking;
                 $route["multipitch"] = $multipitch;
                 $route["type"] = $type;
            $stmt->close();
            return $crag ;
        } else {
            return NULL;
        }
    }

/**
     * Checking for duplicate route in sector by route naam and sector ID
     * @param String $naam route naam to check in db
     * @param String $ID_Sector sector ID to check in db
     * @return boolean
     */
    private function isRouteExistsName($name, $ID_Sector ) {
        $stmt = $this->conn->prepare("SELECT ID FROM tbl_routes WHERE naam = ? and ID_Sector = ?");
        $stmt->bind_param("si", $name, $ID_Sector);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
    
    
 /**
     * Updating route
     * @param Int $ID_Route  id of the route
     * @param String $naam  naam route
     * @param String $niveau niveau route
     * @param String $sector_ID  sector-ID of the route
     * @param String $opmerking opmerking route
     * @param String $lengte lengte route
     */
    public function updateRoute($ID_Route, $naam, $niveau, $sector_ID, $opmerking, $lengte ) 
{
        $stmt = $this->conn->prepare("UPDATE tbl_routes tr SET  tr.naam = ?, tr.niveau = ?, tr.sector_ID = ?, tr.opmerking = ?, tr.lengte = ? WHERE tr.ID = ? ");
        $stmt->bind_param("siisii", $naam, $niveau , $sector_ID, $opmerking, $lengte, $ID_Route );
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


 /**
     * Deleting a route
     * @param Int $ID_Route id of the route to delete
     */
    public function deleteRoute($ID_Route) {
        $stmt = $this->conn->prepare("DELETE FROM tbl_routes tr WHERE tr.ID = ?");
        $stmt->bind_param("i", $ID_Route);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


 

      
    
    
    
    
/* ------------- `eigen code tot hier'------------------ */




    /* ------------- `tasks` table method ------------------ */

    /**
     * Creating new task
     * @param String $user_id user id to whom task belongs to
     * @param String $task task text
     */
    public function createTask($user_id, $task) {
        $stmt = $this->conn->prepare("INSERT INTO tasks(task) VALUES(?)");
        $stmt->bind_param("s", $task);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // task row created
            // now assign the task to user
            $new_task_id = $this->conn->insert_id;
            $res = $this->createUserTask($user_id, $new_task_id);
            if ($res) {
                // task created successfully
                return $new_task_id;
            } else {
                // task failed to create
                return NULL;
            }
        } else {
            // task failed to create
            return NULL;
        }
    }

    /**
     * Fetching single task
     * @param String $task_id id of the task
     */
    public function getTask($task_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT t.id, t.task, t.status, t.created_at from tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($id, $task, $status, $created_at);
            // TODO
            // $task = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["id"] = $id;
            $res["task"] = $task;
            $res["status"] = $status;
            $res["created_at"] = $created_at;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all user tasks
     * @param String $user_id id of the user
     */
    public function getAllUserTasks($user_id) {
        $stmt = $this->conn->prepare("SELECT t.* FROM tasks t, user_tasks ut WHERE t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    /**
     * Updating task
     * @param String $task_id id of the task
     * @param String $task task text
     * @param String $status task status
     */
    public function updateTask($user_id, $task_id, $task, $status) {
        $stmt = $this->conn->prepare("UPDATE tasks t, user_tasks ut set t.task = ?, t.status = ? WHERE t.id = ? AND t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("siii", $task, $status, $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a task
     * @param String $task_id id of the task to delete
     */
    public function deleteTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("DELETE t FROM tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /* ------------- `user_tasks` table method ------------------ */

    /**
     * Function to assign a task to user
     * @param String $user_id id of the user
     * @param String $task_id id of the task
     */
    public function createUserTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_tasks(user_id, task_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

}

?>
