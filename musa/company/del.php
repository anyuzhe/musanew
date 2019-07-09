<?php
require_once(__DIR__ . '/../config.php');

$redirecturl = '/company';
$id = $_GET['id'];
$DB->delete_records_select('company', "id=$id");

redirect($redirecturl);