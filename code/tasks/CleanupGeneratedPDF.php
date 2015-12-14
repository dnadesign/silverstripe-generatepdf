<?php
class CleanupGeneratedPDF extends BuildTask {

	protected $title = 'Cleanup generated PDFs ';
	
	protected $description = 'Removes generated PDFs on the site, forcing a regeneration of all exports to PDF when users
		go to download them. This is most useful when templates have been changed so users should receive a new copy';

	public function run($request) {
		$path = sprintf('%s/%s', BASE_PATH, GeneratePDF::getPath());
		if(!file_exists($path)) return false;

		$files = scandir($path);
		if (count($files) - 2 === 0) return false;

		exec(sprintf('rm %s/*', $path), $output, $return_val);

		// output any errors
		if($return_val != 0) {
			user_error(sprintf('%s failed: ', get_class($this)) . implode("\n", $output), E_USER_ERROR);
		}
	}
}

class CleanupGeneratePagedPdfDailyTask extends DailyTask {

	public function process() {
		$task = new CleanupGeneratedPDF();
		$task->run(null);
	}

}

