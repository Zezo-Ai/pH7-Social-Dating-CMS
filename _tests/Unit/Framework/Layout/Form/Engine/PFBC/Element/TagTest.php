<?php

/**
 * @author Pierre-Henry Soria <hello@ph7builder.com>
 * @license MIT License; See LICENSE.md and COPYRIGHT.md in the root directory.
 */

declare(strict_types=1);

namespace PH7\Test\Unit\Framework\Layout\Form\Engine\PFBC\Element;

require_once PH7_PATH_FRAMEWORK . 'Layout/Form/Engine/PFBC/Form.class.php';

use PFBC\Element\Tag;
use PFBC\Form;
use PFBC\Validation\Str;
use PH7\Form as AppForm;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testPlainTextboxFallbackPreservesSavedTagsAndValidation(): void
    {
        $oTag = new Tag('Tags', 'tags', [
            'id' => 'article_tags',
            'value' => 'coffee,weekend trips',
            'required' => 1,
            'validation' => new Str(2, AppForm::MAX_TAG_FIELD_LENGTH)
        ]);
        $sHtml = $this->render($oTag);

        $this->assertStringContainsString('type="text"', $sHtml);
        $this->assertStringContainsString('name="tags"', $sHtml);
        $this->assertStringContainsString('value="coffee,weekend trips"', $sHtml);
        $this->assertStringContainsString('maxlength="191"', $sHtml);
        $this->assertStringContainsString('required="required"', $sHtml);
        $this->assertStringContainsString('data-no-counter="true"', $sHtml);
        $this->assertStringContainsString('Separate tags with commas.', $sHtml);
        $this->assertTrue($oTag->isValid('coffee,weekend trips'));
    }

    public function testResourcesAreSharedButEachFieldInitializesIndependently(): void
    {
        $oForm = new Form('tag_test');
        $oForm->addElement(new Tag('Tags', 'tags'));
        $oForm->addElement(new Tag('More tags', 'more_tags'));
        $sHtml = $oForm->render(true);

        $this->assertSame(1, substr_count($sHtml, '/js/tag.js'));
        $this->assertSame(1, substr_count($sHtml, '/css/tag.css'));
        foreach ($oForm->getElements() as $oTag) {
            $this->assertStringContainsString('document.getElementById("' . $oTag->getID() . '")', $sHtml);
            $this->assertStringContainsString('id="' . $oTag->getID() . '_tag_help"', $sHtml);
        }
    }

    public function testValuesAndHelpAttributesCannotCreateMarkup(): void
    {
        $sUnsafe = '"><img src=x onerror=alert(1)>';
        $sHtml = $this->render(new Tag('Tags', 'tags', [
            'id' => 'tags',
            'value' => $sUnsafe,
            'placeholder' => $sUnsafe,
            'data-tag-remove' => $sUnsafe
        ]));

        $this->assertStringNotContainsString('<img', $sHtml);
        $this->assertSame(3, substr_count($sHtml, htmlspecialchars($sUnsafe, ENT_QUOTES)));
    }

    public function testExistingDescriptionAndExplicitLimitArePreserved(): void
    {
        $oTag = new Tag('Tags', 'tags', [
            'id' => 'tags',
            'aria-describedby' => 'custom_help',
            'maxlength' => 20,
            'validation' => new Str(2, 191),
            'readonly' => 'readonly'
        ]);
        $sHtml = $this->render($oTag);
        $this->assertStringContainsString('aria-describedby="custom_help tags_tag_help"', $sHtml);
        $this->assertStringContainsString('maxlength="20"', $sHtml);
        $this->assertStringContainsString('readonly="readonly"', $sHtml);
        $this->assertSame($sHtml, $this->render($oTag));
    }

    public function testSessionBackedValidationRejectsOverlongLists(): void
    {
        $oForm = new Form('tag_validation');
        $oForm->addElement(new Tag('Tags', 'tags', ['validation' => new Str(2, AppForm::MAX_TAG_FIELD_LENGTH)]));
        $oForm->render(true);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = ['tags' => str_repeat('🌷', 191)];
        $this->assertTrue(Form::isValid('tag_validation'));
        $_POST = ['tags' => str_repeat('🌷', 192)];
        $this->assertFalse(Form::isValid('tag_validation'));
        $this->assertSame($_POST['tags'], Form::getSessionValues('tag_validation')['tags']);
        $this->assertNotEmpty($_SESSION['pfbc']['tag_validation']['errors']['tags']);
    }

    public function testRequiredAndOptionalValidationAreNotLostOnSerialization(): void
    {
        $oOptional = unserialize(serialize(new Tag('Tags', 'tags', ['validation' => new Str(2, 191)])));
        $this->assertTrue($oOptional->isValid(''));
        $oRequired = unserialize(serialize(new Tag('Tags', 'tags', ['required' => 1, 'validation' => new Str(2, 191)])));
        $this->assertFalse($oRequired->isValid(''));
        $this->assertTrue($oRequired->isValid('travel,music'));
    }

    public function testBlogAndNoteFormsUseTheSchemaBoundedTagElement(): void
    {
        foreach (['blog/forms/AdminBlogForm.php', 'blog/forms/EditAdminBlogForm.php', 'note/forms/NoteForm.php', 'note/forms/EditNoteForm.php'] as $sPath) {
            $sCode = file_get_contents(PH7_PATH_SYS_MOD . $sPath);
            $this->assertMatchesRegularExpression('/new Tag\(\s*t\(\x27Tags:\x27\),\s*\x27tags\x27/', $sCode);
            $this->assertStringContainsString('Form::MAX_TAG_FIELD_LENGTH', $sCode);
        }
        $sSchema = file_get_contents(dirname(PH7_PATH_PROTECTED) . '/_install/data/sql/MySQL/pH7_Core.sql');
        $this->assertSame(2, substr_count($sSchema, 'tags varchar(' . AppForm::MAX_TAG_FIELD_LENGTH . ')'));
    }

    private function render(Tag $oTag): string
    {
        ob_start();
        $oTag->render();

        return ob_get_clean();
    }
}
