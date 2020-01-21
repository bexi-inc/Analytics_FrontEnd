<?
if (isset($_COOKIE['baw_session_id'])) {
   // unset($_COOKIE['baw_session_id']); 
    setcookie('baw_session_id', null,time()-3600);
    //return true;
    echo "eliminado";
} else {
   // return false;
	echo "false";
}

//setcookie("baw_session_id", "", time() - 3600); 

?>