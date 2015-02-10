<h2>Files: <?php echo $this->model['directory']; ?></h2>
<div class="actions">
	<?php if ($this->is_logged_in()): ?>
	<a href="/file/mkdir<?php echo $this->model['directory']; ?>">create new directory</a> |
	<a href="/file/upload<?php echo $this->model['directory']; ?>">upload a file</a>
	<?php endif; ?>
</div>
<div class="file-list">
	<?php while (false !== ($entry = readdir($this->model['handle']))): ?>
		<?php if (substr($entry, 0, 1) == '.') continue; ?>
	<div class="file<?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? ' dir' : ''; ?>">
		<a href="<?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? '/file/index'.$this->model['directory'].$entry : '/files'.$this->model['directory'].$entry; ?>">
			<?php echo $entry; ?><?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? '/' : ''; ?>
		</a>
	</div>
	<?php endwhile; ?>
</div>