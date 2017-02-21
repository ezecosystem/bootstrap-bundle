<?php

namespace xrow\bootstrapBundle\MigrationDebugger;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater;

/**
 * Class to update content objects to a new type version.
 */
class LegacyContentUpdater extends ContentUpdater
{
    /**
     * Applies all given updates.
     *
     * @param mixed $contentTypeId
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action[] $actions
     */
    public function applyUpdates($contentTypeId, array $actions)
    {
        if (empty($actions)) {
            return;
        }

        foreach ($this->getContentIdsByContentTypeId($contentTypeId) as $contentId) {
            foreach ($actions as $action) {
                try {
                    $action->apply($contentId);
                } catch (\Exception $e) {
                    if (php_sapi_name() == "cli") {
                        echo "**** Exception where contentId = " . $contentId . ";\n";
                        echo $e->getMessage() ."\n";
                        $trace = $e->getTrace();
                        if (count($trace) > 1) {
                            echo "     Thrown by " . $trace[1]['class'] . "::" . $trace[1]['function'] . "\n";
                        }
                    }
                }
            }
        }
    }
}
