<?php
defined('_JEXEC') or die('Restricted access');

class PlgContentNew_Article_Notifications extends JPlugin
{
  public $link;
  public $send_tg;
  public $channel;
  public $route;
  public $image;
  
  public function onContentAfterSave($context, $article, $isNew)
  {
    if($context !== 'com_content.article' || $isNew !== true) {
      return;
    }

    $this->link = str_replace("administrator/",'',JUri::base()); // in order for sendTelegram function to work link must be valid, make sure of it if you use joomla on localhost
    $send_emails = $this->params->get('send_emails', '') == "1" ? true : false;
    $this->send_tg = $this->params->get('send_tg', '') == "1" ? true : false;
    $token = $this->params->get('token', '');
    $this->channel = $this->params->get('channel', '');
    $cats = $this->params->get('cat_id', array());
    $newsletter = $this->params->get('newsletter', 'New Article');
    $bccmax = intval($this->params->get('bccmax', "50"));

    foreach($cats as $cat){
      if($article->catid==$cat){
        if($this->send_tg) {
          $this->sendTelegram($token, $article);
        }
        if($send_emails) {
          $this->sendEmail($newsletter, $article, $bccmax);
        }
        return;
      }
    }
    return;
  }

  public function sendTelegram($token, $article)
  {
    if(empty($token) || empty($this->channel)){
      return;
    }

    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';

    $text = "<strong>".$article->title."</strong>\n".strip_tags($article->introtext);
    $maxlen = 490;
    if(strlen($text) > $maxlen){
      $text = mb_substr($text." ", 0, $maxlen)."...";
    }
    else if($article->fulltext!==""){
      $text = substr_replace($text ,"...", -1);
    }

    $images  = json_decode($article->get("images"));
    $this->image  = $images->image_intro;

    $this->route = $this->link . "index.php?option=com_content&view=article&id=" . $article->id;
    if ((int) $article->catid > 1) {
		$this->route .= '&catid=' . $article->catid;
    }

    $inlinekeys[] = array(
      array(
        "text" => JText::_('Подробиці'),
        "url" => JRoute::_($this->route)
        )
      );
    $inlinekeyboard = array("inline_keyboard" => $inlinekeys);

    $content = array();
    if($this->image == ""){
      $content = array(
        "chat_id" => $this->channel,
        "text" => $text,
        "parse_mode" => "HTML",
        "reply_markup" => $inlinekeyboard
      );
    } else {
      $url = 'https://api.telegram.org/bot' . $token . '/sendPhoto';

      $content = array(
        "chat_id" => $this->channel,
        "caption" => $text,
        "parse_mode" => "HTML",
        "photo" => $this->link . $this->image,
        "reply_markup" => $inlinekeyboard
        );
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
  }

  public function sendEmail($newsletter, $article, $bccmax)
  {
    require_once dirname(__FILE__, 4) . '/modules/mod_newsletter_subscription/helper.php';
    $subscribers = modNewsletterSubscriptionHelper::updateSubscriptionList();
    $bccs = array();
    $mails = "";
    $count = 1;
    for($i = 0; $i < count($subscribers); $i++){
      if($count < $bccmax){ 
        $mails .= str_replace("\r\n", ",", $subscribers[$i]);
        if($i == count($subscribers) - 1){
          array_push($bccs, $mails);
        }
        $count++;
      } else {
        $mails .= str_replace("\r\n", "", $subscribers[$i]);
        array_push($bccs, $mails);
        $mails = "";
        $count = 1;
      }
    }

    $img_src = $this->link . $this->image; 
    $alt = json_decode($article->get("images"))->image_intro_alt;
    $cover = $this->image == "" ? "" : "<div><img src='$img_src' alt='$alt'/></div>";
    $channelname = ltrim($this->channel, $this->channel[0]);
    $footer = $this->send_tg && !empty($this->channel) ? "<footer style='margin-top: 10px;'>Також слідкуйте за новинами у телеграм-каналі <a href='https://t.me/$channelname' target='_blank'>$this->channel</a></footer>" : "";

    $config = JFactory::getConfig();
    $to = $config->get( 'mailfrom' );
    $subject = "$newsletter: " . $article->title;
    $message = "<h2>$article->title</h2>" . 
                $cover . $article->introtext . 
                "<div><a href='$this->route' target='_blank' style='font-size: 16px;'>Читати далі</a></div>" . 
                $footer;
    $headers  = "MIME-Version: 1.0\r\n" ;
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    foreach($bccs as $bcc){
      mail($to, $subject, $message, $headers."Bcc: $bcc\r\n");
    }
  }
}