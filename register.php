<?php
include('funcs.php');
  if(!isset($_POST['reg'])){
  	$_POST['reg']="";
  }
  /*REGISTER * * * * * * * * * * **/
  $username = "";
  $password = "";
  if($_POST['reg'] == "reg"){
      $passwordchk = $_POST['pwdchk'];
      $password = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);
      if($password != $passwordchk){
        Error("Please enter your details again", "The passwords did not match.");
      };
      $password = md5($password);
      $username = filter_var($_POST['username'],FILTER_SANITIZE_STRING);
      
      $res = Query("select username from users where username = '".$username."'");
      if(mysqli_num_rows($res) > 0) {
        echo $header."<div class = \"container\">";
        Message("Please try a different username, that one is in use".$footer);
      }

      Connect();
      $now = time();
      /**/
      $sql = "INSERT INTO users(`timestamp`, `username`, `password`, `admin`) VALUES ('$now', '$username', '$password', 0)";

      if(Query($sql)) {
        echo $header;
        echo "<div class=\"alert alert-success\">Thanks! You've successfully registered. </div>";
        echo "<a href =\"index.php\"><button type=\"button\" class=\"btn btn-info\">Login <span class=\"glyphicon glyphicon-log-in\"></span></button></a>";
        echo $footer;
      };
      die();
    }

    /*REGISTER*/
    echo $header;
  ?><div class="container">
    <h4>Please enter your details below to register for a user account or
      <a href="index.php"><button type="button" class="btn btn-info">Login <span class="glyphicon glyphicon-log-in"></span></button></a></h4>

    	<form action = "register.php" method ="post">
        <input type="hidden" name="reg" value="reg">
        <div class="row">
          <div class="col s4">
            <label for="input">username:</label>
            <input type="text" class="form-control" id="input" placeholder="first name" name="username" required="">
          </div>
          <div class="col s4">
            <label for="input">Password:</label>
             <input type="password" class="form-control" id="pwd" placeholder="password" name="pwd" required="">
          </div>
          <div class="col s4">
            <label for="input">Retype Password:</label>
             <input type="password" class="form-control" id="pwd" placeholder="password" name="pwdchk" required="">
          </div>
        </div>
        <button type="submit" class="btn center-align">register</button>
   	  </form>
<?php echo $footer ?>
