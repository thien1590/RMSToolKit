<?php
require_once "Classes/RMSPost.php";
require_once "Classes/Read.php";
$argv = $GLOBALS['argv'];

$function = null;
if(is_array($argv)&&count($argv)>1)
    $function = $argv[1];

switch ($function){
    case 'login':{
        if(count($argv)<3) {
            echo 'pls input username and password';
            die();
        }

        $username = $argv[2];
        $password = $argv[3];
        $rms = new RMSPost(null);
        $login = $rms->login($username,$password);
        if($login['success']){
            echo 'Login success'.PHP_EOL;
            $file_config = fopen("config", "w");
            echo 'Token: '.$login['token'].PHP_EOL;
            fwrite($file_config, "TOKEN=".$login['token'].PHP_EOL."DOMAIN_NAME=".$login['domain_name']);
            fclose($file_config);
            echo 'Created .env and save token'.PHP_EOL;
        }
        echo 'Next step: Import [affiliates/customers] [file path]'.PHP_EOL;
        echo 'NOTE: Import aff need subs admin account, but customer need only channel account'.PHP_EOL;
    } break;

    case 'import':{
        $who = $argv[2];
        $where = $argv[3];
        $file_config = file("config");
        $config = [];
        foreach ($file_config as $line) {
            $temp = explode('=',$line);
            $config[$temp[0]] = trim($temp[1]);
        }
        $rms = new RMSPost($config['TOKEN']);
        $rms->setDomainName($config['DOMAIN_NAME']);

        $path = realpath($where);
        $excel = new Read($path);
        $data = $excel->getData();

        switch ($who){
            case 'affiliates':
            case 'aff':{
                $log = $rms->importAffiliates($data);
                $file_config = fopen("Import-Affiliate-".date('Y-m-d-H-i-s').".log", "w");
                fwrite($file_config, implode(PHP_EOL,$log));
                fclose($file_config);
                echo 'Import affiliates completed!'.PHP_EOL;
            } break;
            case 'customers':
            case 'cus':{
                $channel = $argv[4];
                $log = $rms->importCustomers($data,$channel);
                $file_config = fopen("Import-Customers-".date('Y-m-d-H-i-s').".log", "w");
                fwrite($file_config, implode(PHP_EOL,$log));
                fclose($file_config);
                echo 'Import customers completed!'.PHP_EOL;
            } break;
            default:{
                echo 'just only support affiliates or customers';
            }
        }
    }

}