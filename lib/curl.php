<?php
/*
 * $Id: curl.php 954 2011-09-19 09:04:13Z thomas $
 */
class CURL {
	var $callback = false;
	var $async = false;
	var $header = false;
	var $cookie = false;
	var $login;
	var $password;

	function setCallback($func_name)
	{
		$this->callback = $func_name;
	}

	function doRequest($method, $url, $vars)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if (substr($url, 0, 5) == 'https')
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSLVERSION, 1);
      curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
    }
		if ($this->header)
		{
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16');
		}
//		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 900);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($this->cookie)
		{
			curl_setopt($ch, CURLOPT_COOKIEJAR, DOC_ROOT . '/tmp/cookie.txt');
			curl_setopt($ch, CURLOPT_COOKIEFILE, DOC_ROOT . '/tmp/cookie.txt');
		}
		if (!empty($this->login))
		{
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER,
				array("HTTP_AUTH_LOGIN: {$this->login}",
							"HTTP_AUTH_PASSWD: {$this->password}",
							"HTTP_PRETTY_PRINT: TRUE",
							"Content-Type: text/xml"));
		}
		if ($method == 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}
		if ($this->async)
		{
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->async);
		}
		$data = curl_exec($ch);
//		echo $data;
//		exit;
		if (curl_errno($ch) && !$this->async)
			echo curl_error($ch);
//		curl_close($ch);
		if ($data)
		{
			if ($this->callback)
			{
			   $callback = $this->callback;
			   $this->callback = false;
			   return call_user_func($callback, $data);
			}
			else
			   return $data;
		}
		else
			return ''; //curl_error($ch) . ':' . curl_error($ch);
	}

	function get($url) {
	   return $this->doRequest('GET', $url, 'NULL');
	}

	function post($url, $vars) {
		if (is_array($vars))
		{
			foreach ($vars as $k => $v)
				$vars[$k] = $k . '=' . urlencode($v);
			$vars = implode('&', $vars);
		}
	  return $this->doRequest('POST', $url, $vars);
//		if (!empty($this->login))
//		{
//			$vars = str_replace("\n", '', $vars);
//			$vars = str_replace("\r", '', $vars);
//			$vars = str_replace('!', '\!', $vars);
//			$vars = str_replace('"', '\"', $vars);
//			$pw = str_replace('!', '\!', $this->password);
//			$curl = "curl -k -H \"HTTP_AUTH_LOGIN: {$this->login}\" -H \"HTTP_AUTH_PASSWD: {$this->password}\" -H \"HTTP_PRETTY_PRINT: TRUE\" -H \"Content-Type: text/xml\" --connect-timeout 30 \"$url\" --data-binary \"$vars\"";
//		}
//		else
//		{
//			if (is_array($vars))
//			{
//				unset($s);
//				foreach ($vars as $k => $v)
//					$s[] = "$k=" . urlencode($v);
//				$vars = implode('&', $s);
//			}
//			$curl = "curl -k --connect-timeout 30 \"$url\" --data-binary \"$vars\"";
//		}
//
////		$curl = "curl -k -H \"HTTP_AUTH_LOGIN: {$this->login}\" -H \"HTTP_AUTH_PASSWD: {$pw}\" -H \"HTTP_PRETTY_PRINT: TRUE\" -H \"Content-Type: text/xml\" --connect-timeout 10 \"$url\" --data-binary \"$vars\"";
////		$curl = "curl -k -u {$this->login}:{$pw} -H \"HTTP_PRETTY_PRINT: TRUE\" -H \"Content-Type: text/xml\" --connect-timeout 10 \"$url\" --data-binary \"$vars\"";
//		$r = `$curl`;
//		return substr($r, 0, 4) == 'curl' ? null : $r;
	}
}
?>