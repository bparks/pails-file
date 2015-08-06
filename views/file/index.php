<?php if ($this->is_logged_in()): ?>
<script>
$(function () {
	var root = "<?php echo $this->model['directory']; ?>";

	function uploadFile(file, path) {
		var data = new FormData();
		data.append('file', file);
		$.ajax({
			type: "POST",
			url: "/file/upload" + path,
			xhr: function () {
				var myXHR = $.ajaxSettings.xhr();
				if (myXHR.upload) {
					myXHR.upload.addEventListener('progress', function () {
						//TODO
					}, false);
				}
				return myXHR;
			},
			data: data,
			success: function () {
				if (root === path)
					$('<div>').addClass('file')
					.append($('<a>').attr('href', '/files' + path + '/' + file.name).text(file.name))
					.appendTo($('.file-list'));
			},
			error: function () {
				//TODO
			},
			contentType: false,
			processData: false,
		});
	}

	function createDirectory(name, path, cb) {
		$.ajax({
			type: "POST",
			url: "/file/mkdir" + path,
			data: {
				'dir_name': name
			},
			success: function () {
				if (root === path)
					$('<div>').addClass('file dir')
					.append($('<a>').attr('href', '/file/index' + path + '/' + name).text(name))
					.appendTo($('.file-list'));

				cb();
			},
			error: function () {
				//TODO
			}
		});
	}

	function uploadEntry (entry, root) {
		var safe_root = root.endsWith('/') ? root : root + '/';
		if (entry.isFile) {
			entry.file(function (file) {
				uploadFile(file, root);
			})
		} else {
			//Is a Directory
			createDirectory(entry.name, root, function () {
				var reader = entry.createReader();
				var dir_name = entry.name;
				var entries = [];
				var done = function () {}

				var handle_results = function (results) {
					if (!results.length)
						return done();
					
					for (var j = 0; j < results.length; j++)
						uploadEntry(results[j], safe_root + dir_name);

					reader.readEntries(handle_results);
				}
				reader.readEntries(handle_results);
			});
		}
	}

	$.event.props.push( "dataTransfer" );
	$('.dropzone')
	.on('dragover', function (evt) {
		evt.preventDefault();
	})
	.on('dragenter', function (evt) {
		$('.dropzone').css({'border': 'dashed 1px green'})
	})
	.on('dragleave', function (evt) {
		$('.dropzone').css({'border': 'none'})
	})
	.on('drop', function (evt) {
		evt.preventDefault();
		$('.dropzone').css({'border': 'none'})
		if (evt.dataTransfer.items.length > 0)
		{
			var files = evt.dataTransfer.items;
			for (var i = 0; i < files.length; i++)
			{
				var entry = files[i].webkitGetAsEntry ? files[i].webkitGetAsEntry() : null;
				if (!entry) { //If this isn't Chrome, assume it's a file
					files[i].isFile = true;
					files[i].file = function (success, error) {
						return success(this.getAsFile());
					}
				}
				entry = entry || files[i];
				uploadEntry(entry, root);
			}
		}
	});
});
</script>
<?php endif; ?>
<h2>Files: <?php echo $this->model['directory']; ?></h2>
<div class="actions">
	<?php if ($this->is_logged_in()): ?>
	<a href="/file/mkdir<?php echo $this->model['directory']; ?>">create new directory</a> |
	<a href="/file/upload<?php echo $this->model['directory']; ?>">upload a file</a>
	<?php endif; ?>
</div>
<div class="dropzone">
	<?php if ($this->is_logged_in()): ?>
	<h3>Drag files here to upload them</h3>
	<?php endif; ?>
	<div class="file-list">
		<?php while (false !== ($entry = readdir($this->model['handle']))): ?>
			<?php if (substr($entry, 0, 1) == '.') continue; ?>
		<div class="file<?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? ' dir' : ''; ?>">
			<a href="<?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? '/file/index'.$this->model['directory'].'/'.$entry : '/files'.$this->model['directory'].'/'.$entry; ?>">
				<?php echo $entry; ?><?php echo is_dir(PUBLIC_FILES.$this->model['directory'].'/'.$entry) ? '/' : ''; ?>
			</a>
		</div>
		<?php endwhile; ?>
	</div>
</div>