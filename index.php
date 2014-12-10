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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>My Facebook App</title>
</head>
<body>
  <section id="status">
    <?php 
    if($_POST){
      //get the specific group's feed
        $group_id = $_POST['groups'];
        $request_url = 'https://graph.facebook.com/v2.2/'.$group_id.'/feed?access_token='.$_SESSION['fb_token'];
        $ch = curl_init();
        curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        print_r($result);
      }else{
        //get user session
        try {
    $session = $helper->getSessionFromRedirect();

  } catch(FacebookRequestException $ex) {
      // When Facebook returns an error
      echo $ex->getMessage();
  } catch(\Exception $ex) {
      // When validation fails or other local issues
      echo $ex->getMessage();
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
    <form action ="" method="post" name="select_groups">
      <select name="groups">
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
      <input type="submit" name="Submit" value="Submit" />
    </form>
    <?php 
 }else{
    //display login url and get user_groups permission
    $login_url = $helper ->getLoginUrl(array('scope'=>'user_groups'));
    ?>
    <a href="<?php echo $login_url; ?>">Login</a>
    <?php }} ?>
  </section>
</body>
</html>