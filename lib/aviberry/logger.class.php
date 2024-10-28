<?php
/*  
 * Copyright 2012 Movavi (email : support@movavi.com)
 */



class Members_Logger {
	private static $logs;
	private static $logflow;
	
	/**
	 * Благодаря $logflow одноименные файлы логов будут подразделяться 
	 * на программные потоки, из которых они формировались.
	 * Первый лог, полученный через вызов этого метода, считается 
	 * логом самого потока, и поток получает имя этого лога. Все 
	 * последующие логи, полученные через вызов этого метода, считаются
	 * принадлежащими уже этому потоку.
	 */ 
	public static function getLog($logname, $loglevel = Members_Log::NONE, $email_notification = false, $debug_dir = false) {
		// First logname that was gotten, will be also used as logflow name. 
		if (!isset(self::$logflow)) {
			self::$logflow = $logname;
			$logname = ''; // For pretty filename of first log, that is log of flow.
		}
			
		$logid = self::$logflow . '_' . $logname;
			
		if (!isset(self::$logs[$logid]))
			self::$logs[$logid] = new Members_Log(self::$logflow, $logname, $loglevel, $email_notification, $debug_dir);
            
		return self::$logs[$logid];
	}
    
	/**
	 * Constructor.
	 */
    private function __contsruct() {
    }
	
	/**
	 * Clonning. 
	 */
	private function __clone() {
    }
}




class Members_Log {
	const EMERG   = 1; // System is unusable
	const ALERT   = 2; // Immediate action required
	const CRIT    = 3; // Critical conditions
	const ERR     = 4; // Error conditions
	const WARNING = 5; // Warning conditions
	const NOTICE  = 6; // Normal but significant
	const INFO    = 7; // Informational
	const DEBUG   = 8; // Debug-level messages
	
	const ALL   = 0xffffffff; // All messages
	const NONE  = 0x00000000; // No message
	
	private $handle = null;
	private $level  = null;
	private $timers = array();
	
	//email notification system
	private $admin_email = false;
	private $level_email_admin = self::NONE;
	private $admin_email_subject;
	private $sender_name = 'Members_Log debug system';
	private $sender_email = 'Members_Log@movavi.com';
	
	
	public $freeMode = false;
	
	/**
	 * Constructor.
	 * 
	 * @param string $logname
	 * @return void
	 */
	public function __construct($logflow, $logname, $loglevel = self::NONE, $email_notification = false, $debug_dir = false) {
		$this->level = $loglevel;
		
		//if need to notify by email
		if(!empty($email_notification['admin_email']) && !empty($email_notification['level'])){			 
			$this->admin_email = $email_notification['admin_email'];
			$this->level_email_admin = $email_notification['level'];
			
			if(!empty($email_notification['subject']))
				$this->admin_email_subject = $email_notification['subject'];
			else
				$this->admin_email_subject = $logflow . ($logname ? '.' . $logname : '');
			
			if(!empty($email_notification['sender_name']))
				$this->sender_name = $email_notification['sender_name'];
			
			if(!empty($email_notification['sender_email']))
				$this->sender_email = $email_notification['sender_email'];			
		}
		
		//if need to write output file
		if ($this->level != self::NONE) {
			// Don't rewrite files of other program flow if this flow is not logged.

			if(!$debug_dir)
				$debug_dir = DEBUG_DIR;

			$filename = $debug_dir . $logflow . ($logname ? '.' . $logname : '') . '.log';
			if (file_exists($filename) && !is_writable($filename) && $this->admin_email) {								
				$message = "Have tried to write in the log '$filename' which is not writable.";				
				$this->sendMail($this->sender_email, $this->sender_name, $this->admin_email, 'Log is not accessable.', $message);
				
				return;
			}
			
			$this->handle = fopen($filename, 'a');
			chmod($filename, 0664);
		}
	}

	public function __destruct() {
		if (is_resource($this->handle))
			fclose($this->handle);
	}
	
	private function l2s($level) {
		$result = '';
		
		switch ($level) {
			case self::EMERG:   $result = 'EMERGENCY'; break;
			case self::ALERT:   $result = 'ALERT';     break;
			case self::CRIT:    $result = 'CRITICAL';  break;
			case self::ERR:     $result = 'ERROR';     break;
			case self::WARNING: $result = 'WARNING';   break;
			case self::NOTICE:  $result = 'NOTICE';    break;
			case self::INFO:    $result = 'INFO';      break;
			case self::DEBUG:   $result = 'DEBUG';     break;
		}
		
		return $result;
	}

	private function formatTime($time) {
		$result = $time;
		
		$formatter = 0;
		$formats = array('ms', 's', 'm');
		
		if ($time >= 1000 && $time < 60000) {
			$formatter = 1;
			$result = ($time / 1000);
		} elseif ($time >= 60000) {
			$formatter = 2;
			$result = ($time / 1000) / 60;
		}
		$result = number_format($result, 3, '.', '') . ' ' . $formats[$formatter];
		
		return $result;
	}
	
	private function log($mess, $level) {
		if (is_numeric($level)) {
			//write to output file
			if ($level <= $this->level && is_resource($this->handle)) {
				if ($this->freeMode) {
					fwrite($this->handle, $mess);
				} else {
					fwrite($this->handle, $this->l2s($level) . ' ' . gmdate('Y-m-d H:i:s') . "\n");
					fwrite($this->handle, $mess);
					fwrite($this->handle, "\n\n");
				}
				
				fflush($this->handle);
			}
			//var_dump($this); var_dump($level); die();
			//send notification by email			
			if ($level <= $this->level_email_admin && $this->admin_email) {			
				$this->sendMail($this->sender_email, $this->sender_name, $this->admin_email, $this->admin_email_subject, $mess);
			}
			
		} else {
			if (is_resource($this->handle)) {
				if ($this->freeMode) {
					fwrite($this->handle, $mess);
				} else {
					fwrite($this->handle, $level . ' ' . gmdate('Y-m-d H:i:s') . "\n");
					fwrite($this->handle, $mess);
					fwrite($this->handle, "\n\n");
				}
				
				fflush($this->handle);
			}
		}
	}

	public function handleException($e) {
		$this->logError(sprintf("errno=%s,\nerrstr=%s,\nerrfile=%s,\nerrline=%s,\nerrcontext=%s", $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), print_r($e->getTrace(), true)));
	}

	public function handleError($errno, $errstr, $errfile, $errline, $errcontext) {
		$this->logError(sprintf("errno=%s,\nerrstr=%s,\nerrfile=%s,\nerrline=%s,\nerrcontext=%s", $errno, $errstr, $errfile, $errline, print_r($errcontext, true)));
	}
	
	private function getMessage($args) {
		$numargs = count($args);
		if ($numargs > 1) {
			$format = array_shift($args);
			foreach ($args as &$arg) {
				if (is_bool($arg))
					$arg = $arg ? 'true' : 'false';
				else
					$arg = print_r($arg, true);
			} 
			return vsprintf($format, $args);
		} else {
    		return $args[0];
		}
	}
	
	/**
	 * Записывает в лог сообщение с уровнем ERROR. Синопсис такой же как и у 
	 * функции sprintf().
	 * 
	 * @param string $mess
	 * @return void
	 */
	public function logError($mess) {
    	$mess = $this->getMessage(func_get_args());
		$this->log($mess, self::ERR);
	}
	
	public function logWarning($mess) {
    	$mess = $this->getMessage(func_get_args());
		$this->log($mess, self::WARNING);
	}
	
	public function logNotice($mess) {
    	$mess = $this->getMessage(func_get_args());
		$this->log($mess, self::NOTICE);
	}
	
	public function logInfo($mess) {
    	$mess = $this->getMessage(func_get_args());
		$this->log($mess, self::INFO);
	}
	
	public function logDebug($mess) {
    	$mess = $this->getMessage(func_get_args());
		$this->log($mess, self::DEBUG);
	}
	
	public function logSpeed($timer) {
		if (!isset($this->timers[$timer])) {
			$this->timers[$timer] = microtime(true);
			$mess = "$timer: START";
		} else {
			$time = microtime(true);
			$speed = $time - $this->timers[$timer];
			$speed = $this->formatTime($speed);
			$mess = "$timer: END: $speed";
			
			unset($this->timers[$timer]);
		}
		
		$this->log($mess, 'SPEED');
	}
	
	/**
     * Returns "true" if message is putted in mail queue for delivery.
     *
     * @return bool
     */
    private function sendMail($sender_email, $sender_name, $email, $subject, $message) {
		$headers  = "From: $sender_name <$sender_email>\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n";
		$headers .= "Content-type: text/html; charset=utf-8\n";
		$headers .= "Return-Path: $sender_name <$sender_email>\n";
		$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
		
		return mail($email, $subject, $message, $headers);
	}
}

?>