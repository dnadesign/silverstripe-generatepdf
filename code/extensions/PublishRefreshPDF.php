<?php

/**
* Generates a PDF version of the page when one on it's element is republished
*/

class PublishRefreshPDF extends DataExtension {

	public function onAfterPublish(&$original) {

		$type = $this->owner->class;
		$currentPage = null;

		/*
		* Pages
		*/
		if (is_subclass_of($type, 'SiteTree')) {
			$currentPage = $this->owner;
		}
		/*
		* Widget
		*/
		else if(is_subclass_of($type, 'BaseElement')) {
			$currentPage = $this->owner->getPage();
		}

		/* Republish */
		if ($currentPage) {
			$parents = self::getParents($currentPage);

			foreach($parents as $parent) {
				if ($parent->hasExtension('AutoGeneratePDF')) {
					// Republish PDF
					$parent->regeneratePDF();
				}
			}	
		}
	}

	private static function getParents(SiteTree $page) {

		$parents = array();

			$parent = $page->parent();

			while($parent && $parent->exists()) {
				array_push($parents, $parent);
				// Keep looping
				$parent = $parent->parent(); 
			}

		return $parents;
	}
}