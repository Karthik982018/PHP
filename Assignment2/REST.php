<?php

require_once('Database.php');
require_once('model.php');

// creating a database connection
$db = new Database();

// setting the exception handler
set_exception_handler(function ($e) {
    $code = $e-> getCode() ?: 400;
    header ("Content-Type: application/json", NULL, $code);
    echo json_encode(["error" => $e-> getMessage()]);
    exit ;
});


// retrieving inputs from the request

$method = $_SERVER['REQUEST_METHOD'];
$resource = explode('/', $_REQUEST['resource']);
$data = json_decode(file_get_contents('php://input'),TRUE);

// switch statement to route requests

switch($method) {
    case 'GET':
    [$data,$status] = readData($db,$resource);
    break;
    case 'POST':
    [$data,$status] = createData($db,$resource,$data);
    break;
    case 'DELETE':
    [$data,$status] = deleteData($db,$resource);
    break;
    case 'PATCH':
    [$data,$status] = updateData($data,$db,$resource);
    break;
    default:
    throw new Exception('Method Not Supported', 405);
}



// setting the header  
header("Content-Type: application/json",TRUE,$status);

// returning the response in a json encoded form

echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

/*
* function readData() 
* retrieve the information from the data base according to the request url and sends it back to the client.
* else an error  message is sent back.

*/
function readData($db,$resource) {
    /*
    * resource- team
    * returns all the details of the present team.
    

    */
    if(count($resource)==1 && ($resource[0]=='team')){
        $team=new Team($db);
        $allTeam=$team->retrieveAllTeam($db);
        
        return [$allTeam,200];



    }
    
    /*
    * resource- team/{teamName}/player
    * returns all the details of the players of a particular team.
    

    */
    elseif(count($resource)==3 && ($resource[0]=='team') && ($resource[1]!=NULL)&& ($resource[2]=='player')){
        $player=new Player($db);
        $allPlayer=$player->retrieveAllPlayerOfTeam($db,$resource[1]);
        
        return [$allPlayer,200];
    

    }
    
    /*
    * resource- team/{teamName}/player/{playerId}
    * returns all the details of the player with Id playerId of a particular team .

    */
    elseif(count($resource)==4 && ($resource[0]=='team') && ($resource[1]!=NULL)&& ($resource[2]=='player')&& ($resource[3]!=NULL)){
        $player=new Player($db);
        $allPlayer=$player->retrieveExistingPlayerOfTeam($resource[1],$resource[3]);
        if($allPlayer){
            return [$allPlayer,200];

        }else{
            $message=array('message'=>'player does not exist');
            return[$message,404];

        }
        
       
    

    }else{
        $message=array('message'=>'Resource not found');
        return[$message,404];
        
    }




}
/*
* function createData() 
* stores the data to the database according to the request url.

*/
function createData($db,$resource,$data){
    /*
    * resource- team/{teamName}/player
    * insert all the details of the player of a particular team into the player table in the database.
    * if insertion is successfull the id of the player is sent back.
    * else an error  message is sent back.

    */

    if(count($resource)==3 && ($resource[0]=='team') && ($resource[1]!=NULL)&& ($resource[2]=='player')){

            $db->conn ->beginTransaction ();
    
            $player=new Player($db);
            $player->set($data);
            $id=$player->addPlayerToTeam();
            $avgAge=$player->calculateAvgerageAge($resource[1]);
            $team=new Team($db);
            $success=$team->updateAvgAge($resource[1],$avgAge);
            if($success){

                $db->conn ->commit();
                return [$id,200];
        
            }else{
                $db->conn->rollback();
                $message=array('message'=>'unable to connect to the server');
                return[$message,503];

            }


            



    }else{
        $message=array('message'=>'Resource not found');
        return[$message,404];

    }



    
}

/*
*function updateData() 
*update the information in the database according to the url.

*/
function updateData($data,$db,$resource){

    /*
    * resource- team/{teamName}/player/{playerId}
    * update the details of the player with Id playerId of a particular team .
    * else an error  message is sent back.

    */
    if(count($resource)==4 && ($resource[0]=='team') && ($resource[1]!=NULL)&& ($resource[2]=='player')&& ($resource[3]!=NULL)){
            $db->conn->beginTransaction();
            $player=new Player($db);
            $player->set($data);
            $updated=$player->updatePlayerDetails($resource[3],$resource[1]);
            $avgAge=$player->calculateAvgerageAge($resource[1]);
            $team=new Team($db);
            $success=$team->updateAvgAge($resource[1],$avgAge);




            if($updated && $success){
                $db->conn->commit();
                $message=array('message'=>'player got updated successfully');

                return[$message,200];
                
            }else{
                $db->conn->rollback();
                $message=array('message'=>'player does not exist');
                return[$message,404];
                

            }
    }else{
        $message=array('message'=>'Resource not found');
        return[$message,404];

}





}
/*
*function deleteData() 
*delete the information from  the database according to the url.

*/
function deleteData($db,$resource){
    /*
    * resource- team/{teamName}/player/{playerId}
    * delete the details of the player with Id playerId of a particular team .
    * else an error  message is sent back.

    */


    if(count($resource)==4 && ($resource[0]=='team') && ($resource[1]!=NULL)&& ($resource[2]=='player')&& ($resource[3]!=NULL)){
    $db->conn ->beginTransaction ();

    $player=new Player($db);
    
    $deleted=$player->deletePlayer($resource[3]);
    $avgAge=$player->calculateAvgerageAge($resource[1]);
    $team=new Team($db);
    $updated=$team->updateAvgAge($resource[1],$avgAge);
    if($deleted && $updated){
        $db->conn ->commit();
        $message=array('message'=>'player got deteled successfully');

        return[$message,200];
        
    }else{
        $db->conn->rollback();
        $message=array('message'=>'player does not exisst');
        return[$message,404];
        

    }

    }else{
        $message=array('message'=>'Resource not found');
        return[$message,404];

    }
}


?>