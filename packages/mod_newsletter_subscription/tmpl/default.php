<?php 
defined('_JEXEC') or die('Restricted access'); 
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . '/modules/mod_newsletter_subscription/css/style.css');
?>
<div class="newsletter_subscription">
    <input type="checkbox" id="toggle">
    <label for="toggle" class="btn btn-primary w-100">Підписатися на новини</label>
    <div class="wrapper">
        <label for="toggle"><span class="cancel-icon icon-cancel-2"></span></label>
        <div class="icon"><span class="icon-mail"></span></div>
        <div class="content">
            <header>Підписатися на новини</header>
            <p class="<?php echo $email ? "" : "invisible"; ?>">Введіть електронну адресу, на яку Ви б хотіли отримувати повідомлення про оновлення на сайті.</p>
        </div>
        <form action="index.php" method="POST" class="<?php echo $email ? "" : "invisible"; ?>">
            <?php modNewsletterSubscriptionHelper::subscribeUser($subscribers, $subject, $message, $sender); ?>
            <div class="field">
                <input type="text" class="email" name="email" placeholder="e-mail" required value="<?php echo $userEmail; ?>">
            </div>
            <div class="field">
                <button type="submit" name="subscribe" class="custom-button">
                    <span class="icon-mail" aria-hidden="true"></span>
                    Підписатися
                </button>
                <button type="submit" name="unsubscribe" class="link" >
                    Відписатися від розсилки
                </button>
            </div>
        </form>
        <div class="mar-bot <?php echo $tg && $tgExists ? "" : "invisible"; ?>"><?php echo $tg && $email ? "або п" : "П" ?>ідпишіться на новини сайту у telegram</div>
        <a class="custom-button <?php echo $tg && $tgExists ? "" : "invisible"; ?>" href="<?php echo $link; ?>" target="_blank" rel="noopener">
            <img class="tg-icon" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/1024px-Telegram_logo.svg.png" />
            Підписатися
        </a>
    </div>
</div>