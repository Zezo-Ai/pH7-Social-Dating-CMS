<?php

/**
 * @author Pierre-Henry Soria <hello@ph7builder.com>
 * @license MIT License; See LICENSE.md and COPYRIGHT.md in the root directory.
 */

namespace PFBC\Element;

/**
 * Comma-separated text without JavaScript, removable tags when enhanced.
 */
class Tag extends Textbox
{
    public function render()
    {
        $this->attributes['data-no-counter'] = 'true';
        $this->attributes += [
            'placeholder' => t('Add a tag'),
            'data-tag-help' => t('Type a tag and press Enter, or use commas.'),
            'data-tag-remove' => t('Remove tag: %0%'),
            'data-tag-limit' => t('Too many characters. Shorten or remove a tag.'),
            'data-tag-required' => t('Please add a tag.')
        ];
        $sHelpId = $this->getID() . '_tag_help';
        $aDescriptionIds = explode(' ', $this->attributes['aria-describedby'] ?? '');
        $aDescriptionIds[] = $sHelpId;
        $this->attributes['aria-describedby'] = trim(implode(' ', array_unique($aDescriptionIds)));

        parent::render();
        echo '<small class="pfbc-tags-help" id="', $this->filter($sHelpId), '">',
        $this->filter(t('Separate tags with commas.')), '</small>';
    }

    public function getCSSFiles(): array
    {
        return [$this->form->getResourcesPath() . '/css/tag.css'];
    }

    public function getJSFiles(): array
    {
        return [$this->form->getResourcesPath() . '/js/tag.js'];
    }

    public function jQueryDocumentReady()
    {
        $sInputId = json_encode($this->getID(), JSON_HEX_TAG);
        echo 'if (window.pH7TagField) { pH7TagField.init(document.getElementById(', $sInputId, ')); }';
    }
}
