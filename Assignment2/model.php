<?php
// model class for Team 
class Team {

// attributes of the model class Team
private $conn;
private static $table = 'team';
private $parts = ['name','sport','avgDob'];
public $name, $sport,$avgDob,$_links;
public function __construct($db) {
    $this->conn = $db->conn;
    
}

// set the attributtes of the model class
public function set($source) {
    if (is_object($source))
    $source = (array)$source;
    foreach ($source as $key=>$value)
    if (in_array($key,$this->parts))
    $this->$key = $value;
    else
    throw new Exception("$key not an attribute of team",400);
}



// validates if all the values are set. 
public function validate() {
    foreach ($this->parts as $key)
    if (is_null($this->$key))
    return FALSE;
    return TRUE;
}

// function to get the string representaion of the model object in json format.
public function __toString() {
    return json_encode($this,
    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

/*
* setLinks function to set the links.
* parameter- $teamName- name of the team

*/
public function setLinks($teamName) { 

    $this->_links =[( object)[ ' href '=>"team/".$teamName."/player",'method'=>'GET','rel'=>'get' ],(object)[ 'href'=>"team/".$teamName."/player/{playerId}",
     
    'method'=>'GET', 'rel'=>'get' ],( object)[ ' href '=>"team/".$teamName."/player",'method'=>'POST','rel'=>'post' ],( object)[ ' href '=>"team/".$teamName."/player/{playerId}",'method'=>'PATCH','rel'=>'patch' ],( object)[ ' href '=>"team/".$teamName."/player/{playerId}",'method'=>'DELETE','rel'=>'delete' ]];
}


/*
* retrieveAllTeam($db)
* retrieve the information of all the team.
* return an array of all the team object.
* parameter- $db - database object.

*/
public static function retrieveAllTeam($db) {
    $allTeam=array();
    $result =$db->conn->query('SELECT * FROM '.self::$table.' order by name');
    $row=$result->fetchAll();
    foreach ($row as $rows ) {
        
   
            $team=new Team($db);
            $team->name=$rows['name'];
            $team->sport=$rows['sport'];
            $team->avgDob=$rows['avgDob'];
            $team->setLinks($rows['name']);
            array_push($allTeam,$team);
    
    
   
    }


    return $allTeam;
}
/*
* updateAvgAge($name,$avgAge)
* updates the avgDob column in the team table for a given team.

* parameter - $name - name of the team.
            - $avgAge -average age of all the players in a particular team     

*/
public function updateAvgAge($name,$avgAge){
    $query = 'update '.self::$table.' set avgDob=:avgAge where name=:name' ;
    $stmt1=$this->conn->prepare($query);
    $success=$stmt1->execute(array(':avgAge'=>$avgAge,':name'=>$name));
    return $success;




}



}


// model class for player
class Player {
    
    // attributes  of player class.
    private $conn;
    private $id;
    private static $table = 'player';
    private $parts = ['surname','givenName','nationality','dob','teamName'];
    public $surname, $givenName,$nationality,$dob,$teamName;
    public function __construct($db) {
        $this->conn = $db->conn;
        
    }
    
    // set the private id of the player class
    public function setPrivateId($id){
        $this->$id;
    }
    
    // set the attributtes of the model class
    public function set($source) {
        if (is_object($source))
        $source = (array)$source;
        foreach ($source as $key=>$value)
        if (in_array($key,$this->parts))
        $this->$key = $value;
        else
        throw new Exception("$key not an attribute of meetings",400);
    }
    
    
    
    // validates if all the values are set. 
    public function validate() {
        foreach ($this->parts as $key)
        if (is_null($this->$key))
        return FALSE;
        return TRUE;
    }
    
    // function to get the string representaion of the model object in json format.
    public function __toString() {
        return json_encode($this,
        JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
    
    

    /*
    * retrieveAllPlayerTeam($db,$name)
    * retrieve the information of all player of a particular team.
    * return an array of all the player object.
    * parameter - $db - database object.
                - $name- name of the team

    */
    public static function retrieveAllPlayerOfTeam($db,$name) {

        $allPlayer=array();

        $query = ' SELECT * FROM ' . self:: $table .' WHERE teamName=:name ' ;
        $stmt1=$db->conn->prepare($query);
        $stmt1->execute(array($name));

      
        foreach ($stmt1 as $rows ) {
            
    
                $player=new Player($db);
                $player->surname=$rows['surname'];
                $player->givenName=$rows['givenName'];
                $player->nationality=$rows['nationality'];
                $player->dob=$rows['dob'];
                $player->teamName=$rows['teamName'];
                $player->setPrivateId($rows['id']);

                
                array_push($allPlayer,$player);
            }


        return $allPlayer;
    }



    /*
    * retrieveExistingPlayerOfTeam($team,$playerId)
    * retrieve the information of all player with a oarticular id of a particular team
    * return data retireved from the database.
    * parameter - $team - name of the team.
                - $playerId- id of the player.


    */
    public function retrieveExistingPlayerOfTeam($team,$playerId){
        $query = ' SELECT * FROM ' .self:: $table.' WHERE teamName=:name and id=:id' ;
        $stmt1=$this->conn->prepare($query);
        $stmt1->execute(array(':name'=>$team,':id'=>$playerId));
        $row=$stmt1->fetch();
        return $row;


    }
    /*
    * addPlayerToTeam($team,$playerId)
    * add the information of the player to the database.
    * returns the last inserted id.
 

    */
    public function addPlayerToTeam(){

        $query = ' INSERT INTO ' . self:: $table .' (surname,givenName,nationality,dob,teamName) VALUES (?,?,?,?,?) ' ;
        $stmt = $this ->conn ->prepare($query);
        $stmt ->execute(array($this ->surname,$this ->givenName,$this ->nationality,$this ->dob,$this ->teamName));
        
        $this ->id = $this ->conn ->lastInsertId ();
        
        return $this ->id;


    }


    /*
    * updatePlayerDetails($id,$teamName)
    * update the information of the player with a oarticular id of a particular team
    * return TRUE is success else FALSE.
    * parameter - $id - id of the player.
                - $teamName- name of the team.

    */
    public function updatePlayerDetails($id,$teamName){
        
        $query = 'UPDATE '.self::$table.' SET surname=:surname,givenName=:givenName,nationality=:nation,dob=:dob where teamName=:name and id=:id';
        $stmt1=$this->conn->prepare($query);
        $success=$stmt1->execute(array(':surname'=>$this->surname,':givenName'=>$this->givenName,':nation'=>$this->nationality,':dob'=>$this->dob,':id'=>$id,':name'=>$teamName));

        if($stmt1->rowcount()>0){
            return TRUE;
        }
        else{
            return FALSE;
        }

    }
    /*
    * deletePlayer($id)
    * delete the information of the player with a particular id of a particular team
    * return TRUE is success else FALSE.
    * parameter - $id - id of the player.

    */

    public function deletePlayer($id){
        $query = 'DELETE FROM '.self::$table.' WHERE id=:id';

        $stmt1=$this->conn->prepare($query);
        $stmt1->execute(array(':id'=>$id));
        if($stmt1->rowcount()>0){
            return TRUE;
        }
        else{
            return FALSE;
        }




    }
    /*
    * calculateAverageAge($name)
    * calculates the average age of the players in a team.
    * return the average age.
    * parameter - $name- name of the team.

    */

    public function calculateAvgerageAge($name){
        $now=date("Y-m-d");

        $age=array();
        $query = ' SELECT dob FROM ' . self:: $table .' WHERE teamName=:name ' ;
        $stmt1=$this->conn->prepare($query);
        $stmt1->execute(array($name));

        foreach ($stmt1 as $row) {
            $dob=$row['dob'];

            $diffDate=($now-$dob);
            array_push($age,$diffDate);
        }

        $avg=array_sum($age)/count($age);
        return (int)$avg;
    
    }

    

  
    

    
    
    
}


?>