<?php
namespace Kruzya\TelegramNotifications;

use XF\AddOn\AbstractSetup;
use XF\Db\Schema\Create;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup {
  public function install(array $stepParams = []) {
    $db = $this->db();
    $sm = $db->getSchemaManager();

    $sm->alterTable('tg_user', function (Alter $table) {
      $table->addColumn('notifications', 'bool')->setDefault(0)->after('photo_url');
    });

    $sm->createTable('tg_messages_queue', function (Create $table) {
      $table->addColumn('id',       'int')->autoIncrement();
      $table->addColumn('options',  'blob');
      $table->addColumn('status',   'enum')->values(['planned', 'finished', 'failed'])->setDefault('planned');
    });
  }

  public function upgrade(array $stepParams = []) {}

	public function uninstall(array $stepParams = []) {
    $db = $this->db();
    $sm = $db->getSchemaManager();

    $sm->dropTable('tg_messages_queue');
    $sm->alterTable('tg_user', function (Alter $table) {
      $table->dropColumns(['notification']);
    });
  }
}