<?php
////////////////////////////////////////////      MPZ V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                          MPZ is MINA Version Of CMS                                            */
/*                              All rightes reserved to MPZ ModernPharaohs LTD                                    */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include 'init.php';

/**
 * The main class of the api is stands here
 */
class Api extends traxeed
{

    public function __construct()
    {
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
        } else if (isset($_POST['action'])) {
            $action = $_POST['action'];
        } else {
            echo miss_params;
            exit();
        }

        $this->$action();

    }

    //---------------------------- Get Menu For User functions ----------------------------//
    public function getMenuforUser()
    {
        global $classApi;
        global $success;

        if (isset($_POST['id']) && isset($_POST['userId'])) {
            $id = $_POST['id'];
            $userId = $_POST['userId'];

            if ($classApi->ifParentRest($id)) {

                $menu = $classApi->getRestMenu($id);
                foreach ($menu as $menux) {
                    $submenu = $classApi->getSubMenubyMenuId($menux['id']);

                    foreach ($submenu as $submenux) {
                        if ($classApi->getUserRates($userId, $submenux['id'], 'submenu')) {
                            $submenux['rates'] = '1';
                        } else {
                            $submenux['rates'] = '0';

                        }

                        $extras = $classApi->getExtrabySubMenuId($submenux['id']);
                        if ($extras) {

                            foreach ($extras as $extrasx) {
                                $submenux['extras'][] = $extrasx;
                            }
                        }
                        $menux['item'][] = $submenux;
                    }
                    $newMenus[] = $menux;
                }

                $success = array("status" => "true", "message" => "operation done successfully");
                $return = (object)$success;
                $return->menus = $newMenus;
                print_r(json_encode($return));


            } else {

                $id = $classApi->getParentIdOfBranch($id);

                $menu = $classApi->getRestMenu($id);
                foreach ($menu as $menux) {
                    $submenu = $classApi->getSubMenubyMenuId($menux['id']);
                    foreach ($submenu as $submenux) {
                        if ($classApi->getUserRates($userId, $submenux['id'], 'submenu')) {
                            $submenux['rates'] = '1';
                        } else {
                            $submenux['rates'] = '0';
                        }

                        $extras = $classApi->getExtrabySubMenuId($submenux['id']);

                        if ($extras) {

                            foreach ($extras as $extrasx) {
                                $submenux['extras'][] = $extrasx;
                            }
                        }
                        $menux['item'][] = $submenux;
                    }
                    $newMenus[] = $menux;
                }

                $success = array("status" => "true", "message" => "operation done successfully");
                $return = (object)$success;
                $return->menus = $newMenus;
                print_r(json_encode($return));


            }


        } else {
            echo miss_params;
        }
    }

    //----------------------------- Accept Order ----------------------------------------// 
    public function AcceptOrder()
    {

        global $classApi;
        global $success;

        if (isset($_POST['orderID']) && isset($_POST['OwnerType']) && isset($_POST['Acceptance'])) {

            $OrderData = $classApi->getOrderData($_POST['orderID']);
            $restData = $classApi->getRestData($OrderData['RestId']);
            $tokenData = $classApi->getUserData($restData['ownerId']);
            $orderOwnerName = $classApi->getUserData($OrderData['ordersUserID']);

            if ($_POST['OwnerType'] == 'delivery') {

                if ($_POST['Acceptance'] == '1') {
                    $classApi->changeOrderStatus($_POST['orderID'], '2');
                    $array = array('orderId' => $_POST['orderID'], 'orderuserName' => $orderOwnerName['name']);
                    if ($tokenData['deviceOS'] == "Android") {

                        $pushNotification = $classApi->pushNotification("تم عمل طلب جدبد", "AcceptOrderForResturant", $tokenData['deviceToken'], $array);
                        if ($pushNotification) {
                            $success = array("status" => "true", "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => "false", "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }

                    } elseif ($tokenData['deviceOS'] == "ios") {

                        $pushNotification = $classApi->pushNotificationIOS("تم عمل طلب جدبد","تم عمل طلب جدبد", "AcceptOrderForResturant", $tokenData['deviceToken'], $array);
                        if ($pushNotification) {
                            $success = array("status" => "true", "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => "false", "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }
                    } else {
                        $success = array("status" => "true", "message" => "operation done successfully");
                        $return = (object)$success;
                        print_r(json_encode($return));
                    }


                } else {

                    $classApi->cancelOrder($_POST['orderID'], 'delivery');
                    $array = array('orderId' => $_POST['orderID'], 'orderuserName' => $orderOwnerName['name']);

                    if ($tokenData['deviceOS'] == "Android") {

                        $pushNotification = $classApi->pushNotification("تم الغاء الطلب من قبل الموصل", "RefuseOrderForResturant", $tokenData['deviceToken'], $array);
                        if ($pushNotification) {
                            $success = array("status" => "true", "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => "false", "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }

                    } elseif ($tokenData['deviceOS'] == "ios") {
                        $pushNotification = $classApi->pushNotificationIOS("تم الغاء الطلب من قبل الموصل","تم الغاء الطلب من قبل الموصل", "RefuseOrderForResturant", $tokenData['deviceToken'], $array);
                        if ($pushNotification) {
                            $success = array("status" => "true", "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => "false", "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }
                    } else {
                        $success = array("status" => "true", "message" => "operation done successfully");
                        $return = (object)$success;
                        print_r(json_encode($return));
                    }


                    $success = array("status" => "true", "message" => "operation done successfully");
                    $return = (object)$success;
                    print_r(json_encode($return));

                }


            } else {
                if (isset($_POST['deliveryTime'])) {


                    if ($_POST['Acceptance'] == '1') {
                        $classApi->changeOrderStatus($_POST['orderID'], '4');
                        $classApi->setDeliveryTime($_POST['orderID'], $_POST['deliveryTime']);
                        $success = array("status" => "true", "message" => "operation done successfully");
                        $return = (object)$success;
                        print_r(json_encode($return));


                    } else {

                        $classApi->cancelOrder($_POST['orderID'], 'resturant');
                        $success = array("status" => "true", "message" => "operation done successfully");
                        $return = (object)$success;
                        print_r(json_encode($return));

                    }
                } else {
                    echo miss_params;
                }


            }


        } else {
            echo miss_params;
        }

    }

    //-----------------------------chating function ---------------------------------------//
    public function SendMsgChat()
    {

        global $classApi;
        global $success;

        if (isset($_POST['senderId']) && isset($_POST['message']) && isset($_POST['receiverId'])) {

            if ($classApi->getUserData($_POST['senderId']) && $classApi->getUserData($_POST['receiverId'])) {
                if (isset($_FILES['chatPhoto'])) {
                    $chatFile = $classApi->UploadFile($_FILES['chatPhoto']);
                } else {
                    $chatFile = NULL;
                }

                $sendMsg = $classApi->SendMessage($_POST['senderId'], $_POST['receiverId'], $_POST['message'], $chatFile);
                if ($sendMsg) {
                    $array = array('time' => date('Y-m-d H:i:s'), 'senderId' => $_POST['senderId'], 'receiverId' => $_POST['receiverId'], 'chatPhoto'=> $chatFile);
                    $arrayIOS = array('time' => date('Y-m-d H:i:s'), 'MessageTime'=> 1000 * date('Y-m-d H:i:s'),'senderId' => $_POST['senderId'],'chatPhoto'=> $chatFile, 'receiverId' => $_POST['receiverId']);
                    $receiverData = $classApi->getUserData($_POST['receiverId']);
                    $senderData=$classApi->getUserData($_POST['senderId']);
                    if ($receiverData['deviceOS'] == "Android") {
                        $pushNotification = $classApi->pushNotification($_POST['message'], "chat", $receiverData['deviceToken'], $array);
                        if ($pushNotification) {
                            $success = array("status" => true, "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => false, "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }

                    } elseif ($receiverData['deviceOS'] == "ios") {

                        $pushNotification = $classApi->pushNotificationIOS('رساله من '.$senderData['name'],$_POST['message'], "chat", $receiverData['deviceToken'], $arrayIOS);
                        if ($pushNotification) {
                            $success = array("status" => true, "message" => "operation done successfully");
                            $return = (object)$success;
                            print_r(json_encode($return));
                        } else {
                            $failed = array("status" => false, "message" => "operation Failed");
                            $return = (object)$failed;
                            print_r(json_encode($return));
                        }

                    } else {
                        $success = array("status" => true, "message" => "operation done successfully");
                        $return = (object)$success;
                        print_r(json_encode($return));

                    }


                } else {
                    $failed = array("status" => false, "message" => "operation Failed");
                    $return = (object)$failed;
                    print_r(json_encode($return));

                }


            } else {
                $failed = array("status" => false, "message" => "User Not Found");
                $return = (object)$failed;
                print_r(json_encode($return));
            }

        } else {

            echo miss_params;

        }

    }
    //------------------------------------Get TimeLine For User ------------------------------//

    /*public function getTimelineList()
    {
        global $classApi;
        global $success;



    }*/

    //-------------------------------end of my file -----------------------------//


}

$Api = new Api();

////////////////////////////////////////////      MPZ V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                          MPZ is MINA Version Of CMS                                            */
/*                              All rightes reserved to MPZ ModernPharaohs LTD                                    */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////