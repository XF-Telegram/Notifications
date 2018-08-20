<?php
namespace Kruzya\TelegramNotifications\Entity;

class User extends XFCP_User {
  public function addNotification($data) {
    // add a chat id to custom data.
    $data['chat_id']  = $this->id;

    $entity = \XF::em()->create('Kruzya\TelegramNotifications:Notification');
    $entity->options = $data;
    $entity->save();
  }
}