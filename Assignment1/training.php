<?php
// starting the session and maintaining the session
session_start();

// if the topic topic is set in the url then add it to the session variable
if(isset($_REQUEST['topic']) ){
    $_SESSION['topic']=$_REQUEST['topic'];

    
    
}

// if the topic times is set in the url then add it to the session variable
if(isset($_REQUEST['times']) ){
    $_SESSION['times']=$_REQUEST['times'];

    
    
}

/* if the topic and times is selected a value in the form then next field name and and email is entered 
and the correcsponding value is set to the session variable
*/
if(isset($_REQUEST['name'])&& isset($_SESSION['times'])&& isset($_SESSION['topic'])){
    $_SESSION['name']=$_REQUEST['name'];

    
    
}
/* if the topic and times is selected a value in the form then next field name and and email is entered 
and the correcsponding value is set to the session variable
*/
if(isset($_REQUEST['email'])&& isset($_SESSION['times'])&& isset($_SESSION['topic']) ){
    $_SESSION['email']=$_REQUEST['email'];

    
    
}




?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1"> 
<title>Booking portal for trainings</title>

<link rel="stylesheet" href="training.css">
</head>
<body>
<h1>Booking portal for trainings</h1>
<?php

/*
function selectTopic()
* function displays the selection input field for the topic.
* fetches topics with capacity >0 from the database.
* @param &$do- PDO object should be passed a the function input as reference.

*/
function selectTopic(&$do){
    $stmt1=$do->query("select DISTINCT topic from training where capacity>0");
    $topic=$stmt1->fetchAll();
    asort($topic);
    echo '<form action="training.php"  method="post" name="form1" >
    <label>training:</label><br><select name="topic"  onChange="document.form1.submit()">
    <option value="'.$_SESSION['topic'].'">'.$_SESSION['topic'].'</option>';

    foreach($topic as $value){
        
        foreach($value as $key){
        echo '<option value="'.$key.'">'.$key.'</option>';

    }};

    echo ' </select>
    </form>';

};

/*
function displayTimeSlot()
* function displays the selection input field for the time slot available.
* fetches time slots for the previously selected topic  with capacity >0 from the database.
* @param &$do- PDO object should be passed a the function input as reference.

*/
function displayTimeSlot(&$do){
    $topic=$_SESSION['topic'];
    $stmt2=$do->prepare("select times from training where capacity>0 and topic=?");
    $stmt2->bindParam(1,$topic,PDO:: PARAM_STR);
    $stmt2->execute();
   

    echo '<form  action="training.php" method="post" name="form2" >
    <label>Time Slot:</label><br><select name="times"  onChange="document.form2.submit()">
    <option value="'.$_SESSION['times'].'">'.$_SESSION['times'].'</option>';

    foreach($stmt2  as $value){
        foreach($value as $key){
            echo '<option value="'.$key.'">'.$key.'</option>';

        }
    }

    echo ' </select>
    </form>';

};


/*
function displayUserInfoForm()
* function displays the input  field to enter the name and the email of the user and 
a submit button to submit the values to the server.


*/
function displayUserInfoForm(){
    echo '<form  action="training.php" method="post"  >
    <label>name:</label><br><input type="text" name="name" value="'.$_SESSION['name'].'"><br><label>Email:</label><br><input type="text" name="email"value="'.$_SESSION['email'].'"><br>';
    echo '<input type="submit" name="submit">';
    echo '</form>';

}



/*
function validateUserName()
* function valiidates the user name 
* name  should  not contain a sequence of '' and --
* name should contain only a-z A-Z - ' and spaces
* name should start with a letter or apostrophe
* name should not end with hyphen or space.
* returns true if success and false if failure
*/
function validateUserName(){
    if(preg_match("/''|\-\-/",$_SESSION['name'])){
        return  FALSE;
    }
    else{
        if(preg_match("/^[a-zA-Z'][a-zA-Z'\-\s]*[a-zA-Z']$/",$_SESSION['name'])){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

}

/*
function validateUserEmail()
* function valiidates the email
* name  should  contain exactly one occurance for @
* followed and preceeded by sequrence of charachters  a-z . _ -
* where neither sequence ends with a . or -
* returns true if success and false if failure

*/
function validateUserEmail(){
    if(preg_match_all("/@/",$_SESSION['email'])>1){
        return FALSE;

    }else{
        if(preg_match("/^[a-z\._\-]+[a-z_]@{1}[a-z\._\-]+[a-z_]$/",$_SESSION['email'])){
            return TRUE;
        }else{
            return FALSE;
        }
    }

}

/*
function checkSlotStillAvailable()
* there cann be a case in which a slot displayed earlier gets booked before the uuse submit the form.
* function checks if there is still slot avaialabe when the form is submitted.
@param &$do - a PDO element should be passed by reference. 
* returns the number of slot available.


*/
function checkSlotStillAvailable(&$do){
    $stmt4=$do->prepare("select capacity from training where topic=:topicname and times=:time");
    $stmt4->execute(array(':topicname'=>$_SESSION['topic'],':time'=>$_SESSION['times']));
    $slotAvailable=0;
    foreach($stmt4 as $value){
        $slotAvailable=$value['capacity'];
    

    }
    return $slotAvailable;

}

/*
function bookingSlot()
* function books the slot 
* add the details of the user  to the booking table.
* decrement the capacity of the topic in a time slot by one.
@param &$do - a PDO element should be passed by reference. 
@param $availableSlot after the final check if the slot exist or not 
the  corresponding number of slot is passed to the function through this argument and is used to update the capacity in training table.
* returns true if success and false if failure


*/
function bookingSlot(&$do,$availableSlot){
        try{
            $do->beginTransaction();
            $availableSlot--;
            $stmt5=$do->prepare("update training set capacity=:capacity where topic=:topicname and times=:time");
            $update1=$stmt5->execute(array(':capacity'=>$availableSlot,':topicname'=>$_SESSION['topic'],':time'=>$_SESSION['times']));

            $stmt6=$do->prepare("insert into booking(name,email,topic,time) values(:name,:email,:topic,:time)");
            $update2=$stmt6->execute(array(':name'=>$_SESSION['name'],':email'=>$_SESSION['email'],':topic'=>$_SESSION['topic'],':time'=>$_SESSION['times']));
            if($update1 && $update2){
                $do->commit();

                return TRUE;
            }
            else{
                return FALSE;
            }

      
            


        }catch(PDOException $e){

            $do->rollBaCK();
            return FALSE;

        }



}

/*
* Connection with database is established by  creating a PDO  object
* $pdo is the variable referencing the pdo object. 

*/

$db_hostname = "";
$db_database = "";
$db_username = "";
$db_password = "";
$db_charset = "";
$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
$opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false
);
try {
$pdo = new PDO($dsn,$db_username,$db_password,$opt);

/*

*  before displayingg the form to the user  first it is checked wheather the combined capacity is 0 or not.
*  if 0 then a message indicating all places are filled is displayed.
*  if greater than zero the proceeded to the next step to check is user has inputed every field or not and clicked the submit button.
*  if user has included all the input and pressed the submit button name and email is validated.
*  if either of the name or email fails then a message  is displayed  and both name and email is removed from the session for the user to enter again.
*  if either of the name or email succeeds then number of slot still avaialable is checked.
*  if there are still slot avvailable a transaction is started to  update the database.
*  if transaction is success then session is destrouyed and a success message is displayed.
*  if transaction is failed then a failure message is displayed
*  

*/

$stmt3=$pdo->query("select capacity from training");
$capacity=$stmt3->fetchAll();
$sumCapacity=0;
foreach($capacity as $value){
    foreach($value as $key){
        $sumCapacity+=$key;
    }
}

if($sumCapacity>0){
    
    if($_SESSION['name']!="" && $_SESSION['email']!="" && isset($_SESSION['topic']) && isset($_SESSION['times'])&& isset($_REQUEST['submit'])){
        if(validateUserEmail()&& validateUserName()){
            $slotLeft=checkSlotStillAvailable($pdo);
            if($slotLeft>0){

                if(bookingSlot($pdo,$slotLeft)){
                    echo '<div id="success">boooking was successfull for the topic '.$_SESSION[topic].', for the time slot '.$_SESSION['times'].' </div>';
                    unset($_REQUEST['submit']);

                    $_SESSION=array();
                    if (session_id()!=""||isset($_COOKIE[session_name()])){
                            setcookie(session_name(), session_id(),time()-2592000 ,'/');
            
                        }
             
                    session_destroy();
                }else{
                        echo '<div class="error">Sorry the booking was unsuccessfull</div>';
                }
            }else{
                echo '<div class="error">Booking was unsuccessfull.<br>Sorry no slot availabe right now.<br>Do you wish to choose some other topic and time?</div>'; 
                unset($_SESSION['topic']);
                unset($_SESSION['times']);
                unset($_REQUEST['submit']);
                
                
            }

        }else{
            echo'<div class="error">Sorry the booking was unsuccessfull<br> please enter a valid name and email</div>';
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            unset($_REQUEST['submit']);
        }

    
    }else{
        if(isset($_REQUEST['submit'])){
            echo'<div class="error">Please enter all the inputs.</div>';
            unset($_REQUEST['submit']);
            
            
        }
    }

    


selectTopic($pdo);
displayTimeSlot($pdo);
displayUserInfoForm();


   
}else{
    echo '<div class="error">All places are  filled</div>';
}






} catch (PDOException $e) {
    
    exit('<div class="error">Sorry Problem with data base connection. Please try again later.</div>');
}

?>
</body>
</html>