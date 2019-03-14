<?php
/**
 * 倍赛接口类
 * 
 * @copyright www.basicfinder.com
 * @author lihuixu@basicfinder.com
 * @version v1 20190222
 */

namespace basicfinder\basicfinderapi;

use Yii;

class BasicfinderApi
{
    private $apiHost = 'http://devv3.api.basicfinder.com';
    private $appKey = null;
    private $appVersion = null;
    private $username = null;
    private $password = null;
    private $accessToken = null;
    
    //不可删除
    public function __construct()
    {
    }
    
    /**
     * 初始化, 
     * 因继承类原因, 必须带默认值
     * @see \yii\base\BaseObject::init()
     */
    public function init($appKey = '', $appVersion = '', $username = '', $password = '')
    {
        $this->appKey = $appKey;
        $this->appVersion = $appVersion;
        $this->username = $username;
        $this->password = $password;
    }
    
    
    public function getAccessToken($refresh = false)
    {
        $_logs = ['$refresh' => $refresh];
        
        if (!$refresh && $this->accessToken)
        {
            return $this->format($this->accessToken);
        }
        
        $url = $this->apiHost.'/site/login';
        $data = [
            'app_key' => $this->appKey,
            'app_version' => $this->appVersion,
            'username' => $this->username,
            'password' => $this->password,
            'device_name' => 'api',
            'device_number' => '123'
        ];
        $_logs['$url'] = $url;
        $_logs['$data'] = $data;
        
        $response = $this->request($url, $data, 'post');
        $_logs['$response'] = $response;
        Yii::info(__CLASS__.' '.__FUNCTION__.' '.__LINE__.' accesstoken response '.json_encode($_logs));
        if (!empty($response['error']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $response['error'], $response['message']);
        }
        $result = $response['data'];
        if (!empty($result['error']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $result['error'], $result['message']);
        }
        if (empty($result['data']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $result['error'], $result['message']);
        }
        if (empty($result['data']['id']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $result['error'], $result['message']);
        }
        if (empty($result['data']['access_token']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $result['error'], $result['message']);
        }
        $this->accessToken = $result['data']['access_token'];
        
        return $this->format($this->accessToken);
    }
    
    public function projects($page = 1, $count = 10, $keyword = '', $project_id = '')
    {
        $_logs = ['$page' => $page, '$count' => $count, '$keyword' => $keyword];
        
        $url = $this->apiHost.'/project/projects';
        $data = [
            'page' => $page,
            'limit' => $count,
            'keyword' => $keyword,
            'project_id' => $project_id
        ];
        $_logs['$url'] = $url;
        $_logs['$data'] = $data;
        
        $response = $this->request_with_accesstoken($url, $data, 'post');
        $_logs['$response'] = $response;
        Yii::info(__CLASS__.' '.__FUNCTION__.' '.__LINE__.' Basicfinder response '.json_encode($_logs));
        if (!empty($response['error']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $response['error'], $response['message']);
        }
        $result = $response['data'];
        if (!empty($result['error']))
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode($_logs));
            return $this->format('', $result['error'], $result['message']);
        }
        
        return $this->format($result['data']);
    }
    
    private function format($data = array(), $errno = 0, $error = '')
    {
        return array('data' => $data, 'error' => $errno, 'message' => $error);
    }
    
    protected function request_with_accesstoken($url, $data = null, $method = 'get')
    {
        $loginResult = $this->getAccessToken();
        if ($loginResult['error'])
        {
            Yii::error(__CLASS__.' '.__FUNCTION__.' getAccessToken error ');
            return $this->format('', $loginResult['error'], $loginResult['message']);
        }
        
        if (is_array($data))
        {
            $data['access_token'] = $this->accessToken;
        }
        else
        {
            $url = $url . (strpos($url, '?') ? '&' : '?') . 'access_token='.$this->accessToken;
        }
    
        $result = $this->request($url, $data, $method);
    
//         if (!empty($result['error']))
//         {
//             Yii::error(__CLASS__.' '.__FUNCTION__.' request error ' . json_encode(array($url_, $result)));
    
//             //若是认证错误, 则更新AccessToken, 再次执行
//             if ($result['error'] == 'invalid credential' || strpos($result['errmsg'], 'access_token'))
//             {
//                 Yii::error(__CLASS__.' '.__FUNCTION__.' getAccessToken error ' . serialize(array($result, $url_, $data, $method)));
//                 $accessToken = $this->getAccessToken(true);
//                 $url_ = $url . (strpos($url, '?') ? '&' : '?') . 'access_token='.$accessToken;
    
//                 $result = $this->request($url_, $data, $method);
//                 Yii::error(__CLASS__.' '.__FUNCTION__.' getAccessToken : $refresh  ' . serialize(array($result, $url_, $data, $method)));
//             }
//         }
    
        return $result;
    }
    
    private function request($url,$params=array(),$requestMethod='GET',$headers=array())
    {
        $_logs = ['$url' => $url, '$params' => $params, '$requestMethod' => $requestMethod, '$headers' => $headers];
    
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_USERAGENT, '1001 Magazine v1');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ci, CURLOPT_TIMEOUT, 5);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
    
        $requestMethod = strtoupper($requestMethod);
        switch ($requestMethod) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if ($params) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                }
                else {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, ''); // Don't know why: if not set,  413 Request Entity Too Large
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($params) {
                    $url = "{$url}?{$params}";
                }
                break;
            case 'GET':
                if($params) {
                    $sep = false === strpos($url,'?') ? '?' : '&';
                    $url .= $sep . http_build_query($params);
                }
                break;
            case 'PUT':
                if($params) {
                    curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                }
                break;
        }
        //$headers[] = "APIWWW: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
    
        $response = curl_exec($ci);
        $httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $httpTime = curl_getinfo($ci, CURLINFO_TOTAL_TIME);
        curl_close ($ci);
        
        if ($response && json_decode($response))
        {
            $response = json_decode($response, true);
        }
    
        $return = array(
            'time' => $httpTime,
            'error' => $httpCode == 200 ? 0 : $httpCode,
            'data' => $response,
            'message' => ''
        );
        //$httpInfo = curl_getinfo($ci);
        $_logs['$httpCode'] = $httpCode;
        //Yii::info(__CLASS__.' '.__FUNCTION__.' '.__LINE__.' succ '.json_encode($_logs));
        return $return;
    }
}