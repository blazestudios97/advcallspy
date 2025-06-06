<div id="toolbar-rnav">
	<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <?php echo _("Actions")?> <span class="caret"></span>
	  </button>
		<ul class="dropdown-menu">
			<li><a href="?display=advcallspy"><i class="fa fa-list"></i>&nbsp;<?php echo _("List Spy Codes") ?></a></li>
			<li><a href="?display=spygroups"><i class="fa fa-list"></i>&nbsp;<?php echo _("List Spy Target Groups") ?></a></li>
			<li><a href="?display=spygroups&amp;view=form"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add Spy Target Group") ?></a></li>
		</ul>
    </div>
</div>
<table id="spycodernav"
			 data-url="ajax.php?module=advcallspy&amp;command=getJSON&amp;jdata=sggrid"
			 data-cache="false"
             data-toolbar="#toolbar-rnav"
			 data-toggle="table"
			 data-search="true"
			 class="table">
	 <thead>
					 <tr>
					 <th data-field="spygroup" data-sortable="true"><?php echo _("Spy Target Groups")?></th>
                     <th data-field="exten" data-sortable="true" data-visible="false"><?php echo _("Code")?></th>
			 </tr>
	 </thead>
</table>
<script type="text/javascript">
	$("#spycodernav").on('click-row.bs.table',function(e,row,elem){
		window.location = '?display=spygroups&view=form&extdisplay='+row['exten'];
	})
</script>