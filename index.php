<?php
include("funcs.php");
echo $header;
if(!isset($_GET['login'])) {
  $_GET['login'] = 0;
}
if(!isset($_GET['logout'])) {
  $_GET['logout'] = 0;
}
if(!isset($_GET['addQuestion'])) {
  $_GET['addQuestion'] = 0;
}
if(!isset($_GET['questionID'])) {
  $_GET['questionID'] = 0;
}
if(!isset($_GET['attempt'])) {
  $_GET['attempt'] = 0;
}
if(!isset($_GET['check'])) {
  $_GET['check'] = 0;
}
if(!isset($attemptResult)) {
  $attemptResult = 0;
};
if(!isset($_SESSION['active'])) {
  $_SESSION['active'] = 0;
};
$now = time();
if($_SESSION['thisID']) {
  Connect();
  $then = OneVal("select timestamp from users where user_id = '".$_SESSION['thisID']."'"); 

}
// MAKING AN ATTEMPT
if($_GET['attempt'] == 1) {
  RoadBlock("Please login to play.");
  
  $qid = filter_var($_GET['questionID'], FILTER_SANITIZE_STRING);
  $res2 = Query("select * from correct where question_id = '".$qid."' and user_id = '".$_SESSION['thisID']."'");
  if(mysqli_num_rows($res2) > 0) {
    Message("You've already answered this question correctly!");
  }
  $res = Query("select * from questions where question_id = '".$qid."'");
  $arr = mysqli_fetch_row($res);
  if($arr[6] == $_SESSION['thisID']) {
    Message("You can't answer your own questions!");
  }
  Connect();
  $qid = filter_var($_GET['questionID'], FILTER_SANITIZE_STRING);
  $res = Query("select * from questions where question_id = '".$qid."'");
  $arr = mysqli_fetch_row($res);
  $tq = $arr[3];  
  $aq = $arr[4];
  // die(Message("")); 
  
  ?>
  <h4>type your answer below: 
  <form action = "index.php?check=1&qid=<?php echo $qid ?>" method ="post">
    <div class="row">
      <div class="col s6 input-field">
        <div class="card blue darken-4 white-text center-align">
          <div class="card-title"> <?php echo $tq?>
          </div>
        </div>
        <input type="text" class="validate" id="input" placeholder="song title" name="title" required="">
      </div>
      <div class="col s6 input-field">
        <div class="card orange center-align">
          <div class="card-title"><?php echo $aq?>
          </div> 
        </div>
        <input type="text" class="validate" id="input" placeholder="artist" name="artist" required="">
      </div>
    </div>
    <button type="submit" class="btn btn-dark">check!</button>
    <a href = "index.php?attempt=1&questionID=<?php echo $qid ?>"><span class = "btn red">clear</span></a> 
    <a href = "index.php"><span class = "btn green">back</span></a> 
  </form>
  <?php  
  die();
}
if($_GET['check']) {
  $artist = filter_var($_POST['artist'], FILTER_SANITIZE_STRING);
  $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
  $qid = filter_var($_GET['qid'], FILTER_SANITIZE_STRING);
  $res = Query("select * from questions where question_id = '".$qid."'");
  $arr = mysqli_fetch_row($res);
  if(strtoupper(chop($arr[1])) == strtoupper(chop($artist)) && strtoupper(chop($arr[2])) == strtoupper(chop($title))) {
    Query("insert into correct (`user_id`, `question_id`) VALUES ('".$_SESSION['thisID']."','".$qid."')");
    Query("update users set score = score + 5 where user_id = '".$_SESSION['thisID']."'");
    $attemptResult = 1;
  } else {
     $attemptResult = -1;
  };
}
if($_GET['login']==1){
  if(!isset($_POST['username'])){
    $_POST['username']=0;
  }
  if(!isset($_POST['pwd'])){
    $_POST['pwd']=0;
  }
  $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
  $pwd = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);
  CheckLogin($username, $pwd);
}
if($_GET['addQuestion']){
  if(!isset($_POST['artist'])){
    $_POST['artist']=0;
  }
  if(!isset($_POST['title'])){
    $_POST['title']=0;
  }
  if(!isset($_POST['genre'])){
    $_POST['genre']=0;
  }
  $artist = filter_var($_POST['artist'], FILTER_SANITIZE_STRING);
  $artist = rtrim($artist);
  $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
  $title = rtrim($title);
  $genre = filter_var($_POST['genre'], FILTER_SANITIZE_STRING);
  $genre = rtrim($genre);
  Connect();
  AddQuestion($artist, $title, $genre);
}
if($_GET['logout']) {
  LogOut();
}
// LOGGED IN
if($_SESSION['loggedIn'] == 1) {  
  UpdateOnline();
  echo"<div class = 'card green'><div class = 'card-title white-text'>You're logged in as <strong>".$_SESSION['username']."</strong> [<a href = 'index.php?logout=1'>logout</a>]</div></div>";
  if($attemptResult == -1) {
    $message = "<div class = 'card red white-text center-align'><div class = 'card-title'><strong>$title</strong> by <strong>$artist</strong> is incorrect. 
      <span class='btn green'><a href='index.php?attempt=1&questionID=".$qid."'>TRY AGAIN?</a></span></div></div>";
  } else if($attemptResult == 1) {
    $message = "<div class = 'card green white-text'><div class = 'card-title'>Correct! You scored 5 points.</div></div>";
  } else {
    $message = "";
  };

  ?>
  <div class = "row">
  <div class="col s12 m2 l2 blue-grey lighten-5 z-depth-2">
  <?php 
  PrintScores();
  ?>
  <div class="card white">
        <div class="card-title grey white-text center-align">INSTRUCTIONS</div>
        <strong> Type the FULL song title and artist</strong>. The app will reduce this to just the first letter of each word. Players 
      then try to guess the answers by clicking on the questions buttons below. Every correct answer is worth 5 points.
      </div>
  </div>
  <div class="col s12 m10 l10">
    <div class = "card blue lighten-3">
      
    <?php echo $message ?>
    <div class="card blue darken-3"><div class="card-title white-text">Type a new question here</div>
    <form action = "index.php?addQuestion=1" method ="post">
      <div class="row">
        <div class="col s6 input-field">
          <input type="text" class="validate" id="input" placeholder="artist" name="artist" required="">
        </div>
        <div class="col s6 input-field">
          <input type="text" class="validate" id="input" placeholder="song title" name="title" required="">
        </div> 
      </div>  
      <button type="submit" class="lighten-4 waves-effect waves-light btn orange darken-2" ><i class="large material-icons">add_circle</i> add question</button>
    </form></div>
     <div class = "card blue lighten-3">
      <div class="card-title white">
        Click one of the questions below to try and answer correctly:
      </div>
     </div>
     <?php GetQuestions(); ?>
  </div>
</div>
  <?php
  die($footer);  
} else {
  ?><hr>
    <form action = "index.php?login=1" method ="post">
      <div class="row">
        <div class="col s4">
          <input type="text" class="field-input" id="input" placeholder="username" name="username" required="">
        </div>  
        <div class="col s4">
          <input type="password" class="field-input" id="pwd" placeholder="password" name="pwd" required="">
        </div>
        <div class="col s4">
          <button type="submit" class="btn btn-dark" >login</button>
          <a href="register.php"><button type="button" class="btn">
    Register <span class="glyphicon glyphicon-pencil"></span></button></a>
        </div>
      </div>      
    </form>
    <hr>
  <div class="row">
    <div class="col s12 m2 l2 blue-grey lighten-5 z-depth-2">
      <?php PrintScores(); ?>
    </div>
    <div class="col s12 m10 l10">
      <?php GetQuestions(); ?>
    </div>
  </div>
<?php } die($footer); ?>

