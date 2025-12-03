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
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->importAll($configs);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables) {
		$this->restoreLegacyAll($pdo);
	}
}