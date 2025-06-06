<?php
namespace FreePBX\modules\Advcallspy;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$configs = $this->dumpAll();
		$this->addConfigs($configs);
	}
}