<?php 
session_start();
require_once('autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

// init app with app id (APPID) and secret (SECRET)
FacebookSession::setDefaultApplication('your_app_id','your_secret');

// login helper with redirect_uri
$helper = new FacebookRedirectLoginHelper( 'your_redirect_uri' );

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Facebook App</title>
  <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
  <div class="container-fluid">
      <div class="row">
        <div class="page-header">
          <h1 class="text-center">My Facebook App</h1>
        </div>
      </div>
    <div class="row">
      <nav class="navbar navbar-default" role="navigation">
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="http://szeching.com"><span class="glyphicon glyphicon-home"></span> Home</a></li>
            <li class="active"><a href="#">Facebook Group Feed</a></li>
          </ul>
        </div>
      </nav>
    </div>
    <?php 
    if($_POST){
      //get the specific group's feed
        $group_id = $_POST['groups'];
        $request_url = 'https://graph.facebook.com/v2.2/'.$group_id.'/feed?access_token='.$_SESSION['fb_token'].'&limit=500';
        $ch = curl_init();
        curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        $r_array = json_decode($result,true);
        foreach ($r_array[data] as $value){
          echo '<div class="row"><div class="col-md-4"><span class="glyphicon glyphicon-bookmark"></span><p>'.$value[message].'</p><p class="text-muted">'.$value[from][name].'&nbsp;&nbsp;<small>||&nbsp;&nbsp;'.$value[updated_time].'&nbsp;&nbsp;||&nbsp;&nbsp;<a href="'.$value[actions][0][link].'">[Link]</a></small></p></div><div class="col-md-8">回复消息：';
            foreach ($value[comments][data] as $comment){
              echo '<p><span class="glyphicon glyphicon-comment"></span>&nbsp;&nbsp;&nbsp;&nbsp;'.$comment[message].'&nbsp;&nbsp;&nbsp;&nbsp;<small class="text-muted">'.$comment[from][name].'&nbsp;&nbsp;||&nbsp;&nbsp;'.$comment[created_time].'</small></p>';
            }   
          echo '</div></div><hr />';
        }

      }else{
        //get user session
          try {
          $session = $helper->getSessionFromRedirect();

          } catch(FacebookRequestException $ex) {
        // When Facebook returns an error
          echo '<p class="bg-danger">'.$ex->getMessage().'</p>';
          } catch(\Exception $ex) {
        // When validation fails or other local issues
          echo '<p class="bg-danger">'.$ex->getMessage().'</p>';
          }
    if (isset( $session )) {
      //save session token
      $_SESSION['fb_token'] = $session ->getToken();
      //get user's groups
      $request = new FacebookRequest($session,'GET','/me/groups?fields=name');
      $response = $request->execute();
      $graphObject = $response->getGraphObject();
      $groups = $graphObject->asArray();
    ?>
    <form class="form-inline" action ="" method="post" name="select_groups" role="form">
       <select class="form-control input-lg" name="groups">
        <?php 
        foreach ($groups[data] as $row) {
          $array = json_decode(json_encode($row), true);
          foreach ($array as $key => $value) {
            if ($key == 'name') {
               $group_name = $value;
            }elseif ($key == 'id') {
               $group_id = $value;
            }
          }
          echo '<option value="'.$group_id.'">'.$group_name.'</option>';
        }
        ?>
      </select>
      <input type="submit" name="Submit" class="btn btn-primary btn-lg" value="Submit" />
    </form>
    <?php 
 }else{
    //display login url and get user_groups permission
    $login_url = $helper ->getLoginUrl(array('scope'=>'user_groups'));
    ?>
    <a class="btn btn-primary btn-lg btn-block" href="<?php echo $login_url; ?>" role="button">Login</a>
    <?php }} ?>
  <div class="row">
      <ol class="breadcrumb">
      <li><a href="http://szeching.com">Home</a></li>
      <li class="active">My Facebook App</li>
      </ol>
    </div>
    <footer class="row">
    <p class="text-center"><span class="glyphicon glyphicon-copyright-mark"></span> 2014 Y.Cheung</p>
    </footer>
    </div>
   <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</body>
</html>