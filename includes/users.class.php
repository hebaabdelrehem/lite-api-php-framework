<?php

////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////

/*                                                                                                                */

/*                                           This is not a free script                                            */

/*                              All rightes reserved to traxeed.net and traxeed.com                               */

/*                                                                                                                */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*

 * Users class contains all functions that belong to user login and logout and editing profile data and security data

 * Edit Profile

 * Change email

 * Change password

 * login & Logout

 * Check brute force

 * Login check

 */



class users extends traxeed {

    public function __constract() {
        
    }

    //---------------------------- User Data function - Return an array ----------------------------//



    public function get_userdata($id = "") {

        global $traxeed_mysqliDB;

        $query = "SELECT * FROM `users` WHERE id = ?";

        $params = array($id);

        $userdata = $traxeed_mysqliDB->selectone($query, 's', $params);

        return $userdata;
    }

    //---------------------------- Edit profile ----------------------------//



    public function edit_profile($name = '', $user = '', $dname = '', $country = '', $city = '', $sex = '', $bio = '', $face = '', $twitter = '', $google = '', $skype = '', $linkedin = '', $yahoo = '', $filename = '', $website = '') {

        global $traxeed_mysqliDB;
        global $userid;



        // Disabled Usernames -------

        if ($this->disabled_names($user)) {
            exit('<div id=\'res\'>notvalidusername</div>');
        }



        // Unique Username -------

        $uniqueusername = $this->getset('uniqueusername');

        if ($uniqueusername == "1") {

            if ($this->unique_username($user, $userid)) {
                exit('<div id=\'res\'>useralreadyexist</div>');
            }
        }



        // User avator

        if ($filename == " ") {

            $userdata = $this->get_userdata($userid);

            $filename = $userdata['avatar'];
        }

        // Update user profile -------

        $q = "UPDATE users SET `name` = ?, `user` = ?, `dname` = ?, `country` = ?, `city` = ?, `sex` = ?, `bio` = ?, 

											`face` = ?, `twitter` = ?, `google` = ?, `skype` = ?, `linkedin` = ?, `yahoo` = ?, `avatar`= ?,

											 `website` = ? WHERE `id` = ? ";



        $values = array($name, $user, $dname, $country, $city, $sex, $bio, $face, $twitter, $google, $skype, $linkedin, $yahoo, $filename, $website, $userid);



        $update_user = $traxeed_mysqliDB->update($q, 'ssssssssssssssss', $values);



        if ($update_user) {
            exit('<div id=\'res\'>success</div>');
        } else {
            exit('<div id=\'res\'>error</div>');
        }
    }

    //---------------------------- Change email  ----------------------------//



    public function change_email($email = '', $cemail = '') {

        global $traxeed_mysqliDB;

        global $userid;



        // Unique Email	-------

        $uniqueemail = $this->getset('uniqueemail');

        if ($uniqueemail == "1") {

            if ($this->unique_email($email, $userid)) {
                exit('<div id=\'res\'>emailalreadyexist</div>');
            }
        }

        if ($email != $cemail) {
            exit('<div id=\'res\'>emaildonotmatch</div>');
        }



        // Email upadting -------

        $q_email = "UPDATE users SET `email` = ? WHERE `id` = ? ";

        $values = array($email, $userid);

        $update_email = $traxeed_mysqliDB->update($q_email, 'ss', $values);



        if ($update_email) {
            exit('<div id=\'res\'>success</div>');
        } else {
            exit('<div id=\'res\'>error</div>');
        }
    }

    //---------------------------- Change password  ----------------------------//



    public function change_pass($password = '', $cpassword = '') {

        global $traxeed_mysqliDB;

        global $userid;



        if (check_password($password)) {

            if (strlen($password) < $this->getset('passlength')) {
                exit('<div id=\'res\'>passlenth</div>');
            }

            if ($password != $cpassword) {
                exit('<div id=\'res\'>passdonotmatch</div>');
            }
        } else {
            exit('<div id=\'res\'>passismissing</div>');
        }



        // Password encrypting -------

        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

        $encrypted_pass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($random_salt), $password, MCRYPT_MODE_CBC, md5(md5($random_salt))));



        // Password upaating -------

        $q_pass = "UPDATE users SET `password` = ?, `salt` = ? WHERE `id` = ? ";

        $values = array($encrypted_pass, $random_salt, $userid);

        $update_pass = $traxeed_mysqliDB->update($q_pass, 'sss', $values);



        if ($update_pass) {
            exit('<div id=\'res\'>success</div>');
        } else {
            exit('<div id=\'res\'>error</div>');
        }
    }

    //---------------------------- Login function ----------------------------//



    public function login($email = '', $password = '' , $usergroup = '') {



        global $traxeed_mysqliDB;

        global $traxeed_Mail;


        if($usergroup='website'){
                    $login_q = "SELECT id, password, salt, group_id FROM users WHERE email = ? OR phone = ? LIMIT 1";
                    $params = array($email,$email);
                    $login_q_execute = $traxeed_mysqliDB->selectone($login_q, 'ss', $params);
                                    }

                    elseif ($usergroup == '2') {
                        $login_q = "SELECT id, password, salt, group_id FROM users WHERE (email = ? OR phone = ?) AND (group_id = '2' OR group_id = '7' )LIMIT 1";
                     $params = array($email,$email);
                     $login_q_execute = $traxeed_mysqliDB->selectone($login_q, 'ss', $params);
                    }
                else{
                     $login_q = "SELECT id, password, salt, group_id FROM users WHERE (email = ? OR phone = ?) AND group_id = ? LIMIT 1";
                     $params = array($email,$email,$usergroup);
                     $login_q_execute = $traxeed_mysqliDB->selectone($login_q, 'sss', $params);
                }


        

        if ($login_q_execute) {



            // Get User info -------



            $userid = $login_q_execute['id'];

            $db_password = $login_q_execute['password'];

            $salt = $login_q_execute['salt'];

            $group_id = $login_q_execute['group_id'];

            //$email = $login_q_execute['email'];



            $db_password_decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($salt), base64_decode($db_password), MCRYPT_MODE_CBC, md5(md5($salt))), "\0");



            // Check Brute force attack -------



            if ($this->getset('cbruteforceen')) {


                if ($this->checkbrute($userid)) {
                    exit('<div id=\'res\'>checkbrute</div>');
                }
            }



            // Check password - build session - change last login -------





            if ($db_password_decrypt == $password) {

                if (!isset($_POST['Token'])) {

                    //echo $_POST['Token'];

                    $user_browser = $_SERVER['HTTP_USER_AGENT'];

                    $user_ip = $_SERVER['REMOTE_ADDR'];

                    $userid = preg_replace("/[^0-9]+/", "", $userid);

                    $_SESSION['userid'] = $userid;

                    $email = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $email);

                    $_SESSION['email'] = $email;

                    $_SESSION['loginstring'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($user_browser), $db_password, MCRYPT_MODE_CBC, md5(md5($user_browser))));



                    //setcookie("loginCredentials", $user, time() * 7200); // Expiring after 2 hours



                    $last_login = date("Y-m-d H:i:s");

                    $login_values = array($last_login, $user_ip, $user_browser, $userid);

                    $updateq = "UPDATE users SET `last_login` = ?, `ip_add` = ?, `user_agent` = ? WHERE `id` = ? ";

                    $update = $traxeed_mysqliDB->update($updateq, 'ssss', $login_values);
                }

                return true;
            } else {

                if ($this->getset('cbruteforceen')) {
                    // Log login failed attempts in database -------



                    $now = time();

                    if ($this->getset('cbruteip')) {

                        $user_agent = $_SERVER['HTTP_USER_AGENT'];

                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {

                        $user_agent = "-";

                        $ip = "-";
                    }

                    $datef = date("Y-m-d H:i:s");

                    $q = "INSERT INTO log_security (userid, time, ip, user_agent, date) VALUES (?, ?, ?, ?, ?)";

                    $values = array($userid, $now, $ip, $user_agent, $datef);

                    $insert = $traxeed_mysqliDB->insert($q, 'sssss', $values);



                    $mailset = $traxeed_Mail->mailset();

                    if ($mailen['mailen']) {



                        $message_content = "<h2>Brute force attack</h2>";

                        $message_content .= "<p>Brute force attack</p>";

                        $mail_subj = "Falied login attemps";



                        if ($this->getset('cbrutemailadmin')) {

                            $mail = $traxeed_Mail->send_mail($this->getset('adminmail'), $mail_subj, $message_content);
                        }

                        if ($this->getset('cbrutemailowner')) {

                            $mail = $traxeed_Mail->send_mail($email, $mail_subj, $message_content);
                        }
                    }

                    return false;
                }
            }
        }
    }

    //--------------------------------login with mobile -------------------------------------------//

    public function loginMobile($mobile = '', $password = '') {



        global $traxeed_mysqliDB;

        global $traxeed_Mail;

    

        $login_q = "SELECT id, password, salt, group_id FROM users WHERE phone = ? LIMIT 1";

        $params = array($phone);

        $login_q_execute = $traxeed_mysqliDB->selectone($login_q, 's', $params);



        if ($login_q_execute) {



            // Get User info -------



            $userid = $login_q_execute['id'];

            $db_password = $login_q_execute['password'];

            $salt = $login_q_execute['salt'];

            $group_id = $login_q_execute['group_id'];

            //$email = $login_q_execute['email'];



            $db_password_decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($salt), base64_decode($db_password), MCRYPT_MODE_CBC, md5(md5($salt))), "\0");



            // Check Brute force attack -------



            if ($this->getset('cbruteforceen')) {


                if ($this->checkbrute($userid)) {
                    exit('<div id=\'res\'>checkbrute</div>');
                }
            }



            // Check password - build session - change last login -------





            if ($db_password_decrypt == $password) {

                if (!isset($_POST['Token'])) {

                    //echo $_POST['Token'];

                    $user_browser = $_SERVER['HTTP_USER_AGENT'];

                    $user_ip = $_SERVER['REMOTE_ADDR'];

                    $userid = preg_replace("/[^0-9]+/", "", $userid);

                    $_SESSION['userid'] = $userid;

                    $email = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $email);

                    $_SESSION['email'] = $email;

                    $_SESSION['loginstring'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($user_browser), $db_password, MCRYPT_MODE_CBC, md5(md5($user_browser))));



                    //setcookie("loginCredentials", $user, time() * 7200); // Expiring after 2 hours



                    $last_login = date("Y-m-d H:i:s");

                    $login_values = array($last_login, $user_ip, $user_browser, $userid);

                    $updateq = "UPDATE users SET `last_login` = ?, `ip_add` = ?, `user_agent` = ? WHERE `id` = ? ";

                    $update = $traxeed_mysqliDB->update($updateq, 'ssss', $login_values);
                }

                return true;
            } else {

                if ($this->getset('cbruteforceen')) {
                    // Log login failed attempts in database -------



                    $now = time();

                    if ($this->getset('cbruteip')) {

                        $user_agent = $_SERVER['HTTP_USER_AGENT'];

                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {

                        $user_agent = "-";

                        $ip = "-";
                    }

                    $datef = date("Y-m-d H:i:s");

                    $q = "INSERT INTO log_security (userid, time, ip, user_agent, date) VALUES (?, ?, ?, ?, ?)";

                    $values = array($userid, $now, $ip, $user_agent, $datef);

                    $insert = $traxeed_mysqliDB->insert($q, 'sssss', $values);



                    $mailset = $traxeed_Mail->mailset();

                    if ($mailen['mailen']) {



                        $message_content = "<h2>Brute force attack</h2>";

                        $message_content .= "<p>Brute force attack</p>";

                        $mail_subj = "Falied login attemps";



                        if ($this->getset('cbrutemailadmin')) {

                            $mail = $traxeed_Mail->send_mail($this->getset('adminmail'), $mail_subj, $message_content);
                        }

                        if ($this->getset('cbrutemailowner')) {

                            $mail = $traxeed_Mail->send_mail($email, $mail_subj, $message_content);
                        }
                    }

                    return false;
                }
            }
        }
    }

    //---------------------------- Check brute force function ----------------------------//



    public function checkbrute($userid) {

        global $traxeed_mysqliDB;



        $attemps_allowed = $this->getset('attemptsn');

        $block_time = $this->getset('attemptsblock');



        $now = time();

        $valid_attempts = $now - ($block_time * 60 * 60);



        $q = "SELECT time FROM log_security WHERE userid = ? AND time > ?";

        $values = array($userid, $valid_attempts);

        $check_brute = $traxeed_mysqliDB->get_count_q($q, 'ss', $values);



        if ($check_brute >= $attemps_allowed) {

            return true;
        } else
            return false;
    }

    //---------------------------- Login check fuction ----------------------------//



    public function login_check() {

        global $traxeed_mysqliDB;

        if (isset($_SESSION['userid'], $_SESSION['email'], $_SESSION['loginstring'])) {



            $userid = $_SESSION['userid'];

            $loginstring = $_SESSION['loginstring'];

            $email = $_SESSION['email'];

            $user_browser = $_SERVER['HTTP_USER_AGENT'];

            $userdatapass = $this->userdata($userid);

            $password = $userdatapass['password'];



            if ($password) {

                $login_check = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($user_browser), $password, MCRYPT_MODE_CBC, md5(md5($user_browser))));



                if ($login_check == $loginstring) {

                    return true;
                } else {

                    return false;
                }
            } else {

                return false;
            }
        } else {

            return false;
        }
    }

    //---------------------------- Logout function ----------------------------//



    public function logout() {

        $_SESSION = array();

        $params = session_get_cookie_params();

        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

        unset($_SESSION['sec_session_user']);



        return TRUE;
    }

    //---------------------------- Forget password send url ----------------------------//



    public function forget_pass_send_url() {
        
    }

    //---------------------------- Forget password put new password ----------------------------//



    public function forget() {
        
    }

    //---------------------------- Disabled usernames ----------------------------//



    public function disabled_names($user) {

        $disabledusernames = $this->getset('disabledusernames');



        if ($disabledusernames) {

            $array = explode(",", $disabledusernames);

            if (in_array($user, $array)) {

                return TRUE;
            }
        }
    }

    //---------------------------- Unique usernames ----------------------------//



    public function unique_username($user = '', $userid = '') {

        global $traxeed_mysqliDB;
        $q = "SELECT id,user FROM users WHERE user = ?";

        $params = array($user);

        $check = $traxeed_mysqliDB->selectone($q, 's', $params);

        if ($check && !$userid) {

            return TRUE;
        } elseif ($check && $check['id'] != $userid) {

            return TRUE;
        }
    }

    //---------------------------- Unique Name ----------------------------//



    public function unique_name($name = '', $userid = '') {

        global $traxeed_mysqliDB;

        $q = "SELECT id,name FROM users WHERE name = ?";

        $params = array($name);

        $check = $traxeed_mysqliDB->selectone($q, 's', $params);

        if ($check && !$userid) {

            return TRUE;
        } elseif ($check && $check['id'] != $userid) {

            return TRUE;
        }
    }

    //---------------------------- Unique email ----------------------------//



    public function unique_email($email = '', $userid = '') {

        global $traxeed_mysqliDB;
        $q = "SELECT id FROM users WHERE email = ? LIMIT 1";

        $params = array($email);

        $check = $traxeed_mysqliDB->selectone($q, 's', $params);

        if ($check && !$userid) {

            return TRUE;
        } elseif ($check && $check['id'] != $userid) {

            return TRUE;
        }
    }


    //----------------------------------unique mobile ----------------------------//

  

}

$users = new users();

////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////

/*                                                                                                                */

/*                                           This is not a free script                                            */

/*                              All rightes reserved to traxeed.net and traxeed.com                               */

/*                                                                                                                */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

