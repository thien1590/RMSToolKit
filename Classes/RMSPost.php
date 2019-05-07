<?php


class RMSPost
{
    protected $api = "https://api.rms.com.vn/";
    private $token;
    private $domain_name;

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
    function setDomainName($domain_name){
        $this->domain_name = $domain_name;
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
                $response['token'] = trim(explode(':',$login['header'][3])[1]);
                $this->token = $response['token'];
                $response['domain_name'] = $this->getSubsDomainName();
            }else
                $response['error'] = $login['status'];
        } else
            echo 'Login fail.'.PHP_EOL;

        return $response;
    }

    function getSubsDomainName(){
        $body = '{"sort_name":"createdAt","is_sort_asc":false,"criteria":{},"custom_criteria":{}}';
        $body = json_decode($body);
        $url = 'v1/subscribers/search';

        $search = $this->post($body,$url);
        $subs_info = json_decode($search['response'])->data->list[0];
        return $subs_info->domain_name;
    }

    public function createAffiliate($aff){
        if(!is_array($aff))
            return false;

        $affiliate = array(
            'fe_url' => 'RMSToolKit - by RMS team',
            'subscriber_domain_name' => $this->domain_name,
            'password' => '12345678',
            'confirmed_password' => '12345678',
            'email' => $aff['email'],
            'confirmed_email' => $aff['email'],
            'phone' => '0'.$aff['phone'],
            'nickname' => $aff['nickname'],
            'referrer_name' => $aff['referrer'],
            'referrer' => $aff['referrer'],
        );
        $name = explode(' ',$aff['name']);
        $affiliate['first_name'] = $name[count($name)-1];
        $affiliate['first_name'] = $affiliate['first_name']?$affiliate['first_name']:$aff['name'];
        unset($name[count($name)-1]);
        $affiliate['last_name'] = implode(' ', $name);
        $affiliate['last_name'] = $affiliate['last_name']?$affiliate['last_name']:$affiliate['first_name'];

        return $this->post($affiliate,'v1/affiliates/sign_up');
    }

    public function importAffiliates($affiliates){
        $log = [];
        foreach ($affiliates as $aff){
            echo 'Import ['.$aff['nickname'].']'.PHP_EOL;
            $item = json_encode($this->createAffiliate($aff));
            $log[] = $item;
        }
        return $log;
    }

    public function createCustomer($cus, $channel, $number){
        if(!is_array($cus))
            return false;

        $order = array(
            "order_lines" => array(
                array(
                    "product" => array(
                        "code" => "IMPORTCUSTOMER",
                        "name" => "Import dữ lệu khách hàng",
                        "price" => 1000
                    ),
                    "price"=> 1000,
                    "commission" => 0,
                    "quantity" => 1
                ),
            ),
            "channel_domain_name" =>$channel,
            "number" => $number,
            "note" => $cus['note'],
            "nickname" => $cus['referrer'],
            "customer" => array(
                "fullname" => $cus['name'],
                "email"    => $cus['email'],
                "address"  => $cus['address'],
                "phone"    => $cus['phone']
            )
        );
        return $this->post($order,'v1/orders');
    }

    public function importCustomers($customers,$channel){
        $log = [];
        foreach ($customers as $key => $cus){
            echo 'Import ['.$cus['name'].']'.PHP_EOL;
            $log[] = json_encode($this->createCustomer($cus,$channel,'ODIMPORT'.$key));
        }
        return $log;
    }

    public function post($data,$url, $method=false, $header = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method?$method:"POST",
            CURLOPT_POSTFIELDS => json_encode($data,JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array_merge(
                array(
                    "content-type: application/json",
                    "x-security-token: ".($this->token?$this->token:"undefined")
                ),
                $header
            ),
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