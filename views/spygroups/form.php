<?php
$title = 'Spy Target Groups ';
$userslist = FreePBX::Core()->listUsers();
$extenslist = FreePBX::Advcallspy()->listGroupExtens($_REQUEST['extdisplay']);
$target_list = '';

foreach ($userslist as $result) {
    $selected = in_array($result[0], $extenslist ?? []) ? 'selected' : '';
	$target_list .= "<option value='" . $result[0] . "' {$selected}>" . $result[0] . " (" . $result[1] . ")</option>\n";
}
if(isset($_REQUEST['extdisplay']) && !empty($_REQUEST['extdisplay'])) {
    $spy = \FreePBX::Advcallspy()->getSpygroup($_REQUEST['extdisplay']);
    $title .= "Edit: " . $_REQUEST['extdisplay'];
    $action = 'edit';
    $spygroupid = $_REQUEST['extdisplay'];
} else {
    $title .= 'Add';
    $spygroupid = null;
}
$spygroup = $spy['spygroup'] ?? '';
$description = $spy['description'] ?? '';
    
    

for ($i=0; $i<=9; $i++ ) {
	$digits[]="$i";
}
$digits[] = '*';
$digits[] = '#';

?>
<div class="container-fluid">
	<h1><?php echo _("Advanced Call Spy")?></h1>
    <p>
	<h2><?= _($title) ?></h2>
	<div class="display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
                        <form autocomplete="off" name="edit" id="edit" action="?display=spygroups" method="post" class="fpbx-submit" data-fpbx-delete="<?php echo $delURL ?? '';?>">
                            <input type="hidden" name="display" value="spygroups">
                            <input type="hidden" name="spygroup_form" value="true">
                            <input type="hidden" name="form_action" value="<?= (isset($spygroupid) ? 'edit' : 'add') ?>">
                            <?php if(isset($spygroupid)) { ?> 
                                <input type="hidden" name="spygroup" value="<?= $spygroup ?>">
                            <?php } 
                         if(!isset($spygroupid)) { ?>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="form-group">
                                                        <div class="col-md-3 control-label">
                                                            <label class="control-label" for="spygroup">Group Name</label>
                                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="spygroup"></i>
                                                        </div>
                                                        <div class="col-md-9">
                                                            <input type="text" class="form-control" id="spygroup" name="spygroup" value="<?= $spygroup ?>" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <span id="spygroup-help" class="help-block fpbx-help-block">Name of the Spy Group. Name should only contain letters, numbers, dashes (-) or underscores (_)</span>
                                    </div>
                                </div>      
                            </div>
                            <?php }  ?>
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
                                    <span id="description-help" class="help-block fpbx-help-block general-find">Description of the Spy Group.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="element-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3 control-label">
                                                    <label for="groupmems">Group Targets</label>
                                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="groupmems"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <select name = "targets[]" id="grpmems" multiple="multiple" style="width:75%">
                                                        <?= $target_list ?>				
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                    <span id="groupmems-help" class="help-block fpbx-help-block general-find">Select extensions that will be spy targets associated with this group. Any Spy Code using this group will be able to spy on the selected target extensions</span>
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
$(document).ready(function() {
    $('#grpmems').multiselect({
		enableFiltering: true,
		includeSelectAllOption: true,
		enableCaseInsensitiveFiltering: true,
        buttonWidth: '300px',
    });
});
</script>