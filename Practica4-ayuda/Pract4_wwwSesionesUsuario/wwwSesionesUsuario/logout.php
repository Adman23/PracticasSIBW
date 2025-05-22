<?php
  session_start();
      
  session_destroy();
  
  header("Location: unaPaginaCualquiera.php");
    
  exit();
?>
