<?php
class RefreshPDF extends BuildTask {

	protected $title = 'Cleanup generated PDFs and re-generate them';
	
	protected $description = 'Removes generated PDFs on the site, and re-generate them to make them available to the users straight-away';

	public function run($request) {

		// Clean up
		$task = new CleanupGeneratedPDF();
		$task->run(null);

		// Generate
		$pages = Page::get()->filterByCallback(function($page) {
			return $page->hasExtension('AutoGeneratePDF');
		});


		if ($pages) {			
			 foreach($pages as $page) {

			 	$controller = singleton($page->ClassName.'_Controller');

			 	if ($controller && $controller->hasExtension('GeneratePDF_Controller')) {
					$success = $controller->generatePDF($page);

					if ($success) {
						echo 'Generating PDF for '.$page->Title.PHP_EOL;
					}
				}
			 	
			 }
		}
	}
}