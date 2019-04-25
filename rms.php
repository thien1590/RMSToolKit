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
            fwrite($file_config, "TOKEN=".$login['token']);
            fclose($file_config);
            echo 'Created .env and save token'.PHP_EOL;
        }
        echo 'Next step: import [affiliates/customers] [file path]';
    } break;

    case 'import':{
        $who = $argv[2];
        $where = $argv[3];
        $file_config = file("config");
        $config = [];
        foreach ($file_config as $line) {
            $temp = explode('=',$line);
            $config[$temp[0]] = $temp[1];
        }
        switch ($who){
            case 'affiliates':
            case 'aff':{
                $rms = new RMSPost($config['TOKEN']);
                $path = realpath($where);
                $excel = new Read($path);
                $data = $excel->getData();

            } break;
            case 'customers':
            case 'cus':{
                echo 'test';
            } break;
            default:{
                echo 'just only support affiliates or customers';
            }
        }
    }

    default: {
        echo 'stupid';
    }
}