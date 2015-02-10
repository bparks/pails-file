<?php
define('PUBLIC_FILES', 'files');

class FileController extends Pails\Controller
{
	use PailsAuthentication;
	use FormBuilder;

	public $before_actions = array(
		'require_login'
	);

	function index ($opts = array())
	{
		if (!$this->validate_directory()) return 404;

		$path = '/';
		if (count($opts) != 0)
			$path .= implode('/', $opts);

		$this->model = array('directory' => $path, 'handle' => opendir(PUBLIC_FILES.$path));
	}

	function mkdir ($opts = array())
	{
		$path = '/';
		if (count($opts) != 0)
			$path .= implode('/', $opts);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dir_name'] != '')
		{
			if (file_exists(PUBLIC_FILES.$path.'/'.$_POST['dir_name']))
			{
				$this->model = "Sorry. A file or directory with the name '".$_POST['dir_name']."' already exists.";
				return;
			}

			mkdir(PUBLIC_FILES.$path.'/'.$_POST['dir_name']);
			$this->model = '/file/index'.$path;
			return 302;
		}

		$this->model = $path;
	}

	function upload ($opts = array())
	{
		$path = '/';
		if (count($opts) != 0)
			$path .= implode('/', $opts);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']))
		{
			if (file_exists(PUBLIC_FILES.$path.'/'.$_FILES['file']['name']))
			{
				$this->model = "Sorry. A file or directory with the name '".$_POST['dir_name']."' already exists.";
				return;
			}

			//Do the uploading
			$destination = PUBLIC_FILES.$path.'/'.$_FILES['file']['name'];
			move_uploaded_file($_FILES['file']['tmp_name'], $destination);

			$this->model = '/file/index'.$path;
			return 302;
		}

		$this->model = $path;
	}

	/*
	function delete ($opts = array())
	{
		//TODO: This
	}
	*/

	private function validate_directory()
	{
		if (!file_exists(PUBLIC_FILES))
		{
			$this->model = "The 'files' directory does not exist";
			return false;
		}

		if (!is_dir(PUBLIC_FILES))
		{
			$this->model = "'files' is not a directory.";
			return false;
		}

		if (!is_writable(PUBLIC_FILES) && !defined(IGNORE_RO_FILES))
		{
			$this->model = "The 'files' directory is not writable. If this is intended, define the macro 'IGNORE_RO_FILES' in your config/application.php";
			return false;
		}

		return true;
	}
}