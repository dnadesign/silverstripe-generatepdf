<?php

/**
* Generates a PDF version of the page when one on it's element is republished
*/

class PublishRefreshPDF extends DataExtension
{

    /*
    * Pages
    */
    public function onAfterPublish(&$original)
    {
        $type = $this->owner->class;
        $currentPage = null;

        if (is_subclass_of($type, 'SiteTree')) {
            $currentPage = $this->owner;
            if ($currentPage->exists()) {
                $this->owner->triggerGeneratePDF($currentPage);
            }
        }
    }

    /**
    * Versioned DataObject (Elemental)
    */
    public function onAfterVersionedPublish($fromStage, $toStage, $createNewVersion)
    {
        if ($toStage == "Live") {
            $type = $this->owner->class;
            $currentPage = null;

            if (is_subclass_of($type, 'BaseElement')) {
                $currentPage = $this->owner->getPage();
                if ($currentPage->exists()) {
                    $this->owner->triggerGeneratePDF($currentPage);
                }
            }
        }
    }

    /**
    * Call method regeneratePDF() on supplied page and its parents
    * if extend AutoGeneratePDF
    */
    public function triggerGeneratePDF($page = null)
    {
        if ($page) {
            $parents = self::getParents($page);

            foreach ($parents as $parent) {
                if ($parent->hasExtension('AutoGeneratePDF')) {
                    // Republish PDF
                    $parent->regeneratePDF();
                }
            }
        }
    }

    /**
    * Go throught tree upward to find all parents
    */
    private static function getParents(SiteTree $page)
    {
        $parents = array();

        $parent = $page->parent();

        while ($parent && $parent->exists()) {
            array_push($parents, $parent);
                // Keep looping
                $parent = $parent->parent();
        }

        return $parents;
    }
}
