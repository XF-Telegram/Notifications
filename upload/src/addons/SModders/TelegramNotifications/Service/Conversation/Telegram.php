<?php

/**
 * This file is a part of [Telegram] Notifications.
 * All rights reserved.
 *
 * Developed by SourceModders.
 */

namespace SModders\TelegramNotifications\Service\Conversation;


use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use XF\Entity\ConversationMessage;
use XF\Entity\User;
use XF\Service\AbstractService;

class Telegram extends AbstractService
{
    /**
     * @var \XF\Entity\ConversationMessage
     */
    protected $message;
    
    /**
     * @var string
     */
    protected $actionType;
    
    /**
     * @var \XF\Entity\User
     */
    protected $sender;
    
    /**
     * @var \XF\Entity\User
     */
    protected $receiver;
    
    /**
     * @var \XF\Language
     */
    protected $language;
    
    /**
     * @var \TelegramBot\Api\BotApi
     */
    protected $api;

    /**
     * Telegram constructor.
     * @param \XF\App $app
     * @param User $receiver
     * @param mixed ...$properties
     */
    public function __construct(\XF\App $app, User $receiver, ...$properties)
    {
        parent::__construct($app);
        
        $this->receiver = $receiver;
        $this->language = $app->language($receiver->language_id);
        $this->api = $app->get('smodders.telegram')->api();
        
        $this->setInitialProperties(...$properties);
    }
    
    /**
     * @param ConversationMessage $message
     * @param $actionType
     * @param User $sender
     */
    protected function setInitialProperties(ConversationMessage $message, $actionType, \XF\Entity\User $sender)
    {
        $this->message = $message;
        $this->actionType = $actionType;
        $this->sender = $sender;
    }
    
    /**
     * @return string
     */
    protected function getNotificationBody()
    {
        $phrase = $this->language->phrase('push_conversation_' . $this->actionType, [
            'boardTitle' => $this->app->options()->boardTitle,
            'title' => $this->message->Conversation->title,
            'sender' => $this->sender->username
        ]);
        
        return $phrase->render('raw');
    }
    
    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->app->router('public')->buildLink(
            'canonical:conversations/unread', $this->message->Conversation
        );
    }
    
    public function sendNotification()
    {
        try {
            $this->api->sendMessage(
                $this->receiver->Profile->connected_accounts['smodders_telegram'],
                $this->getNotificationBody(), null, true, null,
                new InlineKeyboardMarkup([
                    [
                        'text'  => \XF::phraseDeferred('open'),
                        'url'   => $this->getNotificationUrl(),
                    ]
                ])
            );

            return true;
        }
        catch (\Exception $e)
        {
            // Disable notifications for this user.
            /** @var \SModders\TelegramNotifications\XF\Entity\UserOption $userOption */
            $userOption = $this->receiver->Option;
            $userOption->smodders_tgnotifications_on_conversation = false;
            $userOption->save();

            return false;
        }
    }
}