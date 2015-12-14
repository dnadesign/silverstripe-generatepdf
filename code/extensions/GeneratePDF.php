<?php

/**
* Generates a PDF version of the page upon publish
*/

class GeneratePDF extends DataExtension {

	private static $generated_pdf_path = 'assets/_generated_pdfs';

	public static function getPath() {
		return self::$generated_pdf_path;
	}

	/**
	 * Return the full filename of the pdf file, including path & extension
	 */
	public function getPdfFilename() {
		$baseName = sprintf('%s-%s', $this->owner->URLSegment, $this->owner->ID);

		$folderPath = Config::inst()->get($this->owner->Classname, 'generated_pdf_path');
		if(!$folderPath) $folderPath = self::$generated_pdf_path;
		if($folderPath[0] != '/') $folderPath = BASE_PATH . '/' . $folderPath;

		return sprintf('%s/%s.pdf', $folderPath, $baseName);
	}

	/*
	* Return link to either the PDF file itself (view) or a download action (download)
	* Default to 'View'
	* Use in templates
	*
	* @return String
	*/
	public function PdfLink($action = 'view') {
		$action = strtolower($action);
		$path = $this->owner->getPdfFilename();
		$url = null;

		switch ($action) {
			case 'view':
				$url = (Versioned::current_stage() == 'Live' && file_exists($path)) ?  Director::baseURL() . preg_replace('#^/#', '', Director::makeRelative($path)) : $this->owner->Link('downloadPDF');
				break;
			case 'download':
				$url = 	(Versioned::current_stage() == 'Live') ? $this->owner->Link('downloadPDF') : null;
				break;
			default:
				$url = null;
				break;
		}

		return $url;
	}

	/*
	 * Remove linked pdf when unpublishing the page,
	 * so it's no longer valid.
	 *
	 * @return boolean
	 */	 
	public function doUnpublish() {
		if(!parent::doUnpublish()) return false;

		$filepath = $this->owner->getPdfFilename();
		if(file_exists($filepath)) {
			unlink($filepath);
		}

		return true;
	}

	/**
	* Invoke the generatePDF method on Controller
	* And prompt user with status message
	*/
	public function doGeneratePDF() {
		// Delete previous version
		$this->owner->regeneratePDF();

		// Trigger PDF generation
		$controller = singleton($this->owner->ClassName.'_Controller');
		if (!$controller) {
			$this->owner->popupCMSError('No controller found!');
		}

		if ($controller->hasExtension('GeneratePDF_Controller')) {
			$success = $controller->generatePDF($this->owner);
			if ($success) {
				$this->owner->popupCMSError('Successfully generated PDF version.');
			} else {
				$this->owner->popupCMSError('Something went wrong while generating the PDF!');
			}
		} else {
			$this->owner->popupCMSError('The method "generatePDF()" does not exists on '.$this->owner->ClassName.'_Controller!');
		}
	}

	public function popupCMSError($message='The action is not allowed', $errorCode=403)	{
        header("HTTP/1.1 $errorCode $message");
        exit;
	}

	/**
	* Unlink the generated PDF, so to be recreated upon request
	*/
	public function regeneratePDF() {

		$filepath = $this->owner->getPdfFilename();
		if(file_exists($filepath)) {
			unlink($filepath);
		}		
	}

}


class GeneratePDF_Controller extends Extension {

	private static $allowed_actions = array('downloadPDF');

	/*
	* Trigger file download 
	* If does not exist, generate it.
	*/
	public function downloadPDF() {
		// We only allow producing live pdf. There is no way to secure the draft files.
		Versioned::reading_stage('Live');

		$path = $this->owner->dataRecord->getPdfFilename();
		if(!file_exists($path)) {
			$this->generatePDF();
		}

		return SS_HTTPRequest::send_file(file_get_contents($path), basename($path), 'application/pdf');		
	}

	/*
	* Render the page as PDF using wkhtmltopdf.
	*/
	public function generatePDF($record = null) {

		// Make sure we are on the live stage
		$currentStage = Versioned::current_stage();
		Versioned::reading_stage('Live');

		if (!$record) { $record = $this->owner->dataRecord; }

		$binaryPath = Config::inst()->get($record->ClassName, 'wkhtmltopdf_binary');
		if(!$binaryPath || !is_executable($binaryPath)) {
			if(defined('WKHTMLTOPDF_BINARY') && is_executable(WKHTMLTOPDF_BINARY)) {
				$binaryPath = WKHTMLTOPDF_BINARY;
			}
		}

		if(!$binaryPath) {
			user_error('Neither WKHTMLTOPDF_BINARY nor '.$pageType.'.wkhtmltopdf_binary are defined', E_USER_ERROR);
		}

		set_time_limit(60);

		// prepare the paths
		$pdfFile = $record->getPdfFilename();
		$bodyFile = str_replace('.pdf', '_pdf.html', $pdfFile);		

		// make sure the work directory exists
		if(!file_exists(dirname($pdfFile))) Filesystem::makeFolder(dirname($pdfFile));

		// Make sure we use the Live version of record
		$live_record = Versioned::get_by_stage($record->ClassName, 'Live')->byID($record->ID);
		// write the output of this page to HTML, ready for conversion to PDF
		Requirements::clear();		
		file_put_contents($bodyFile, $this->owner->render($live_record));

		// finally, generate the PDF
		$command = $binaryPath . ' --outline -B 40pt -L 20pt -R 20pt -T 20pt --encoding utf-8 ' .
			'--orientation Portrait --disable-javascript --print-media-type ';
		$retVal = 0;
		$output = array();
		exec($command . " \"$bodyFile\" \"$pdfFile\" &> /dev/stdout", $output, $return_val);

		// remove temporary file
		unlink($bodyFile);

		// Restores stage
		Versioned::reading_stage($currentStage);

		// output any errors
		if($return_val != 0) {
			user_error('wkhtmltopdf failed: ' . implode("\n", $output), E_USER_ERROR);
			return false;
		}

		return true;
	}
	
}
