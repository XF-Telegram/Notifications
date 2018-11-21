<?php
namespace Kruzya\TelegramNotifications\Entity;

use XF\Mvc\Entity\Structure;
use XF\Mvc\Entity\Entity;

class Notification extends Entity {
  public static function getStructure(Structure $structure) {
    $structure->table       = 'xf_tg_messages_queue';
    $structure->shortName   = 'Kruzya\\TelegramNotifications:Notification';
    $structure->primaryKey  = 'id';

    $structure->getters     = [];
    $structure->relations   = [];

    $structure->columns     = [
      'id'              => [
        'type'          => self::UINT,
        'autoIncrement' => true,
        'nullable'      => true,
      ],
      'options'         => [
        'type'          => self::SERIALIZED_ARRAY,
        'required'      => true,
      ],
      'status'          => [
        'type'          => self::STR,
        'default'       => 'planned',
        'allowedValues' => ['planned', 'finished', 'failed'],
      ],
    ];

    return $structure;
  }
}