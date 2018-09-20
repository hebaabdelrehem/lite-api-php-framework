<?php
////////////////////////////////////////////      MinaAPI V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                                                                                            */
/*                                                                                                                */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Includers
include '../includes/minaapi.class.php';
include 'api.class.php';
//include 'coutnries.php';


$init = dirname(__FILE__);

//Definations

$success = array("status"=>"true" , "message"=>"operation done successfully");
$badtime = array("status"=>"false" , "message"=>"expierTime");

//define('success', '{"status": "true" , "message":"operation done successfully"}');

//define('success', '{"status": "true" , "message":"operation done successfully"}');
define('failed', '{"status": false , "message":"fail to do operation" }');
define('miss_params', '{"status": false , "message":"MISSING PARAMETERS" }');
define('empty_user','{"id":"","name":"","email":"","password":"","mobile":"","type_id":"0","image":""}');

////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////
/*                                                                                                                */
/*                                           This is not a free script                                            */
/*                              All rightes reserved to traxeed.net and traxeed.com                               */
/*                                                                                                                */
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
