<input type="hidden" name="module" value="<?php echo $data['module']; ?>" />
<input type="hidden" name="type" value="<?php echo $data['type']; ?>" />
<input type="hidden" name="id" value="<?php echo $data['id']; ?>" />
<?php foreach ($data['access'] as $category): ?>
<div class="table-responsive">
	<table class="table table-hover">
		<thead>
			<tr>
				<th class="col-md-8 col-sm-8 col-xs-8"><?php echo icon($category['icon']).' '.$category['title']; ?></th>
				<th class="col-md-4 col-sm-4 col-xs-8" colspan="2"><?php echo icon('fa-key'); ?> Accès</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($category['access'] as $name => $access): ?>
			<tr data-action="<?php echo $name; ?>">
				<td class="text-primary"><?php echo icon($access['icon']).' '.$access['title']; ?></td>
				<td class="access-count">
					<?php echo NeoFrag::loader()->access->count($data['module'], $name, $data['id']); ?>
				</td>
				<!--<td class="text-right"><?php echo button('#', 'fa-copy', 'Glissez pour copier les droits', 'primary'); ?></td>-->
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>