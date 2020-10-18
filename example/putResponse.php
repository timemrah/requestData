<?php
//Request Data (RD) PUT Example
//Since there is no $_PUT supergobal in PHP, the RD library is very useful.

require '../RD.php';


echo '<pre>' . print_r(RD::PUT(), true) . '</pre>';


echo '<hr />';


$_PUT = RD::PUT();
echo $_PUT['form-name'] . '<br />';
echo $_PUT['name']['first'] . '<br />';
echo $_PUT['name']['last'] . '<br />';
echo $_PUT['birth'] . '<br />';
//undefined key example
echo $_PUT['no']; //Notice: Undefined index: birth..


echo '<hr />';


echo RD::PUT('form-name') . '<br />';
echo RD::PUT('name.first') . '<br />';
echo RD::PUT('name.last') . '<br />';
echo RD::PUT('birth') . '<br />';
//undefined key example
echo RD::PUT('no'); //No Notice


echo '<hr />';


echo RD::positivePUT('birth', 1980); //In case of failure, the default value is assigned.