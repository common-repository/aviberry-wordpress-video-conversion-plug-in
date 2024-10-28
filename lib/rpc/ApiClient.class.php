<?php

/*
	Copyright 2011 Aviberry (email : support@aviberry.com)
*/

/**
 * Данный файл является svn:externals для:
 * - farm
 * - wordpress
 * Оригинал находится на front, изменять только на front
 */

// Назначение этого файла: 
// 		обеспечивание внутренних вызовов АПИ между серверами;
// 		трансляция исключительных ситуаций АПИ между серверами;
//		собственные генерируемые исключения должны удовлетворять архитерктуре искоючений АПИ.
//


require_once 'xmlrpc.inc';


/**
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 */
class ApiClient {
	

	const PROTOCOL_XML = 'xml';
	const PROTOCOL_JSON = 'json';

	/**
	 * Заголовок contentType
	 * 
	 * @var string
	 */
	private $_contentType;

	/**
	 * Protocol XML or JSON
	 * 
	 * @var string
	 */
	private $_protocol;
	
	/**
	 * Logger.
	 * 
	 * @var Members_Logger
	 */
	private $logger = null;
	 
	/**
	 * Количество попыток вызвать curl
	 *
	 * @var integer
	 */
	public $connectRetries;

	/**
	 * Debug state
	 *
	 * @var integer
	 */
	public $connectTimeout; 

	/**
	 * Debug state
	 *
	 * @var boolean
	 */
	public $debug;

	/**
	 * The server URL
	 *
	 * @var string
	 */
	private $url;
	/**
	 * The request id
	 *
	 * @var integer
	 */
	private $id;
	/**
	 * If true, notifications are performed instead of requests
	 *
	 * @var boolean
	 */
	private $notification = false;
	
	/**
	 * Takes the connection parameters
	 *
	 * @param string $url
	 * @param boolean $debug
	 * @param string $protocol ApiClient::PROTOCOL_JSON|ApiClient::PROTOCOL_XML
	 * @param integer $connectTimeout Connection attempt timeout. Seconds
	 * @param integer $connectRetries Maximum number of connection attempts
	 */
	public function __construct(
		$url, 
		$debug = false, 
		$protocol = false, 
		$connectTimeout = false, 
		$connectRetries = false
	){
		// server URL
		if(empty($url))
			throw new ApiException('Please specify the URL of the RPC server.');
		$this->url = $url;
		
		// debug state
		$this->debug = (boolean)$debug;
		$this->debug && ($this->logger = Members_Logger::getLog(__CLASS__));
		
		// check protocol
		if(	$protocol && 
			!in_array(
				$protocol, 
				array(
					self::PROTOCOL_JSON,
					self::PROTOCOL_XML
				)
			)
		)
			throw new InternalError_ApiException(false, false, sprintf('Sorry, the "%s" RPC type is not supported.', $protocol));
		
		// set protocol
		if($protocol)
			$this->_protocol = $protocol;
		else
			if(strpos($this->url, '/xml/') !== false){
				$this->_protocol = self::PROTOCOL_XML;
			}else{
				$this->_protocol = self::PROTOCOL_JSON;
			}
		
		// set connectTimeout
		if($connectTimeout !== false)
			$this->connectTimeout = $connectTimeout;
		elseif(class_exists('Config') && isset(Config::$api_client_connecttimeout))
			$this->connectTimeout = Config::$api_client_connecttimeout;
		else
			throw new ApiException('Please specify the connection attempt timeout.');
		
		// set connectRetries
		if($connectRetries !== false)
			$this->connectRetries = $connectRetries;
		elseif(class_exists('Config') && isset(Config::$api_client_maxretries))
			$this->connectRetries = Config::$api_client_maxretries;
		else
			throw new ApiException('Please specify the maximum number of connection attempts.');
			
		// proxy
		$this->proxy = empty($proxy) ? '' : $proxy;
		
		// message id
		$this->id = 1;
	}


	/**
	 * Для JSON-протокола подготавливает данные запроса в формате JSON
	 * @param string $method
	 * @param array $params
	 * @return string
	 */
	private function _getRequestJSON($method, $params){
		
		$request = array(
			'version' => '1.1',
			'method'  => $method,
			'id'      => $this->id,
			'params'  => $params
		);
		$this->_contentType = 'application/json';
		return json_encode($request);
	}

	/**
	 * Для XML-протокола подготавливает данные запроса в формате XML
	 * Используются SimpleXML и xmlrpc libs
	 * 
	 * @param string $method
	 * @param array $params
	 * @return string xml
	 */
	private function _getRequestXML($method, $params){

		$paramsXML = '';
		foreach((array)$params as $param){
			$val = php_xmlrpc_encode($param, array('auto_dates'));
			$paramsXML .= '<param>'.$val->serialize().'</param>';
		}
			
		$xmlObj = simplexml_load_string('<methodCall><methodName>'.$method.'</methodName><params>'.$paramsXML.'</params></methodCall>');
		$xml = $xmlObj->asXML();

		$this->_contentType = 'text/xml';
		return $xml;
	}

	/**
	 * В зависимости от протокола выдает строку запроса
	 * 
	 * @param string $method
	 * @param array $params
	 * @return string 
	 */
	public function getRequest($method, $params){

		switch($this->_protocol){
			case self::PROTOCOL_JSON:
				$request = $this->_getRequestJSON($method, $params);
				break;
			case self::PROTOCOL_XML:
				$request = $this->_getRequestXML($method, $params);
				break;
		}
		
		return $request;
	}



	/**
	 * Преобразует json cтроку в массив
	 * @param string $response
	 * @return array 
	 */
	private function _getResponseJSON($response){
		return json_decode($response, true);
	}
	

	
	/**
	 * Обходит рекурсивно значения xmlrpcval-объекта и формирует php-массив 
	 * значений этого объекта
	 * 
	 * @param xmlrpcval $value
	 * @return array 
	 */
	function scalarVal(xmlrpcval $value){

		if(get_class((object)$value) != 'xmlrpcval')
			return false;
		
		switch($value->kindOf()){
			case 'struct':
				$value->structreset();
				$res = array();
				while(list($key,$val)=$value->structeach()){
					$res[$key] = $this->scalarVal($val);
				}
				return $res;

			case 'array':
				reset($value->me);
				$res = array();
				while(list($key,$val)=each($value->me['array'])){
					$res[$key] = $this->scalarVal($val);
				}
				return $res;

			case 'scalar':
				return $value->scalarval();
		}
		
	}

	/**
	 * Преобразует XML в php type
	 * @param string $response
	 * @return array 
	 */
	private function _getResponseXML($response){
		$xmlrpc = php_xmlrpc_decode_xml($response);

		if(get_class((object)$xmlrpc->value()) == 'xmlrpcval' && !$xmlrpc->errno){
			$result['result'] = $this->scalarVal($xmlrpc->value());
			$result['id'] = $this->id;
		}else{
			$result['error']['code'] = $xmlrpc->errno;
			$result['error']['message'] = $xmlrpc->errstr;
			$result['error']['class'] = $xmlrpc->errclass;
		}
		return $result;
	}
	
	/**
	 * В зависимости от протокола преобразует строку ответа в массив
	 * 
	 * @param string $response
	 * @return array
	 */
	public function getResponse($response){

		switch($this->_protocol){
			case self::PROTOCOL_JSON:
				$response = $this->_getResponseJSON($response);
				break;
			case self::PROTOCOL_XML:
				$response = $this->_getResponseXML($response);
				break;
		}
		return $response;
	}
	
	
	/**
	 * Performs a jsonRCP request and gets the results as an array
	 *
	 * @param string $method
	 * @param array $params
	 * @return array
	 */
	public function __call($method, $params) {
		$this->debug && $this->logger->logDebug(__METHOD__ . ": Step Into:\nmethod=%s,\nparams=%s", $method, $params);
		
		if(!is_array($params)){
			throw new InternalError_ApiException(false, false, 'API Client: params must be an array.');
		}
		
		$time["1 start call"] = microtime(true);

		if(count($params))
			$params = $params[0];
		
		// check
		if (!is_scalar($method))
			throw new InternalError_ApiException(false, false, 'API Client: Method name has no scalar value.');

		$currentId = $this->id;

		$this->debug && $this->logger->logDebug(__METHOD__ . ': url: "%s"', $this->url);

		// берем содержание запроса в зависимости от протокола
		$request = $this->getRequest($method, $params);
		$this->debug && $this->logger->logDebug(__METHOD__ . ": request:\n%s", $request);

		$time["before curl"] = microtime(true);
		// performs the HTTP POST
		$attempt = 0;
		$r = "";
		while (true) {
			$attempt++;

			$curl = curl_init($this->url);
				curl_setopt_array($curl, array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $request,
				CURLOPT_VERBOSE => (int)$this->debug,
				CURLOPT_CONNECTTIMEOUT => $this->connectTimeout, //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
				CURLOPT_HTTPHEADER => array('Content-type: '.$this->_contentType)
			));

			$r = curl_exec($curl);
			if ($error = curl_error($curl)) {
				if (!self::_exponentialBackoff($attempt, $this->connectRetries))
					throw new ProtocolError_ApiException(false, false, 'API Client: ' . curl_error($curl));
					
				continue;
			}
			break;
		}
		$time["after curl"] = microtime(true);

		$this->debug && $this->logger->logDebug(__METHOD__ . ": response:\n%s", $r);

		// в зависимости от протокола получаем ответ в виде ассоциативного массива
		$response = $this->getResponse(trim($r));	

		$this->debug && $this->logger->logDebug(__METHOD__ . ": $time: %s", print_r($time, true));
		
		// Если получаем ошибку, то возбуждаем исключение на клиентской стороне.		
		if (isset($response['error'])){
			$exceptionClass = 'ApiException';
			//по возможности то же самое типизированное
			if(!empty($response['error']['class']) && class_exists($response['error']['class']) )
				$exceptionClass = $response['error']['class'];
			
			throw new $exceptionClass($response['error']['message'], $response['error']['code']);
		}

		// Далее идут системные ошибки, которые никогда не возникнут, если
		// все идет по плану.
		 
		if ($response['id'] != $currentId) {
			throw new ProtocolError_ApiException(false, false, 'API Client: Incorrect response id (' 
				.   'request id: ' . $currentId 
				. ', response id: ' . $response['id']
				. ', method response: ' . print_r($response, true)
				. ', protocol response: ' . print_r($r, true)
			. ')');
		}

		if (!isset($response['result']))
			throw new ProtocolError_ApiException(false, false, 'API Client: Request error.');
		
		$this->debug && $this->logger->logDebug(__METHOD__ . ": Step Out: %s", $response['result']);
		return $response['result'];
	}
	
	protected static function _exponentialBackoff($current, $max) {
		if ($current <= $max) {
			$delay = (int)(pow(2, $current) * 1000000);
			usleep($delay);
			return true;
		}
		return false;
	}
}
?>
