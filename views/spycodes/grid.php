<?php
$dataurl = "ajax.php?module=advcallspy&command=getJSON&jdata=grid";
?>
<h1><?php echo _("Advanced Call Spy")?></h1>
<div id="toolbar-all">
	<a href="?display=advcallspy&view=form" class="btn btn-default"><i class ="fa fa-plus"></i>&nbsp;<?php echo _("Add Spy Code");?></a>
    <a href="?display=spygroups" class="btn btn-default"><i class ="fa fa-list"></i>&nbsp;<?php echo _("List Spy Groups");?></a>
</div>
<table id="advspygrid" data-url="<?php echo $dataurl?>" data-cache="false" data-toolbar="#toolbar-all" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped">
	<thead>
		<tr>
			<th data-field="spycode" data-sortable="true"><?php echo _("Spy Code")?></th>
            <th data-field="description" data-sortable="false"><?php echo _("Description")?></th>
            <th data-field="status" data-sortable="true"><?php echo _("Status")?></th>
            <th data-field="spytype" data-sortable="true"><?php echo _("Spy Type")?></th>
			<th data-field="spycode" data-formatter="linkFormatter" class="col-md-2"><?php echo _("Actions")?></th>
		</tr>
	</thead>
</table>
<script>

function linkFormatter(value, row, index){
	var html = '<a href="?display=advcallspy&view=form&extdisplay='+value+'"><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;<a href="?display=advcallspy&action=delete&extdisplay='+value+'" class="delAction"><i class="fa fa-trash"></i></a>';
	return html;
}
</script>