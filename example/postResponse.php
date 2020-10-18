<?php
//Request Data (RD) GET Example
//RD library is almost unnecessary for the GET method. It only offers some minor conveniences.

require '../RD.php';


echo '<pre>' . print_r($_POST, true) . '</pre>';


echo '<hr />';


echo $_POST['form-name'] . '<br />';
echo $_POST['name']['first'] . '<br />';
echo $_POST['name']['last'] . '<br />';
echo $_POST['birth'] . '<br />';
//undefined key example
echo $_POST['no']; //Notice: Undefined index: birth..


echo '<hr />';


echo RD::POST('form-name') . '<br />';
echo RD::POST('name.first') . '<br />';
echo RD::POST('name.last') . '<br />';
echo RD::POST('birth') . '<br />';
//undefined key example
echo RD::POST('no'); //No Notice


echo '<hr />';


echo RD::positivePOST('birth', 1980); //In case of failure, the default value is assigned.