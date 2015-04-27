<?php

/**
* Generates a PDF version of the page upon publish
*/

class AutoGeneratePDF extends DataExtension {	

	/**
	 * Remove linked pdf when publishing the page,
	 * as it would be out of date.
	 * And generate new copy
	 */
	public function onAfterPublish(&$original) {
		if ($this->owner->hasExtension('GeneratePDF')) {
			$filepath = $this->owner->getPdfFilename();
			// Delete old file if exists
			if(file_exists($filepath)) {
				unlink($filepath);
			}
			// Generates new PDF
			$this->owner->regeneratePDF();
		}
	}	

}


