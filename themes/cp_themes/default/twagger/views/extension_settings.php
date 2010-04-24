<div class="tg">

	<table class="publish-tab-customisation">
		<thead>
			<tr>
				<th>Weblog</th>
				<th><?php echo $LANG->line("twagger_enable?") ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($weblog_query->num_rows > 0) : ?>
			<?php foreach($weblog_query->result as $count => $weblog) :?>
			<tr class="<?= ($count%2) ? 'odd' : 'even'; ?>">
				<th><?php echo $weblog["blog_title"] ?><div class="instructions">Select the weblog fields you want to parse for tags.</div></th>
				<td><label class="itoggle"><input type="checkbox" name="weblogs[]" value="<?=$weblog['weblog_id']?>" <?php if(in_array($weblog['weblog_id'], $enabled_weblogs)):?> checked="checked"<?php endif; ?> /></label>
				  <div class="settings">
<?php
$parse_fields = array();

$field_query = $DB->query("SELECT field_id FROM exp_twagger_settings WHERE weblog_id = ".$weblog["weblog_id"])->result;
foreach($field_query as $row) {
  $parse_fields[] = $row['field_id'];
}
$weblog_fields = $DB->query('SELECT * FROM exp_weblog_fields f LEFT JOIN exp_weblogs w ON w.field_group = f.group_id WHERE w.weblog_id = '.$weblog["weblog_id"]);
?>
			      <select name="fields[<?=$weblog['weblog_id']?>][]" multiple="multiple">
<?php foreach($weblog_fields->result as $weblog_field) : ?>
			        <option value="<?=$weblog_field['field_id']?>"<?php if(in_array($weblog_field['field_id'], $parse_fields)) : ?> selected="selected" <?php endif; ?>><?=$weblog_field['field_label']?></option>
<?php endforeach; ?>
			      </select>
			    </div>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr class="highlight">
				<td colspan="3"><?= $LANG->line("error_no_assigned_weblogs") ?></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<div><input type="submit" name="submit" value="Submit" /></div>
