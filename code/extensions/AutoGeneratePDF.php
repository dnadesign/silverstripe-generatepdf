<?php

/**
* Generates a PDF version of the page upon publish
*/

class AutoGeneratePDF extends DataExtension
{

    /**
     * Remove linked pdf when publishing the page,
     * as it would be out of date.
     * And generate new copy
     */
    public function onAfterPublish(&$original)
    {
        if ($this->owner->hasExtension('GeneratePDF')) {
            // Generates new PDF
            $this->owner->doGeneratePDF();
        }
    }
}
