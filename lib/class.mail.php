<?php
/*
 * $Id: class.mail.php 668 2011-06-13 03:23:28Z thomas $
 */
use Rain\Tpl;

require_once ('phpmailer/class.phpmailer.php');

class MailModule
{
  var $subject;
  var $vars;       // vars
  var $fromEmail;
  var $fromName;
  var $charSet;
  var	$content;
  var $phpmailer;  // class phpmailer
//  var $view;      // class view
  var $template;   // template file *.html
  var $embeddedImagePath; // embedded images file path
  var $embeddedImage = false;  // boolean
  var $host;
  var $user;
  var	$pass;


	function __construct()
	{
		$this->phpmailer = new phpmailer();
		$this->phpmailer->IsHTML(true);
		$this->phpmailer->ContentType = "text/html";
		$this->charSet  = "utf-8";
//		$this->view = newobj('lib.view', null);
//		$this->smart    =& loadLibrary("View");
		if (defined('SMTP_RELAY'))
			$this->host = SMTP_RELAY;
		if (defined('SMTP_USER'))
			$this->user = SMTP_USER;
		if (defined('SMTP_RELAY'))
			$this->pass = SMTP_PASS;
		$this->embeddedImagePath = DOC_ROOT;
		$this->embeddedImage = true;
		$this->fromName = MAIL_FROM;
		$this->fromEmail = SITE_MAIL;
		$this->vars['SITE_TITLE'] = SITE_TITLE;
		$this->vars['SITE_URL'] = $_SERVER['HTTP_HOST'];
	}

	function __destruct()
	{
		global $_top;

		unset ($_top);
		$this->phpmailer->SmtpClose();
	}

  function addAddress($address,$name = Null)
  {
		$name = ( !empty($name) ? $name : $address );
		$this->phpmailer->AddAddress($address,$name);
  }

  function addReplyTo($address,$name= Null)
  {
		$name = ( !empty($name) ? $name : $address );
		$this->phpmailer->AddReplyTo($address,$name);
  }

	function addAttachment($path,$name="",$encoding="base64",$type = "application/octet-stream")
	{
		$this->phpmailer->AddAttachment($path,$name,$encoding,$type);
	}

	function addEmbeddedImage ($path,$cid,$name= "", $encoding = "base64", $type = "application/octet-stream")
	{
	         $this->phpmailer->AddEmbeddedImage($path,$cid,$name,$encoding,$type);
	}

	function _addEmbeddedImage($body)
	{
	   if(!$this->embeddedImage)  return $body;
	   preg_match_all("/<img[^>]+>/U",$body,$matches);
	   if(sizeof($matches[0])<=0) return $body;
	   $embeddedImages = array();
     foreach($matches[0] as $key=>$value)
     {
       preg_match('/src=\"[^\"]+\"/U',$value,$matche);
       $array = array('src=','\\','"');
       $image = str_replace($array,"",$matche[0]);
       if(!in_array($image,$embeddedImages))
       {
         $embeddedImages[] = $image;
         $md5 = MD5($image);
         $this->AddEmbeddedImage($this->embeddedImagePath.$image,$md5,$image);
         $body = str_replace($image,"cid:".$md5,$body);
       }
     }
     $body = str_replace("\\\"","\"",$body);
     return $body;
	}


	function send()
	{
		//print_r($this);
		$this->phpmailer->Subject   = $this->subject;
		$this->phpmailer->CharSet   = $this->charSet;
//		$this->phpmailer->FromEmail = $this->fromEmail;
//		$this->phpmailer->FromName  = $this->fromName;
		$this->phpmailer->SetFrom($this->fromEmail, $this->fromName);

		if (!empty($this->host))
		{
			$this->phpmailer->IsSMTP();
			$this->phpmailer->Host = $this->host;
			if (!empty($this->user))
			{
				$this->phpmailer->SMTPAuth = true;
				$this->phpmailer->SMTPSecure = 'ssl';
				$this->phpmailer->Username = $this->user;
				$this->phpmailer->Password = $this->pass;
				$this->phpmailer->Port = 465;
			}
		}
		else
			$this->phpmailer->IsMail();

//     if(empty($this->tpl))
//     {
//       $this->template = "phpmailer.html";
//     }
//     else
//     {
//       if (is_array($this->vars))
//		   {
//	   		foreach($this->vars as $k => $v)
//			   	$this->smart->assign($k, $v);
//		   }
//		 }
//
////     $this->smart->load_template($this->template);
//     $this->phpmailer->Body = $this->smart->result();
//     $this->phpmailer->Body = $this->_addEmbeddedImage($this->phpmailer->Body);

    //default
		if (empty($this->template))
     	$this->phpmailer->Body = $this->content;
    else
    {
			$page = new Tpl;
			if (is_array($this->vars))
				foreach ($this->vars as $k => $v)
					$page->assign($k, $v);
			$page->assign('SITE_TITLE', SITE_TITLE);
			$page->assign('SITE_MAIL', SITE_MAIL);
      $page->assign('SITE_URL', SITE_URL);
			$page->assign('SERVER_HOST', SERVER_HOST);
      $page->assign('now', date('Y-m-d H:i:s'));
			$this->phpmailer->Body = $page->draw($this->template, true);
    }
    $this->phpmailer->Body = $this->_addEmbeddedImage($this->phpmailer->Body);
    try {
	    if($this->phpmailer->Send())
	      return true;
	    else
	      return false;
    } catch (Exception $e)
    {
    	print_r($e);
    	exit;
    	return false;
    }
	}
}
?>