<?php

$email = $_POST['email'];

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo "Invalid email";
}else{
    echo "Valid email";
}

?>
