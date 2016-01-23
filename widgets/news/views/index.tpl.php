<?php foreach ($data['news'] as $news): ?>
<div class="media">
	<?php if ($news['user_id']): ?>
	<a href="<?php echo url('members/'.$news['user_id'].'/'.url_title($news['username']).'.html'); ?>" class="media-left">
		<img style="width: 48px; height: 48px;" src="<?php echo $NeoFrag->user->avatar($news['avatar'], $news['sex']); ?>" data-toggle="tooltip" title="<?php echo $news['username']; ?>" alt="" />
	</a>
	<?php else: ?>
	<div class="media-left">
		<img style="width: 48px; height: 48px;" src="<?php echo $NeoFrag->user->avatar(); ?>" data-toggle="tooltip" title="<?php echo i18n('guest'); ?>" alt="" />
	</div>
	<?php endif; ?>
	<div class="media-body">
		<h4 class="media-heading"><a href="<?php echo url('news/'.$news['news_id'].'/'.url_title($news['title']).'.html'); ?>"><?php echo $news['title']; ?></a></h4>
		<?php echo icon('fa-clock-o').' '.time_span($news['date']); ?> <a href="<?php echo url('news/'.$news['news_id'].'/'.url_title($news['title']).'.html'); ?>#comments"><?php echo icon('fa-comment-o').' '.$NeoFrag->library('comments')->count_comments('news', $news['news_id']); ?></a>
	</div>
</div>
<?php endforeach; ?>