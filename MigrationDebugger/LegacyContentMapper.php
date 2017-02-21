<?php

namespace xrow\bootstrapBundle\MigrationDebugger;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class LegacyContentMapper extends \eZ\Publish\Core\Persistence\Legacy\Content\Mapper {

    /**
     * Extracts Content objects (and nested) from database result $rows.
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     * @param array $nameRows
     *
     * @return \eZ\Publish\SPI\Persistence\Content[]
     */
    public function extractContentFromRows(array $rows, array $nameRows)
    {
        $versionedNameData = array();
        foreach ($nameRows as $row) {
            $contentId = (int)$row['ezcontentobject_name_contentobject_id'];
            $versionNo = (int)$row['ezcontentobject_name_content_version'];
            $versionedNameData[$contentId][$versionNo][$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
        }

        $contentInfos = array();
        $versionInfos = array();
        $fields = array();

        $extractHasError = false;

        foreach ($rows as $row) {
            $contentId = (int)$row['ezcontentobject_id'];
            $versionId = (int)$row['ezcontentobject_version_id'];
            $fieldId = (int)$row['ezcontentobject_attribute_id'];
            try {
                if (!isset($contentInfos[$contentId])) {
                    $contentInfos[$contentId] = $this->extractContentInfoFromRow($row, 'ezcontentobject_');
                }
                if (!isset($versionInfos[$contentId])) {
                    $versionInfos[$contentId] = array();
                }

                if (!isset($versionInfos[$contentId][$versionId])) {
                    $versionInfos[$contentId][$versionId] = $this->extractVersionInfoFromRow($row);
                }

                if (!isset($fields[$contentId][$versionId][$fieldId])) {
                    $fields[$contentId][$versionId][$fieldId] = $this->extractFieldFromRow($row);
                }
            } catch(\Exception $e) {
                if (php_sapi_name() == "cli") {
                    echo "\n**** Exception where ezcontentobject_id = " . $contentId . " and ezcontentobject_version_id = " . $versionId . " and ezcontentobject_attribute_id = " . $fieldId . ";\n";
                    echo $e->getMessage() ."\n";
                    $trace = $e->getTrace();
                    if (count($trace) > 1) {
                        echo "     Thrown by " . $trace[1]['class'] . "::" . $trace[1]['function'] . "\n";
                    }
                    echo "     ezcontentobject_attribute_data_text = \"" . $row['ezcontentobject_attribute_data_text'] . "\"\n";
                    if ($row['ezcontentobject_tree_main_node_id'] > 0) {
                        echo "     Full view: /content/view/full/" . $row['ezcontentobject_tree_main_node_id'] . "\n";
                    } else {
                        echo "     Full view: [Content does not have a location]\n";
                    }
                }
            }
        }
        $results = array();
        foreach ($contentInfos as $contentId => $contentInfo) {
            foreach ($versionInfos[$contentId] as $versionId => $versionInfo) {
                try {
                    $content = new Content();
                    $content->versionInfo = $versionInfo;
                    $content->versionInfo->names = $versionedNameData[$contentId][$versionInfo->versionNo];
                    $content->versionInfo->contentInfo = $contentInfo;
                    $content->fields = array_values($fields[$contentId][$versionId]);
                    $results[] = $content;
                } catch(\Exception $e) {
                    if (php_sapi_name() == "cli") {
                        echo "\n**** Exception where ezcontentobject_id = " . $contentId . " and ezcontentobject_version_id = " . $versionId . ";\n";
                        echo $e->getMessage() ."\n";
                        $trace = $e->getTrace();
                        if (count($trace) > 1) {
                            echo "     Thrown by " . $trace[1]['class'] . "::" . $trace[1]['function'] . "\n";
                        }
                    }
                }
            }
        }

        return $results;
    }

    private function throwMyException($row, $context)
    {
        throw new \Exception("This is deliberately thrown");
    }

    /**
     * Extracts a VersionInfo object from $row.
     *
     * This method will return VersionInfo with incomplete data. It is intended to be used only by
     * {@link self::extractContentFromRows} where missing data will be filled in.
     *
     * @param array $row
     * @param array $names
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    private function extractVersionInfoFromRow(array $row, array $names = array())
    {
        $versionInfo = new VersionInfo();
        $versionInfo->id = (int)$row['ezcontentobject_version_id'];
        $versionInfo->contentInfo = null;
        $versionInfo->versionNo = (int)$row['ezcontentobject_version_version'];
        $versionInfo->creatorId = (int)$row['ezcontentobject_version_creator_id'];
        $versionInfo->creationDate = (int)$row['ezcontentobject_version_created'];
        $versionInfo->modificationDate = (int)$row['ezcontentobject_version_modified'];
        $versionInfo->initialLanguageCode = $this->languageHandler->load($row['ezcontentobject_version_initial_language_id'])->languageCode;
        $versionInfo->languageIds = $this->extractLanguageIdsFromMask($row['ezcontentobject_version_language_mask']);
        $versionInfo->status = (int)$row['ezcontentobject_version_status'];
        $versionInfo->names = $names;

        return $versionInfo;
    }

    /**
     * Creates a Content from the given $struct and $currentVersionNo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    private function createContentInfoFromCreateStruct(CreateStruct $struct, $currentVersionNo = 1)
    {
        $contentInfo = new ContentInfo();

        $contentInfo->id = null;
        $contentInfo->contentTypeId = $struct->typeId;
        $contentInfo->sectionId = $struct->sectionId;
        $contentInfo->ownerId = $struct->ownerId;
        $contentInfo->alwaysAvailable = $struct->alwaysAvailable;
        $contentInfo->remoteId = $struct->remoteId;
        $contentInfo->mainLanguageCode = $this->languageHandler->load($struct->initialLanguageId)->languageCode;
        $contentInfo->name = isset($struct->name[$contentInfo->mainLanguageCode])
            ? $struct->name[$contentInfo->mainLanguageCode]
            : '';
        // For drafts published and modified timestamps should be 0
        $contentInfo->publicationDate = 0;
        $contentInfo->modificationDate = 0;
        $contentInfo->currentVersionNo = $currentVersionNo;
        $contentInfo->isPublished = false;

        return $contentInfo;
    }
}
