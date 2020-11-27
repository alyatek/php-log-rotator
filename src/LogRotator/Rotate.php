<?php

namespace LogRotator;

class Rotate
{
	public $regex = null, $path = null, $path_tar_save = null, $tar_file_name = null;

	public function __construct(
		string $regex = '/(.*\.log)/m',
		string $path = '/logs/',
		string $tar_file_name = 'MONTH',
		$path_tar_save = null
	) {
		$this->regex = $regex;

		if (substr($path, -1) === '/') {
			substr_replace($path, "", -1);
		}

		if (substr($path_tar_save, -1) === '/') {
			substr_replace($path_tar_save, "", -1);
		}

		$this->path = $path;
		$this->path_tar_save = $path_tar_save;

		$this->tar_file_name = $tar_file_name;
	}

	private function search_folder()
	{
		if (is_dir($this->path)) {
			return scandir($this->path);
		}

		return null;
	}

	private function match_files($files)
	{
		$matched = [];

		foreach ($files as $file) {
			preg_match($this->regex, $file, $matches, PREG_SET_ORDER, 0);

			if ($matches) {
				$matched[] = $file;
			}
		}

		return $matched;
	}

	private function compress()
	{
		$files = $this->search_folder();

		if (!$files) {
			throw new Exception('No files in folder');
		}

		$matched = $this->match_files($files);

		$compressed = $this->compress_files($matched);

		if($compressed){
			$remove = $this->remove_files($matched);
		}
	}

	private function compress_files($files)
	{
		$phar = new PharData("{$this->path_tar_save}-{$this->tar_file_name}.tar");

		foreach ($files as $file) {
			$phar->addFile($file, basename($file));
		}

		$phar->compress(Phar::GZ);

		return true;
	}

	private function remove_files($files)
	{
		foreach ($files as $file) {
			unlink($file);
		}
		unlink("{$this->path_tar_save}-{$this->tar_file_name}.tar");
	}
}
