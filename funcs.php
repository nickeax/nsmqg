<?php //FUNCS_PHP
session_start();
$header = "
<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta http-equiv='X-UA-Compatible' content='ie=edge'>
<link href=\"https://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">
<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css\">


<link rel='stylesheet' href='./css/main.css'>
<title>Non Searchable Music Quiz Game</title>
</head>
<body>
<header class=\no-padding \"><div class=\navbar-fixed\>  
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js\"></script>
<h1 class=\"center-align\">Non Searchable <strong>Music</strong> Quiz Game</h1>
</div></header>
<div class = 'container'>
<a class=\"btn-floating btn-large red\" href=\"index.php\"><i class=\"large material-icons\">autorenew</i></a>
";


$footer = "
    </div>
  </div>
</body>
</html>
";
if(!isset($_SESSION['loggedIn'])) {
  $_SESSION['loggedIn'] = 0;
}
if(!isset($_SESSION['thisID'])){
    $_SESSION['thisID'] = "";
}
if(!isset($_SESSION['fname'])){
    $_SESSION['fname'] = "";
}
if(!isset($_SESSION['lname'])){
    $_SESSION['lname'] = "";
}
if(!isset($_SESSION['userID'])){
    $_SESSION['userID'] = "";
}
function Error($message, $sqlError){
    print "<div class=\"alert alert-warning\">";
    print"<p>$message</p><hr />";
    print"<em>Messages</em>:[".$sqlError."] click <a href = \"index.php\">[here]</a> to go home</div>";
    die();
}//end Error
function Message($message){
    print "<div class=\"alert alert-info\">";
    print"<p>$message <tt>[";
    print"<a href = \"index.php\">go back]</a></tt></p></div>";
    die();
}//end Error
function Connect(){
    //print"The site is currently down...";
    $link = "";
    include("lsconfig.php");
    if(!$link = mysqli_connect($host, $user, $password, $dbname)){
      Error("Connection trouble!!",mysqli_info($link));
    }//end if !mysqli
    mysqli_select_db($link, $dbname);
    return $link;
}//end connect
function Query($q){
    //print"The site is currently down...";
    $link = Connect();
    if(!$res = mysqli_query($link, $q)){
        print mysqli_info($link);
        Error("Database Error.", mysqli_info($link));
    }
    mysqli_close ( $link );
    return $res;
}
function QueryArr($q){
  Connect();
  if(!$res = Query($q))
      Error("Database Error.", mysqli_error());
  $arr = mysqli_fetch_array($res);
    return $arr;
}
function CheckLogin($userName, $pwd){
		$pwd = md5($pwd);
    $sql = "select * from users where username = '".$userName."' and password = '".$pwd."'";
    $link = Connect();
    if(!($res = mysqli_query($link, $sql)))Error("Database Error...", mysqli_report($link));
    $arr = mysqli_fetch_row($res);
    if(!$num = mysqli_num_rows($res)){
        $_SESSION['loggedIn'] = 0;
    }else{
         $_SESSION['loggedIn']= 1;
         $_SESSION['thisID']=$arr[0];
         $_SESSION['username'] = $userName;
         $userID = OneVal("select user_id from users where username = '".$userName."'");
         Query("update users set logged_in = 1 where user_id = $userID");
         Query("update users set uip = '".$_SERVER['REMOTE_ADDR']."' where user_id = $userID");
         //Error("Login success!","You have logged in");
    }
}//end CheckLogin     style = \"text-align:left\"
function LogOut(){
    $userID = OneVal("select user_id from users where username = '".$_SESSION['username']."'");
    Query("update users set logged_in = 0 where user_id = '".$userID."'");
    $_SESSION['loggedIn'] = false;
    $_SESSION['username'] = "";
    $_SESSION['thisID'] = 0;
}
function OneVal($q){
    Connect();
    $res = Query($q);
    $arr = mysqli_fetch_row($res);
    return $arr[0];    
}//end OneVal
function Build($str) {
  $buildArray = explode(' ', $str);
  $buildStr = "";
  foreach ($buildArray as  $aa) {
    $buildStr .= $aa[0];
  }
  return $buildStr;  
}
function AddQuestion($artist, $title, $genre) {
  $userID = $_SESSION['thisID'];
  Connect();
  $aq = Build(chop($artist));
  $tq = Build(chop($title));
  $res = Query("select * from questions where `artist` = '".$artist."' and `song_title` = '".$title."'");
  if(!mysqli_num_rows($res)) {
    Query("insert into questions(`artist`, `song_title`,`title_question`, `artist_question`,`genre`, `user_id`) VALUES 
      ('".$artist."', '".$title."','".$tq."','".$aq."', '".$genre."', '".$userID."')"); 
  }else  echo "<hr>That question aready exists.<hr>";
}
function RoadBlock($msg) {
  if(!$_SESSION['loggedIn']) {
    Message("<div class = 'card red white-text'>".$msg."</div>");
  }
}
function GetQuestions(){
  $extraClass = "questionBox";
  Connect();
  $res = Query("select * from questions where user_id != '".$_SESSION['thisID']."'");
  if(mysqli_num_rows($res) == 0) {
    echo "There are no questions yet";
  } else {    
    $res = Query("select * from questions where user_id != '".$_SESSION['thisID']."'");
    while ($arr = mysqli_fetch_row($res)) {
      $res2 = Query("select * from correct where user_id = '".$_SESSION['thisID']."' and question_id = '".$arr[0]."'");
      if(mysqli_num_rows($res2) > 0) {
        $extraClass = "questionBox badge z-depth-3 grey darken-4 text-white hoverable";
      }else {
        $extraClass = "questionBox badge z-depth-1 blue darken-3 text-white hoverable";
      }
      $asker = OneVal("select username from users where user_id = ".$arr[6]);
      $title = strtoupper($arr[3]);
      $artist = strtoupper($arr[4]);
      $arr[3];
      echo "<a href=\"index.php?attempt=1&questionID=".$arr[0]."\"><div class = 'card card-content ".$extraClass."'><strong>".$title."</strong> 
        <tt>by</tt> <strong>".$artist."</strong><br><span class = \"asker\">asked by ".$asker."</span></div></a>";
    } 
  } 
}
function PrintScores() {
  Connect();
  $q = "select * from users";
  $res = Query($q);
  $numOfPlayers = mysqli_num_rows($res); 
  $res = Query("select * from users order by score desc");?>
  <div class="card blue darken-2"><div class="card-title white-text center-align">LEADERBOARD</div></div><?php
  
  echo "<div class=\"card grey lighten-3 black-text z-depth-2\">";
  $i = 0;
  
  echo"<table class='right-align compact'><thead class='grey darken-3 white-text'><th>$numOfPlayers</th><th>plrs</th><th></th></thead>";
  echo "<tbody>";
  $minute = 60;
  $then = time() - $minute;
  while($arr=mysqli_fetch_row($res)) {
    $status = $arr[1] > $then;
    $colour = $status ? "green darker-4" : "red";
    $status = $status ? "<i class='material-icons $colour'>account_circle</i>" : "<i class='material-icons $colour'>account_circle</i>";
    echo "<tr class = ''>";
    echo"<td>$status</td><td><strong>$arr[2]</strong></td><td class='right-align blue darken-2 white-text center-align'><strong>$arr[6]</strong></td></tr>";
    $i++;
  }
  echo "</tbody></table></div>";
}
function UpdateOnline() {
  $now = time();
  Connect();
  $q = "update users set timestamp = '".$now."' where user_id = '".$_SESSION['thisID']."'";
  Query($q);
}
?>