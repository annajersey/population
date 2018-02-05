

<form method="GET" id="form1">
	<div class="block">
	<label>How many groups of age: </label>
	<input name="group_num" size=4 type="text" value="<?php echo $group_num; ?>"><input  type="submit" value="Submit" onclick="window.sf=0">
	</div>
</form>

<div id="output">
<form method="POST" id="form2">
	<input name="group_num" type="hidden" value="<?php echo $group_num; ?>"><br>
	<?php for($i=1; $i<=$group_num; $i++){?>
		<div class="block">
		<?php echo '<b>Group #'.$i.'</b>'; ?><br>
		<label>Label of group </label>
		<input required class="required" value="<?php echo $_POST['groups'][$i]['label']; ?>" name="groups[<?php echo $i; ?>][label]"><br>
		<label>Initial number of population </label>
		<input required class="required" value="<?php echo $_POST['groups'][$i]['current']; ?>" name="groups[<?php echo $i; ?>][current]"><br>
		<!--<label>Range of age </label>
		<input value="<?php echo $_POST['groups'][$i]['range']; ?>" name="groups[<?php echo $i; ?>][range]"><br>-->
		<label><?php echo ($i==$group_num) ? 'Death ratio' : 'Ratio of aging';?></label>
		<input required class="required" value="<?php echo $_POST['groups'][$i]['aging-ratio']; ?>" name="groups[<?php echo $i; ?>][aging-ratio]"><br>
		<label>Ratio of births</label>
		<input class="birth" value="<?php echo $_POST['groups'][$i]['birth-ratio']; ?>" name="groups[<?php echo $i; ?>][birth-ratio]"><br>
		</div>
	<?php } ?>
		<div class="block clear">
		<br>
		<label>How many runs are desired? </label><input required name="runs" value="<?php echo $_POST['runs'] ? $_POST['runs'] : 10; ?>"><br>
		<label>Year of first run? </label><input name="year" value="<?php echo $_POST['year']; ?>"><br>
		<br>
		</div>
		<div class="block-bottom">
		<div class="message"></div>
		<input type="submit" value="Calculate" onclick="window.sf=0;">
		</div>

<?php if($result){ 
echo '<table>';
foreach($result as $row){
	echo '<tr>';
		foreach($row as $col){
			echo '<td>'.$col.'</td>';
		}	
	echo '</tr>';
}
echo '</table>'; ?>
<br>
<button name="format" value=1 onclick="window.sf=1">Download CSV</button> &nbsp <button onclick="window.sf=1" name="format" value=2>Download Excel</button> 

<?php } ?>

</form>
</div>
<br>
<small>This tool was created by Xavi Mir in Nov 2016 and it is licensed under Creative Commons Attribution 4.0 International License.</small>

<script>
jQuery('form').submit(function(event){
	if(window.sf) return;
	event.preventDefault();
	var valid=0;
	jQuery("input.birth").each(function(){if(jQuery(this).val() != "") valid+=1;});
	if(!valid && jQuery(this).attr('id')=='form2'){jQuery('.message').html('You have not filled any ratio of births'); return;}
	var datastring = jQuery(this).serialize();
	console.log(datastring);
		jQuery.ajax({
			type: "POST",
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: datastring+'&action=calculate',
			success: function(data) {
				data = data.substr(0, data.length-1); //Remove trailing 0 from wp ajax-response
				jQuery('#pop-container').html(data);
			},
			error: function(  jqXHR,  textStatus,  errorThrown) {
				console.log(errorThrown);
			}
			}); 
			
});
jQuery(document)
  .ajaxStart(function () {
    jQuery('#ajax-loader').show();
  })
  .ajaxStop(function () {
    jQuery('#ajax-loader').hide();
  });
</script>