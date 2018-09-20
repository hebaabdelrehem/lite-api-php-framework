<?php
////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                           This is not a free script                                            */
/*                              All rightes reserved to traxeed.net and traxeed.com                               */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 *
 */
class classApi extends traxeed
{

    //---------------------------- User Data function - Return an array ----------------------------//

    public function __construct()
    {
    }

    
    //---------------------------- check if this rest. parent or child ----------------------------//

    public function ifParentRest($ownerId = '')
    {
        global $traxeed_mysqliDB;

        $query = "SELECT * FROM `pl_resturant` WHERE ownerId = ?";
        $params = array($ownerId);
        $restdata = $traxeed_mysqliDB->selectone($query, 's', $params);
        if ($restdata['parentId'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    //---------------------------- get parentId of Branch Using Branch OwnerID --------------------------------//

    public function getParentIdOfBranch($bownerId = '')
    {

        global $traxeed_mysqliDB;

        $query = "SELECT * FROM `pl_resturant` WHERE ownerId = ?";
        $params = array($bownerId);
        $restdata = $traxeed_mysqliDB->selectone($query, 's', $params);

        $query2 = "SELECT * FROM `pl_resturant` WHERE id = ?";
        $params2 = array($restdata['parentId']);
        $restdata2 = $traxeed_mysqliDB->selectone($query2, 's', $params2);

        if ($restdata2) {
            return $restdata2['ownerId'];

        } else {
            return false;
        }


    }

    //-----------------------------------get resturant menu by id ----------------------------------------------//

    public function getRestMenu($ownerId = '')
    {
        global $traxeed_mysqliDB;
        $query1 = "SELECT * FROM `pl_resturant` WHERE ownerId = ?";
        $params1 = array($ownerId);
        $restdata = $traxeed_mysqliDB->selectone($query1, 's', $params1);

        $query2 = "SELECT * FROM `pl_menu` WHERE `restId` = ?";
        $params2 = array($restdata['id']);
        $restMenu = $traxeed_mysqliDB->selectall($query2, 's', $params2);

        if ($restMenu) {
            return $restMenu;

        } else {
            return false;

        }
    }

    //-------------------------------------get rest data --------------------------------------------------------//

    public function getRestData($restId = '')
    {
        global $traxeed_mysqliDB;
        $query1 = "SELECT * FROM `pl_resturant` WHERE id = ?";
        $params1 = array($restId);
        $restdata = $traxeed_mysqliDB->selectone($query1, 's', $params1);
        if ($restdata) {
            return $restdata;
        } else {
            return false;
        }
    }

    //----------------------------------- get submenu of menu by menu id ------------------------------------------//

    public function getSubMenubyMenuId($menuID = '')
    {
        global $traxeed_mysqliDB;

        $query1 = "SELECT * FROM `pl_submenu` WHERE `menuId` = ?";
        $params1 = array($menuID);
        $restSubMenu = $traxeed_mysqliDB->selectall($query1, 's', $params1);

        if ($restSubMenu) {
            return $restSubMenu;
        } else {
            return false;
        }

    }

    //-------------------------------------get extra of submenu by sub id ----------------------------------------------//

    public function getExtrabySubMenuId($submenuID = '')
    {
        global $traxeed_mysqliDB;

        $query1 = "SELECT * FROM `pl_extra` WHERE `submenuId` = ?";
        $params1 = array($submenuID);
        $restExtras = $traxeed_mysqliDB->selectall($query1, 's', $params1);

        if ($restExtras) {
            return $restExtras;
        } else {
            return false;
        }

    }

    //----------------------------------------get user rate something -------------------------------------------------//

    public function getUserRates($raterId = '', $ratedId = '', $ratedType = '')
    {
        global $traxeed_mysqliDB;

        $query1 = "SELECT * FROM `pl_rates` WHERE `ratedId` = ? AND `raterId` = ? AND `ratedType`=?";
        $params1 = array($ratedId, $raterId, $ratedType);
        $rate = $traxeed_mysqliDB->selectall($query1, 'sss', $params1);

        if ($rate) {
            return $rate;
        } else {
            return false;
        }


    }

    //---------------------------------------------change order status ------------------------------------------------//

    public function changeOrderStatus($orderId = '', $status = '')
    {

        global $traxeed_mysqliDB;

        if ($status == 7) {
            $query = "UPDATE `pl_orders` SET `ordersStatus` = ? , `ordersAvailablty`='0' WHERE id = ?";
        } else {
            $query = "UPDATE `pl_orders` SET `ordersStatus` = ? WHERE id = ?";
        }
        $values = array($status, $orderId);
        $change = $traxeed_mysqliDB->update($query, 'ss', $values);
        if ($change) {
            return $change;
        } else {
            return false;
        }

    }

    //-----------------------------------------------set Delivery Time --------------------------------------------------//

    public function setDeliveryTime($orderId = '', $time = '')
    {

        global $traxeed_mysqliDB;

        $query = "UPDATE `pl_orders` SET `ArrivalTime` = ? WHERE id = ?";
        $values = array($time, $orderId);
        $setTime = $traxeed_mysqliDB->update($query, 'ss', $values);

        if ($setTime) {
            return $setTime;
        } else {
            return false;
        }
    }

    //------------------------------------------------Cancel Order----------------------------------------------------------//


    public function cancelOrder($orderId = '', $userType = '')
    {

        global $traxeed_mysqliDB;

        if ($userType == 'delivery') {
            $query = "UPDATE `pl_orders` SET `ordersStatus` = '8' , `ordersAvailablty`='3' WHERE id = ?";
        } else {
            $query = "UPDATE `pl_orders` SET `ordersStatus` = '0' , `ordersAvailablty`='3' WHERE id = ?";
        }
        $values = array($orderId);
        $cancel = $traxeed_mysqliDB->update($query, 's', $values);
        if ($cancel) {
            return $cancel;
        } else {
            return false;
        }

    }

    //-----------------------------------------------push notifications in android -------------------------------------//

    public function pushNotification($message = '', $type = '', $token = '', $dataarray = '')
    {

        $ids = array($token);

        $data = array('message' => $message, 'type' => $type);
        $data = array_merge($data, $dataarray);
        $apiKey = 'AIzaSyDz-m_RCwK5PMYOXqvIzmA8fx9X1gS9gJY';
        $url = 'https://gcm-http.googleapis.com/gcm/send';
        $post = array(
            'registration_ids' => $ids,
            'data' => $data,
        );
        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'GCM error: ' . curl_error($ch);
            return false;
            die();

        }

        curl_close($ch);
        return true;
    }

    //------------------------------------------------push notification in apple -------------------------------------------//

    public function pushNotificationIOS($alert='', $message = '', $type = '', $token = '', $dataarray = '')
    {
        $ctx = stream_context_create();
        
        //stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns-dev-GahezCertPemDev.pem');
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns-ProDuction-cert.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        // Create the payload body
        $body['aps'] = array('alert' => $alert ,'data'=> $message ,'type' => $type,'sound' => 'default');
        $body['aps']= array_merge($body['aps'], $dataarray);
        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result){
           
        return false;
        die();
        }
        else {
       
        return true;
        
        }
        // Close the connection to the server
        fclose($fp);


    }

    //-------------------------------------------- get Rest id of order -------------------------------------------------//

    public function getOrderData($orderId = "")
    {

        global $traxeed_mysqliDB;

        $query1 = "SELECT * FROM `pl_orders` WHERE `id` = ?";
        $params1 = array($orderId);
        $OrderData = $traxeed_mysqliDB->selectone($query1, 's', $params1);

        if ($OrderData) {
            return $OrderData;
        } else {
            return false;
        }

    }

    //----------------------------------------------get token and OS For User ----------------------------------------------//

    public function getUserData($userId = "")
    {

        global $traxeed_mysqliDB;


        $query1 = "SELECT * FROM `users` WHERE `id` = ?";
        $params1 = array($userId);
        $userData = $traxeed_mysqliDB->selectone($query1, 's', $params1);

        if ($userData) {
            return $userData;
        } else {
            return false;
        }

    }

    //----------------------------------------------- upload message file --------------------------------------------------------//

    public function UploadFile($file = "")
    {

        $contentsFolder = "../contents/resturant/";
        $filename=$file['name'];
        $randname = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
        $filePhysicalPath = $file['tmp_name'];
        $filename = $randname . $filename;
        $destination = $contentsFolder . $filename;

        $status = move_uploaded_file($filePhysicalPath, $destination);
        if($status)
        {
            return $filename;

        }
        else{
            return false;
        }

    }

    //----------------------------------------------send message function ------------------------------------------------------//

    public function SendMessage($senderId="" , $receiverId="" , $message="" , $filename=""){
        global $traxeed_mysqliDB;

        $time = date('Y-m-d H:i:s');

        $query = "INSERT INTO `pl_chat` (`senderId`, `Message`, `receiverId` , `MessageTime` ,`chatPhoto`) VALUES (?,?,?,?,?)";
        $values = array($senderId,$message,$receiverId,$time,$filename);
        $insert = $traxeed_mysqliDB -> insert($query, 'sssss', $values);

        if($insert)
        {
            return true;
        }
        else{
            return false;
        }

    }

    //----------------------------------------------- parse timeline message --------------------------------------------------//

    /*public function ParseTimeLineMessage($actorname='',$actorType='',$actedOnName='',$actedOnType='',$action='')
    {
        $messageBase="لقد قام ";
        if(isset($actorType)){
        if($actorType == '3'){
        $messagePart1="المستخدم ";
        }
        elseif ($actorType == '4') {
        $messagePart1="الموصل ";
        }
        elseif ($actorType == '2') {
        $messagePart1="المطعم ";
        }
        }
        if(isset($actorname)){
            $messagePart2=$actorname." ";
        }
        if(isset($action)){
            if($action == "follow")
            {
                $messagePart3="بمنتابعه ";
            }
            elseif($action == "order"){
                $messagePart3="بطلب من "
            }
            elseif($action == "rate"){
                $messagePart3="بتقييم "
            }
        }
        if(isset($actedOnType))
        {


        }
    }*/
}

$classApi = new classApi();

////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                           This is not a free script                                            */
/*                              All rightes reserved to traxeed.net and traxeed.com                               */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
