<h2>Upload a file</h2>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" enctype="multipart/form-data">
<?php echo $this->input_for('file', 'File', array('type' => 'file')); ?>
<input type="submit" value="Upload" />
</form>