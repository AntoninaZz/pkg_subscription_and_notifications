<?php
defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__) . '/helper.php';

$config = JFactory::getConfig();
$sender = $config->get( 'mailfrom' );

$email = $params->get('email', '') == "1" ? true : false;
$tg = $params->get('tg', '') == "1" ? true : false;
$subject = $params->get('subject', 'Newsletter Subscription');
$message = $params->get('message', 'You have successfully subscribed to a newsletter. When a new article appears on the site, you will automatically receive a notification.');
$link = $params->get('link', '');
$tgExists = !empty($link);
$userEmail = ""; 
$subscribers = modNewsletterSubscriptionHelper::updateSubscriptionList();

require JModuleHelper::getLayoutPath('mod_newsletter_subscription', $params->get('layout', 'default'));