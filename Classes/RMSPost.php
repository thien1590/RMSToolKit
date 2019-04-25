<?php


class RMSPost
{
    protected $api = "http://stage.app.rms.com.vn:9090/";
    private $token;

    function __construct($token, $api=null)
    {
        $this->token = $token;
        $this->api = $api?$api:$this->api;
    }

    function setToken($token){
        $this->token = $token;
    }
    function setAPI($api){
        $this->api = $api;
    }

    public function login($username,$password){
        $login = $this->post(null,'login',"POST", [
            'username: '.$username,
            'password: '.$password
        ]);

        $response = [
            'success' => false,
//            'raw' => $login
        ];
        if($login['success']){
            if ($login['status']==200){
                $response['success'] = true;
                $response['token'] = trim(explode(':',$login['header'][2])[1]);
            }else
                $response['error'] = $login['status'];
        }

        return $response;
    }

    public function createAffiliate($affiliate){

    }

    public function post($data,$url, $method=false, $header = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method?$method:"POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge( array(
                "content-type: application/json",
                "x-security-token: ".($this->token?$this->token:"undefined")
            ),$header),
            CURLOPT_HEADER => 1
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return array(
                'success' => false,
                'error' => $err
            );
        }
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $headers = explode("\r\n", $headers);
        $body = substr($response, $header_size);

        curl_close($curl);

        return array(
            'success' => true,
            'status' => $code,
            'header' => $headers,
            'response' => $body
        );
    }
}