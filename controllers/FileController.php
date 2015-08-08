<?php

class Path implements \Iterator
{
	public $user = false;
	public $path = "/";
	private $components = array();

	function __construct($path = "/", $user = false)
	{
		$this->path = $path;
		$this->user = $user;
	}

	function displayPath()
	{
		$root = $this->user ? "/~" : "";

		return $root.($this->path);
	}

	function absolutePath()
	{
		return $this->rootPath() . $this->path;
	}

	function rootPath()
	{
		//This is an awful hack...
		$root = $this->user ?
			\Pails\File\Config::getUserFilePath() . '/' . (User::find($_SESSION['userPieUser']->user_id)->username) :
			\Pails\File\Config::getCommonFilePath();

		if (!file_exists($root))
			mkdir($root);

		return $root;
	}

	static function create ($parts)
	{
		if (count($parts) == 0)
			return new Path();

		$p = new Path();

		if ($parts[0] === '~')
		{
			$p->user = true;
			array_shift($parts);
		}

		$p->path = '/';
		if (count($parts) != 0)
			$p->path .= implode('/', $parts);

		return $p;
	}

	//Iterator implementation
	private $curIdx = 0;
	
	public function current ()
	{
		$tmp = array_slice($this->components, 0, $this->curIdx + 1);
		$ret = implode('/', $tmp);
		return ($this->user ? "/~" : "") . $ret . '/';
	}

	public function key ()
	{
		return $this->components[$this->curIdx];
	}

	public function next ()
	{
		$this->curIdx++;
	}

	public function rewind ()
	{
		$tmp = preg_replace('/\/\/*/', '/', substr($this->path, 1));
		$this->components = array_merge(array(""), array_filter(explode('/', $tmp)));
		$this->curIdx = 0;
	}
	public function valid ()
	{
		return $this->curIdx < count($this->components);
	}
}

class FileController extends Pails\Controller
{
	use PailsAuthentication;
	use FormBuilder;

	public $before_actions = array(
		'require_login'
	);

	private $commonFiles;
	private $userFiles;
	private $permitCommon;

	function __construct()
	{
		$this->commonFiles = \Pails\File\Config::getCommonFilePath();
		$this->userFiles = \Pails\File\Config::getUserFilePath();
		$this->permitCommon = \Pails\File\Config::isPermittedToManageCommon(User::find($this->current_user()->user_id)->username);
	}

	function index ($opts = array())
	{
		$path = Path::create($opts, $this->current_user());

		if (!$path->user && !$this->permitCommon)
		{
			$this->model = '/file/index/~'.$path->displayPath();
			return 302;
		}

		if (!$this->validate_directory($path)) return 404;
		if (!file_exists($path->absolutePath())) return 404;

		$this->model = array('directory' => $path, 'handle' => opendir($path->absolutePath()));
	}

	function mkdir ($opts = array())
	{
		$path = Path::create($opts, $this->current_user());

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['dir_name'] != '')
		{
			if (file_exists($path->absolutePath().'/'.$_POST['dir_name']))
			{
				$this->view = false;
				return array(
					'error' => "Sorry. A file or directory with the name '".$_POST['dir_name']."' already exists."
				);
			}

			mkdir($path->absolutePath().'/'.$_POST['dir_name']);
			$this->view = false;
			return array('status' => 'OK');
		}

		$this->model = $path->displayPath();
	}

	function upload ($opts = array())
	{
		$path = Path::create($opts, $this->current_user());

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']))
		{
			if (file_exists($path->absolutePath().'/'.$_FILES['file']['name']))
			{
				$this->view = false;
				return array(
					"error" => "Sorry. A file or directory with the name '".$_POST['dir_name']."' already exists."
				);
			}

			//Do the uploading
			$destination = $path->absolutePath().'/'.$_FILES['file']['name'];
			move_uploaded_file($_FILES['file']['tmp_name'], $destination);

			$this->view = false;
			return array('status' => 'OK');
		}

		$this->model = $path->displayPath();
	}

	/*
	function delete ($opts = array())
	{
		//TODO: This
	}
	*/

	private function validate_directory($path)
	{
		$root = $path->rootPath();

		if (!file_exists($root))
		{
			$this->model = "The '$root' directory does not exist";
			return false;
		}

		if (!is_dir($root))
		{
			$this->model = "'$root' is not a directory.";
			return false;
		}

		if (!is_writable($root) && !defined(IGNORE_RO_FILES))
		{
			$this->model = "The '$root' directory is not writable. If this is intended, define the macro 'IGNORE_RO_FILES' in your config/application.php";
			return false;
		}

		return true;
	}
}