<?php
$title = 'Spy Code ';

if(isset($_REQUEST['extdisplay']) && !empty($_REQUEST['extdisplay'])) {
    $spy = \FreePBX::Advcallspy()->getSpycode($_REQUEST['extdisplay']);
    $title .= "Edit: " . $_REQUEST['extdisplay'];
    $action = 'edit';
    $spyid = $_REQUEST['extdisplay'];
    $enforcelist = explode(':', $spy['enforcelist']) ?? [];
    $spiers =  explode('-', $spy['restricted']) ?? [];
    $spygroups = explode(':', $spy['spygroups']) ?? [];
    $delURL = "?display=advcallspy&action=delete&extdisplay=". $_REQUEST['extdisplay'];

} else {
    $title .= 'Add';
    $enforcelist = [];
    $spiers = [];
    $spygroups = [];
}
$userslist = FreePBX::Core()->listUsers();
$groupslist = FreePBX::Advcallspy()->listGroups();
$qsagentlist = '';
$spier_list = '';
$groups_list = '';

// build enforce targets select options
foreach ($userslist as $result) {
    $selected = (in_array($result[0], $enforcelist) ? 'selected' : '');
	$qsagentlist .= "<option value='" . $result[0] . "' ". $selected . ">" . $result[0] . " (" . $result[1] . ")</option>\n";
}
// build allowed spiers select options
foreach ($userslist as $result) {
    $selected = (in_array($result[0], $spiers) ? 'selected' : '');
	$spier_list .= "<option value='" . $result[0] . "' ". $selected . ">" . $result[0] . " (" . $result[1] . ")</option>\n";
}
// build enforce spy groups
foreach ($groupslist as $result) {
    $selected = (in_array($result[0], $spygroups) ? 'selected' : '');
	$groups_list .= "<option value='" . $result[0] . "' ". $selected . ">" . $result[0] . " (" . $result[1] . ")</option>\n";
}
$spycode = $spy['spycode'] ?? '';
$spytype = $spy['spytype'] ?? 'ChanSpy';
$bridged = $spy['bridged'] ?? 1;
$cycledtmf = $spy['cycledtmf'] ?? '';
$exitdtmf = $spy['exitdtmf'] ?? '';
$modedtmf = $spy['modedtmf'] ?? '';
$barge = $spy['barge'] ?? '';
$whisper = $spy['whisper'] ?? '';
$listen = $spy['listen'] ?? '';
$skip = $spy['skip'] ?? '';
$sayname = $spy['sayname'] ?? '';
$passcode = $spy['passcode'] ?? '';
$eventlog = $spy['eventlog'] ?? 0;
$genhint = $spy['genhint'] ?? 0;
$description = $spy['description'] ?? '';
$status = $spy['status'] ?? 'disabled';
$quietmode = $spy['qmode'] ?? '';
$stopspy = $spy['stopspy'] ?? '';
$exithangup = $spy['exithangup'] ?? '';

for ($i=0; $i<=9; $i++ ) {
	$digits[]="$i";
}
$digits[] = '*';
$digits[] = '#';
// cycle dtmf select options
$cycleopts = '<option value=""'.($cycledtmf == '' ? ' SELECTED' : '').'>'._("Disable")."</option>";
foreach ($digits as $digit) {
	$cycleopts .= '<option value="'.$digit.'"'.($digit == $cycledtmf ? ' SELECTED' : '').'>'.$digit."</option>\n";
}
// exit dtmf select options
$exitopts = '<option value=""'.($exitdtmf == '' ? ' SELECTED' : '').'>'._("Disable")."</option>";
foreach ($digits as $digit) {
	$exitopts .= '<option value="'.$digit.'"'.($digit == $exitdtmf ? ' SELECTED' : '').'>'.$digit."</option>\n";
}

$whisperhelp = "Enable 'whisper' mode, so the spying channel can talk to the spied-on channel. Private Whisper allows the spier to only talk to the spied-on channel/extension but they cannot listen to the channel/extension";


$helphtml = <<< HTML
<div><b>ChanSpy:</b> This will spy on the incoming and outgoing audio of a voice channel. This applies to both inbound (from device) and outbound (from PBX) calls. Currently only PJSIP channels are supported since there is generally a PJSIP channel involved in a call.</div><p>
<div><b>ExtenSpy:</b> This will spy on the incoming and outgoing audio of a specific extension. ExtenSpy only spies on outbound (from PBX) extension channels. In this mode, you will not be able to spy on calls made from user devices.</div><p>
While spying, the following actions may be performed:<p>
<div>
<div>Dialing '<b>#</b>' cycles the volume level.</div><p>

<div>Dialing '<b>*</b>' will stop spying on the current channel/extension and look for another channel/extension to spy on.</div><p>

<div>Dialing a series of digits followed by '#' builds a channel name. For example, dialing the digits '1234#' while spying will begin spying on the channel '1234'. Note that this feature will be overridden if DTMF Mode Switch is enabled.</div>
</div>
HTML;


?>
<div class="container-fluid">
	<h1><?php echo _("Advanced Call Spy")?></h1>
    <div><?= show_help($helphtml) ?></div>
	<h2><?= _($title) ?></h2>
	<div class="display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
                        <form autocomplete="off" name="edit" id="edit" action="" method="post" class="fpbx-submit" data-fpbx-delete="<?php echo $delURL ?? '';?>">
                            <input type="hidden" name="display" value="advcallspy">
                            <input type="hidden" name="form_action" value="<?php echo (isset($spyid) ? 'edit' : 'add') ?>">
                            <?php if(isset($action)) { ?> 
                            <input type="hidden" name="spycode" value="<?= $spycode ?>">
                            <?php } ?>
                        
                            <?php if(!isset($spyid)) { ?> 
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="form-group">
                                                        <div class="col-md-3 control-label">
                                                            <label class="control-label" for="spycode">Spy Code</label>
                                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="spycode"></i>
                                                        </div>
                                                        <div class="col-md-9">
                                                            <input type="text" class="form-control extdisplay" id="spycode" name="spycode" value="<?= $spycode ?>" required pattern="[0-9]+" title="Only digits are allowed">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="spycode-help" class="help-block fpbx-help-block">The extension number to dial to active call spying</span>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="description">Description</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
                                                </div>
                                                <div class="col-md-9"><input type="text" name="description" class="form-control " id="description" size="35"   tabindex=""  value="<?= $description ?>"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                    <span id="description-help" class="help-block fpbx-help-block general-find">Description of the spy code.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="spy-type">Spy Type</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="spy-type"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="spytype" id="chanspy" value="ChanSpy" <?php echo (!isset($spytype) || $spytype == "ChanSpy" ? "CHECKED" : ""); ?>>
                                                        <label for="chanspy"><?php echo _("ChanSpy"); ?></label>
                                                        <input type="radio" name="spytype" id="extenspy" value="ExtenSpy" <?php echo ($spytype == "ExtenSpy" ? "CHECKED" : ""); ?>>
                                                        <label for="extenspy"><?php echo _("ExtenSpy"); ?></label>
                                                        
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="spy-type-help" class="help-block fpbx-help-block"><?php echo _("Select the method of call spying."); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="status">Spy Code Status</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="status"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="status" id="status_enabled" value="enabled" <?php echo (!isset($status) || $status == "enabled" ? "CHECKED" : ""); ?>>
                                                        <label for="status_enabled"><?php echo _("Enabled"); ?></label>
                                                        <input type="radio" name="status" id="status_disabled" value="disabled" <?php echo ($status == "disabled" ? "CHECKED" : ""); ?>>
                                                        <label for="status_disabled"><?php echo _("Disabled"); ?></label>
                                                        
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="status-help" class="help-block fpbx-help-block"><?= _("Status of this Spy Code. Disabled by default.") ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Barge Mode -->
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="barge">Barge Mode</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="barge"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="barge" id="bargeyes" value="B" <?php echo (!isset($barge) || $barge == 'B' ? "CHECKED" : ""); ?>>
                                                        <label for="bargeyes"><?php echo _("Yes"); ?></label>
                                                        <input type="radio" name="barge" id="bargeno" value="" <?php echo ($barge === '' ? "CHECKED" : ""); ?>>
                                                        <label for="bargeno"><?php echo _("No"); ?></label>
                                                        
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="barge-help" class="help-block fpbx-help-block"><?= _(" Instead of whispering on a single channel barge in on both channels involved in the call"); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="whisper">Whisper Mode</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="whisper"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="whisper" id="whisperyes" value="w" <?php echo (!isset($whisper) || $whisper == 'w' ? "CHECKED" : ""); ?>>
                                                        <label for="whisperyes"><?php echo _("Yes"); ?></label>
                                                        <input type="radio" name="whisper" id="whisperp" value="W" <?php echo ($whisper == 'W' ? "CHECKED" : ""); ?>>
                                                        <label for="whisperp"><?php echo _("Private"); ?></label>
                                                        <input type="radio" name="whisper" id="whisperno" value="" <?php echo ($whisper == '' ? "CHECKED" : ""); ?>>
                                                        <label for="whisperno"><?php echo _("No"); ?></label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="whisper-help" class="help-block fpbx-help-block"><?php echo _($whisperhelp); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="listen">Only Listen Mode</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="listen"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="listen" id="listenyes" value="o" <?php echo (!isset($listen) || $listen == 'o' ? "CHECKED" : ""); ?>>
                                                        <label for="listenyes"><?php echo _("Yes"); ?></label>
                                                        <input type="radio" name="listen" id="listenno" value="" <?php echo ($listen == '' ? "CHECKED" : ""); ?>>
                                                        <label for="listenno"><?php echo _("No"); ?></label>
                                                        
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="listen-help" class="help-block fpbx-help-block"><?php echo _("The spier can only hear the spied-on call for the user not the other party."); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="modesdtmf">DTMF Mode Switch</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="modesdtmf"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <span class="radioset">
                                                        <input type="radio" name="modedtmf" id="modedtmfyes" value="d" <?php echo (!isset($modedtmf) || $modedtmf == 'd' ? "CHECKED" : ""); ?>>
                                                        <label for="modedtmfyes"><?php echo _("Yes"); ?></label>
                                                        <input type="radio" name="modedtmf" id="modedtmfno" value="" <?php echo ($modedtmf == '' ? "CHECKED" : ""); ?>>
                                                        <label for="modedtmfno"><?php echo _("No"); ?></label>
                                                        
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span id="modesdtmf-help" class="help-block fpbx-help-block">
                                                    <?php echo _("<div>Override the typical numeric DTMF functionality and instead use DTMF to switch between spy modes.</div>
                                                    <div>4 - Spy Mode</div><p>
                                                    <div>5 - Whisper Mode</div><p>
                                                    <div>6 - Barge Mode</div>
                                                    "); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="auth">Authenticate</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="passcode"></i>
                                                </div>
                                                <div class="col-md-9"><input type="text" name="passcode" class="form-control " id="passcode" size="35"   tabindex=""  value=""></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                    <span id="passcode-help" class="help-block fpbx-help-block general-find">
                                        <?= _("Set an authentication PIN that must be entered to allow this spy code to be used.") ?>
                                    </span>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="auth">Allowed Spiers</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="restricted"></i>
                                                </div>
                                                <div class="col-md-9">
                                                <select name = "spiers[]" id="spiers" class="multiple" multiple="multiple" style="width:50%">
                                                        <?= $spier_list ?>					
                                                    </select>
                                                
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="restricted-help" class="help-block fpbx-help-block general-find"><?= _("Select the extensions that will be allowed to spy on calls using this spy code. If no extensions are selected, anyone can use this spy code.") ?> </span>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="enforcelist">Enforce Spy Targets</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="enforcelist"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <select name = "enforced[]" id="enforced" multiple="multiple" style="width:50%">
                                                        <?= $qsagentlist ?>					
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="enforcelist-help" class="help-block fpbx-help-block"><?= _("Enforce a target list of extensions who can be spied on. If no target extensions (or groups) are set, any channel/extension can be spied on depending on mode.") ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="spygroups">Enforce Spy Target Groups</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="spygroups"></i>
                                                </div>
                                                <div class="col-md-9">
                                                <select name = "spygroups[]" id="spygroups" class="multiple" multiple="multiple" style="width:50%">
                                                        <?= $groups_list ?>					
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="spygroups-help" class="help-block fpbx-help-block general-find">
                                            <?= _("Enforce Spy Target Groups. Only extensions in the selected groups can be spied on. If no target groups (or extensions) are set, any channel/extension can be spied on depending on mode.") ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="section-title" data-for="advcallspy_options">
                                <h3><i class="fa fa-minus"></i> <?php echo _('Advanced Settings')?></h3>
                            </div>
                            <div class="section" data-id="advcallspy_options">
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="eventlog">Event Logging</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="eventlog"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="eventlog" id="eventlogyes" value="1" <?php echo (!isset($eventlog) || $eventlog == 1 ? "CHECKED" : ""); ?>>
                                                            <label for="eventlogyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="eventlog" id="eventlogno" value="0" <?php echo ($eventlog == 0 ? "CHECKED" : ""); ?>>
                                                            <label for="eventlogno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="eventlog-help" class="help-block fpbx-help-block"><?= _("Enable Channel Event Logging (CEL) for this spy code."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="genhint">Generate Hint</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="genhint"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="genhint" id="genhintyes" value="1" <?php echo (!isset($genhint) || $genhint == 1 ? "CHECKED" : ""); ?>>
                                                            <label for="genhintyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="genhint" id="genhintno" value="0" <?php echo ($genhint == 0 ? "CHECKED" : ""); ?>>
                                                            <label for="genhintno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="genhint-help" class="help-block fpbx-help-block"><?= _("Generate a hint for this spy code."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="bridged">Answered (Bridged) Calls</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="bridged"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="bridged" id="bridgedyes" value="1" <?php echo (!isset($bridged) || $bridged == 1 ? "CHECKED" : ""); ?>>
                                                            <label for="bridgedyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="bridged" id="bridgedno" value="0" <?php echo ($bridged == 0 ? "CHECKED" : ""); ?>>
                                                            <label for="bridgedno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="bridged-help" class="help-block fpbx-help-block"><?php echo _("Only spy on answered/bridged calls."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="sayname">Say Name</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="sayname"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="sayname" id="saynameyes" value="n" <?php echo (!isset($sayname) || $sayname == 'n' ? "CHECKED" : ""); ?>>
                                                            <label for="saynameyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="sayname" id="saynameno" value="" <?php echo ($sayname == '' ? "CHECKED" : ""); ?>>
                                                            <label for="saynameno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="sayname-help" class="help-block fpbx-help-block"><?php echo _("Say the name of the person being spied on if that person has recorded his/her name."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="qmode">Quiet Mode</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="qmode"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="qmode" id="qmodeyes" value="y" <?php echo ($quietmode == 'y' ? "CHECKED" : ""); ?>>
                                                            <label for="qmodeyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="qmode" id="qmodeno" value="" <?php echo ($quietmode == '' ? "CHECKED" : ""); ?>>
                                                            <label for="qmodeno"><?php echo _("No"); ?></label>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="qmode-help" class="help-block fpbx-help-block"><?= _("Don't play a beep when beginning to spy on a channel, or speak the selected channel name.") ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="bridged">Skip Playback</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="skip"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="skip" id="skipyes" value="s" <?php echo (!isset($skip) || $skip == 's' ? "CHECKED" : ""); ?>>
                                                            <label for="skipyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="skip" id="skipno" value="" <?php echo ($skip == '' ? "CHECKED" : ""); ?>>
                                                            <label for="skipno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="skip-help" class="help-block fpbx-help-block"><?php echo _("Skip the playback of the channel type"); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="stopspy">Stop on End</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="stopspy"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="stopspy" id="stopspyyes" value="S" <?php echo (!isset($skip) || $skip == 'S' ? "CHECKED" : ""); ?>>
                                                            <label for="stopspyyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="stopspy" id="stopspyno" value="" <?php echo ($skip == '' ? "CHECKED" : ""); ?>>
                                                            <label for="stopspyno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="stopspy-help" class="help-block fpbx-help-block"><?php echo _("Exit the application when there are no more channels/extensions to spy on."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="exithangup">Exit on Hangup</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="exithangup"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="radioset">
                                                            <input type="radio" name="exithangup" id="exithangupyes" value="E" <?php echo (!isset($exithangup) || $exithangup == 'E' ? "CHECKED" : ""); ?>>
                                                            <label for="exithangupyes"><?php echo _("Yes"); ?></label>
                                                            <input type="radio" name="exithangup" id="exithangupno" value="" <?php echo ($exithangup == '' ? "CHECKED" : ""); ?>>
                                                            <label for="exithangupno"><?php echo _("No"); ?></label>
                                                            
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span id="exithangup-help" class="help-block fpbx-help-block"><?php echo _("Exit the application if the currently spied-on channel/extension hangs up."); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="cycledtmf">Cycle DTMF</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="cycledtmf"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="form-control" id="cycledigit" name="cycledtmf">
                                                            <?php echo $cycleopts ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                        <span id="cycledtmf-help" class="help-block fpbx-help-block general-find"><?= _("Set the DTMF entry to use to cycle through spied targets. NOTE: This will override the default '*' cycle DTMF option.") ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="element-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-3 control-label">
                                                        <label for="exitdtmf">Exit DTMF</label>
                                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="exitdtmf"></i>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="form-control" id="exitdtmf" name="exitdtmf">
                                                            <?php echo $exitopts ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                        <span id="exitdtmf-help" class="help-block fpbx-help-block general-find"><?= _("Set the DTMF entry to use to exit the application.") ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
   
    
$( function() {
    $('#enforced, .multiple').multiselect({
		enableFiltering: true,
		includeSelectAllOption: true,
		enableCaseInsensitiveFiltering: true,
        buttonWidth: '300px',
    });
});
</script>