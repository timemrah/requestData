<?php
//Request Data GET Example
//RD library is almost unnecessary for the GET method. It only offers some minor conveniences.

require '../RD.php';


echo '<pre>' . print_r($_GET, true) . '</pre>';


echo '<hr />';


echo $_GET['form-name'] . '<br />';
echo $_GET['name']['first'] . '<br />';
echo $_GET['name']['last'] . '<br />';
echo $_GET['birth'] . '<br />';
//undefined key example
echo $_GET['no']; //Notice: Undefined index: birth..


echo '<hr />';


echo RD::GET('form-name') . '<br />';
echo RD::GET('name.first') . '<br />';
echo RD::GET('name.last') . '<br />';
echo RD::GET('birth') . '<br />';
//undefined key example
echo RD::GET('no'); //No Notice


echo '<hr />';


echo RD::positiveGET('birth', 1980); //In case of failure, the default value is assigned.