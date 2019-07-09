<?php

require_once(__DIR__ . '/config.php');


// Start setting up the page
$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('our-services');
$PAGE->set_title('产品与服务');
$PAGE->set_heading('产品与服务');
$PAGE->set_url('/services.php');

//引入头文件
echo $OUTPUT->header();

include 'services_html.php';

echo $OUTPUT->footer();