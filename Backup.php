<?php
/**
 * Advanced Call Spy Module
 * 
 * @author      Applied Messaging Inc / Blaze Studios
 * @copyright   2025 Applied Messaging Inc / Blaze Studios
 * @license     GNU Affero General Public License v3.0 (AGPL-3.0)
 */
namespace FreePBX\modules\Advcallspy;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$configs = $this->dumpAll();
		$this->addConfigs($configs);
	}
}