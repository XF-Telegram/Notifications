<?php
namespace Kruzya\TelegramNotifications;

use XF\AddOn\AbstractSetup;
use XF\Db\Schema\Create;
use XF\Db\Schema\Alter;

use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\AddOn\StepRunnerUninstallTrait;

class Setup extends AbstractSetup {
  use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
  use StepRunnerUninstallTrait;

  /**
   * Install.
   */
  public function installStep1() {
    $this->db()->getSchemaManager()->alterTable('xf_tg_user', function (Alter $table) {
      $table->addColumn('notifications', 'bool')->setDefault(0)->after('photo_url');
    });
  }

  public function installStep2() {
    $this->db()->getSchemaManager()->createTable('xf_tg_messages_queue', function (Create $table) {
      $table->addColumn('id',       'int')->autoIncrement();
      $table->addColumn('options',  'blob');
      $table->addColumn('status',   'enum')->values(['planned', 'finished', 'failed'])->setDefault('planned');
    });
  }

  /**
   * Upgrade.
   */
  public function upgrade1003072Step1() {
    $this->db()->getSchemaManager()->renameTable('tg_messages_queue', 'xf_tg_messages_queue');
  }

  /**
   * Uninstall.
   */
	public function uninstallStep1() {
    $this->db()->getSchemaManager()->dropTable('xf_tg_messages_queue');
    $sm->alterTable('tg_user', function (Alter $table) {
      $table->dropColumns(['notification']);
    });
  }

  public function uninstallStep2() {
    $this->db()->getSchemaManager()->alterTable('xf_tg_user', function (Alter $table) {
      $table->dropColumns(['notification']);
    });
  }
}