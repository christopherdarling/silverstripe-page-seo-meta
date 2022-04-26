<?php

namespace ChristopherDarling\PageSeoMeta;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\HTML;

class PageExtension extends DataExtension
{
    private static $db = [
        'Meta_Noindex' => 'Boolean',
        'Meta_Nofollow' => 'Boolean',
    ];

    private static $defaults = [
        'Meta_Noindex' => false,
        'Meta_Nofollow' => false,
    ];

    private static $field_labels = [
        'Meta_Noindex' => 'Enable noindex?',
        'Meta_Nofollow' => 'Enable nofollow?',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $targetFieldlist = $fields;

        /** @var \SilverStripe\Forms\ToggleCompositeField $metadataField */
        $metadataField = $fields->fieldByName('Root.Main.Metadata');
        if ($metadataField) {
            $targetFieldlist = $metadataField;
        } 

        foreach (['Meta_Noindex', 'Meta_Nofollow'] as $newFieldName) {
            $targetFieldlist->push(
                CheckboxField::create($newFieldName, $this->owner->fieldLabel($newFieldName))
            );
        }
    }

    /**
     * Generate the meta[content] attribute value
     * @return string
     */
    private function generateMetaRobotsTagValue()
    {
        $metaValue = [];
        if ($this->owner->Meta_Noindex) $metaValue[] = 'noindex';
        if ($this->owner->Meta_Nofollow) $metaValue[] = 'nofollow';
        return implode(', ', $metaValue);
    }


    /** @return array */
    public function MetaComponents(&$tags)
    {
        $metaValue = $this->generateMetaRobotsTagValue();

        // Add robots meta tag if we have a value
        if (!empty($metaValue)) {
            $tags['robots'] = [
                'attributes' => [
                    'name' => 'robots',
                    'content' => $metaValue,
                ],
            ];
        }

        return $tags;
    }

    /** @return string */
    public function MetaTags(&$tagsHtml)
    {
        // SS 4.4+ has MetaComponents, older versions don't
        if (method_exists($this->owner, 'MetaComponents')) return $tagsHtml;

        // BC for SS <4.4
        $metaValue = $this->generateMetaRobotsTagValue();
        if (!empty($metaValue)) {
            $tagsHtml .= "\n";
            $tagsHtml .= HTML::createTag('meta', array(
                'name' => 'robots',
                'content' => $metaValue,
            ));
        }
        
        return $tagsHtml;
    }
}
