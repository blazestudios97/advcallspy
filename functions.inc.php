<?php
/**
 * Advanced Call Spy Module
 * 
 * @author      Applied Messaging Inc / Blaze Studios
 * @copyright   2025 Applied Messaging Inc / Blaze Studios
 * @license     GNU Affero General Public License v3.0 (AGPL-3.0)
 */
function advcallspy_check_extensions($exten=true) {
    return FreePBX::create()->Advcallspy()->checkExtMap($exten);
}