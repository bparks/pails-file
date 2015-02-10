<h2>Create a directory</h2>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
<?php echo $this->input_for('dir_name', 'Directory Name', array()); ?>
<input type="submit" value="Create" />
</form>