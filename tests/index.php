<?php

include __DIR__ . '/../vendor/autoload.php';

echo Cmsgoon\tools\Util::getMonthInWords(3);

$arrParams = array();

$arrParams['lg'] = 134;
$arrParams['itens'][] = array("email" => "rodrigower@gmail.com", "codgrupo" => 1000);

$arr = Cmsgoon\tools\Util::loadJson('http://localhost/ProjetosZend/sequenceweb/services/set-newsletters.php', $arrParams);

print_r($arr);
