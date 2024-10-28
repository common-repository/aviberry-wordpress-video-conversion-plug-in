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

// Файл содержит определения исключений внешнего АПИ, которые могут выдаваться
// конечному пользователю сервиса. Ничего лишнего здесь быть не должно. Ошибки
// делятся на системные ошибки, или ошибки протокола, (ошибки, на которые мы не 
// можем повлиять) и ошибки сервиса (ошибки, которые подконтрольны нам).
//

/**
 * Родительский класс, его наследуют все классы-исключения в этом файле
 * Умеет прикреплять 3-ий параметр $message_ext к основному сообщению.
 */
class ApiException extends Exception {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(!empty($message_ext))
			$message .= $message_ext;
		parent::__construct($message, $code);
	}
	
	/**
	 * Set message
	 * 
	 * @param string $message
	 */
	public function setMessage($message){
		$this->message = $message;
	}
}


//
// Protocol Exceptions
//
// The error-codes -32768 .. -32000 (inclusive) are reserved for pre-defined errors.
// Any error-code within this range not defined explicitly below is reserved for future use.

/**
 * Invalid JSON. An error occurred on the server while parsing the JSON text.
 */
class ParseError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Parse error. ';
		parent::__construct($message, -32700, $message_ext);
	}
}

/**
 * The received JSON is not a valid JSON-RPC Request.
 */
class InvalidRequest_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Invalid Request. ';
		parent::__construct($message, -32600, $message_ext);
	}
}

/**
 * The requested remote-procedure does not exist / is not available.
 */
class MethodNotFound_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Method not found. ';
		parent::__construct($message, -32601, $message_ext);
	}
}

/**
 * Invalid method parameters. 
 */
class InvalidParams_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Invalid params. ';
		parent::__construct($message, -32602, $message_ext);
	}
}


// -32099..-32000 Server error. Reserved for implementation-defined server-errors.
// User defined errors are unsigned integers.

/**
 * Internal protocol error.
 */
class ProtocolError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Protocol error. ';
		parent::__construct($message, -32000, $message_ext);
	}
}


//
// Application Exceptions
//

/**
 * Нет доступа к API
 */
class UnauthorizedAccess_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Unauthorized access. ';
		parent::__construct($message, 1, $message_ext);
	}
}

/**
 * Неверное значение параметра
 */
class InvalidParamValue_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Invalid param value. ';
		parent::__construct($message, 2, $message_ext);
	}
}

/**
 * Аккаунт заблокирован
 */
class InactiveAccount_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Inactive account. ';
		parent::__construct($message, 3, $message_ext);
	}
}

/**
 * Превышен лимит
 */
class ExceededLimit_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Exceeded limit. ';
		parent::__construct($message, 4, $message_ext);
	}
}

/**
 * Превышен лимит трафика
 */
class ExceededTraffic_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Exceeded traffic. ';
		parent::__construct($message, 5, $message_ext);
	}
}

/**
 * Неверное направление конвертации
 */
class InvalidConversionDirection_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Invalid conversion direction. ';
		parent::__construct($message, 6, $message_ext);
	}
}

/**
 * Файл не существует 
 */
class NotExistentFile_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'File does not exist. ';
		parent::__construct($message, 7, $message_ext);
	}
}

/**
 * Файл уже существует
 */
class FileAlreadyExists_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'File already exists. ';
		parent::__construct($message, 8, $message_ext);
	}
}

/**
 * Требуется авторизация
 */
class AuthorizationRequired_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {		
		if(empty($message))
			$message = 'Authorization required. ';
		parent::__construct($message, 9, $message_ext);
	}
}

/**
 * Неверный логин или пароль
 */
class NotValidUserOrPassword_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Invalid login or password. ';
		parent::__construct($message, 10, $message_ext);
	}
}

/**
 * Ошибка callback
 */
class CallbackError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Callback Error. ';
		parent::__construct($message, 11, $message_ext);
	}
}

/**
 * Ошибка загрузки файла
 */
class DownloadError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Download Error. ';
		parent::__construct($message, 12, $message_ext);
	}
}

/**
 * Ошибка конвертации
 */
class ConverterError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Converter Error. ';
		parent::__construct($message, 13, $message_ext);
	}
}

/**
 * Ошибка выгрузки файла
 */
class UploadError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Upload Error. ';
		parent::__construct($message, 14, $message_ext);
	}
}

/**
 * Неизвестные доп. параметры
 */
class UnknownAdditionalParam_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Unknown additional param. ';
		parent::__construct($message, 15, $message_ext);
	}
}

/**
 * Не определяется размер файла
 */
class UnknownFileSize_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Can\'t determine file size. ';
		parent::__construct($message, 16, $message_ext);
	}
}

/**
 * Превышение max размера файла
 */
class ExceddedMaxFileSize_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'File size exceeds allowed limit. ';
		parent::__construct($message, 17, $message_ext);
	}
}

/**
 * Превышение max размера задачи
 */
class ExceededTaskSizeMax_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Task size exceeds allowed limit. ';
		parent::__construct($message, 18, $message_ext);
	}
}

/**
 * Internal application error.
 */
class InternalError_ApiException extends ApiException {
	function __construct($message = '', $code = 0, $message_ext = '') {
		if(empty($message))
			$message = 'Internal error. ';
		parent::__construct($message, 32000, $message_ext);
	}
}

//
// Далее следуют типизированные исключения внутреннего API members/api/
// 
// Вообще-то, не следовало все, что ниже, помещать в данный файл. Этот файл называется
// ApiException и лежит он в папке внешнего АПИ /api/lib/rpc, что как бы чуть более,
// чем полностью, символизирует, что здесь исключения внешнего АПИ. И не надо сюда 
// пихать все подряд. Исключения внешнего АПИ - это такие исключения, которые увидит 
// конечный пользователь и появление которых определяется причинно-следстивенной связью:
// конкретное неверное действие пользователя -> конкретное исключение из этого файла.
// На этом основании не стоит в этот файл также помещать исключения отражающие чисто "наши проблемы", 
// например, если мы не можем прочитать файл шаблона письма с диска, то это наши проблемы и 
// пользователь в них не виноват. Не надо заводить здесь исключение TemplateLoadError_ApiException.
// В коде просто должно быть возбуждено исключение throw new Exception(), которое 
// автоматически будет преобразовано в InternalError_ApiException. Таким образом пользователь 
// будет знать, что причина не в нем, а внутри системы.
// 
// Все что ниже к внешнему АПИ отношения не имеет и это не должно было бы быть тут.

class LimitExcedded_ApiException extends ApiException {
	function __construct() {parent::__construct('Limit exceeded.', 10001);}
}
class AuthWrong_ApiException extends ApiException {
	function __construct() {parent::__construct('Wrong authentification.', 10002);}
}
class WrongUserPassword_ApiException extends ApiException {
	function __construct() {parent::__construct('Invalid password.', 10003);}
}
class AccountInactive_ApiException extends ApiException {
	function __construct() {parent::__construct('Inactive account.', 10004);}
}
class ConversionDirectionNotValid_ApiException extends ApiException {
	function __construct() {parent::__construct('Convertion direction is not valid.', 10005);}
}
class EmailNotValid_ApiException extends ApiException {
	function __construct() {parent::__construct('Email you entered is not valid.', 10006);}
}
class EmailBusy_ApiException extends ApiException {
	function __construct() {parent::__construct('This email belongs to an existing account.', 10007);}
}
class TrafficExcedded_ApiException extends ApiException {
	function __construct() {parent::__construct('Traffic exceeded.', 10008);}
}
class ApiKeysGenerateError_ApiException extends ApiException {
	function __construct() {parent::__construct('Can not generate the API credentials.', 10009);}
}
class AccountNotConfirmed_ApiException extends ApiException {
	function __construct() {
		parent::__construct(
			'This email has been registered but requires the confirmation. You were sent the confirmation email. Please follow instructions from this email to confirm your account.', 
			10010
		);
	} 
}

?>