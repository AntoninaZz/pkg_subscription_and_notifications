<?php 
class ModNewsletterSubscriptionHelper
{
    // this function handles click on subscribe & unsubscribe buttons 
    public static function subscribeUser($subscribers, $subject, $message, $sender)
    {
        if(isset($_POST['subscribe'])){ //if subscribe btn clicked
            $userEmail = $_POST['email']; //getting user entered email
            if(filter_var($userEmail, FILTER_VALIDATE_EMAIL)){ //validating user email
                if(mail($userEmail, $subject, $message, "From: $sender\r\nContent-Type: text/html; charset=UTF-8\r\n")){
                    $isNew = true;
                    foreach($subscribers as $subscriber){
                        if($subscriber == "$userEmail\r\n"){
                            $isNew = false;
                        } 
                    }
                    if($isNew){
                        $new_line = "$userEmail\r\n";
                        $file = fopen(dirname(__FILE__) . '/subscribers.txt', "a") or die("Unable to open file!");
                        fputs($file, $new_line);
                        fclose($file);
                        $subscribers = modNewsletterSubscriptionHelper::updateSubscriptionList();
                        JFactory::getApplication()->enqueueMessage("Ви успішно підписались на новини", 'success');
                    } else {
                        JFactory::getApplication()->enqueueMessage("Ви вже підписані на новини");
                    }
                    $userEmail = "";
                }else{
                    JFactory::getApplication()->enqueueMessage("Упс! Щось пішло не так :( ", 'error');
                }
            }else{
                JFactory::getApplication()->enqueueMessage("Недійсна електронна адреса: $userEmail", 'error');
            }
        }
        if(isset($_POST['unsubscribe'])){ //if unsubscribe btn clicked
            $userEmail = $_POST['email']; //getting user entered email
            $unsubscribed = false;
            for($i = 0; $i < count($subscribers); $i++){
                if($subscribers[$i] == "$userEmail\r\n"){
                    $subscribers[$i] = "-\r\n";
                    $unsubscribed = true;
                } 
            }
            if($unsubscribed) {
                $file = fopen(dirname(__FILE__) . '/subscribers.txt', "w") or die("Unable to open file!");
                foreach($subscribers as $subscriber){
                    if($subscriber !== "-\r\n"){
                        fwrite($file, "$subscriber\r\n");
                    } 
                }
                fclose($file);
                JFactory::getApplication()->enqueueMessage("Ви успішно відписались від новин", 'success');
            } else {
                JFactory::getApplication()->enqueueMessage("Ви не підписані на новини");
            }
            $subscribers = modNewsletterSubscriptionHelper::updateSubscriptionList();
        }
        return;
    }

    // this function makes an array of subscribers from file
    public static function updateSubscriptionList(){
        $subscribers = array();

        $txt = file_get_contents(dirname(__FILE__) . '/subscribers.txt');
        $txt = str_replace("\r\n\r\n","\r\n",$txt);
        file_put_contents(dirname(__FILE__) . '/subscribers.txt', $txt);

        $handle = @fopen(dirname(__FILE__) . '/subscribers.txt', "r") or die("Unable to open file!");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if($line !== ""){
                    array_push($subscribers, $line);
                }
            }
            fclose($handle);
        }
        return $subscribers;
    }
}