<?php

/**
 * @author           Pierre-Henry Soria <hello@ph7builder.com>
 * @copyright        (c) 2026, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; See LICENSE.md and COPYRIGHT.md in the root directory.
 */

declare(strict_types=1);

namespace PH7\Test\Unit\Root;

use PH7\BlogModel;
use PH7\Framework\Config\Config;
use PH7\Framework\Mvc\Model\Engine\Db;
use PH7\NoteModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ArticleIdentityTest extends TestCase
{
    private \PDO $oDatabase;

    protected function setUp(): void
    {
        Config::getInstance()->values['cache']['enable.general.cache'] = false;
        $this->oDatabase = new \PDO('sqlite::memory:');
        $this->oDatabase->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->oDatabase->exec('CREATE TABLE ph7_blogs (blogId INTEGER, postId TEXT, title TEXT)');
        $this->oDatabase->exec('CREATE TABLE ph7_blogs_categories (categoryId INTEGER, blogId INTEGER)');
        $this->oDatabase->exec('CREATE TABLE ph7_notes (noteId INTEGER, profileId INTEGER, postId TEXT, approved INTEGER)');
        $this->oDatabase->exec('CREATE TABLE ph7_notes_categories (categoryId INTEGER, noteId INTEGER, profileId INTEGER)');
        $this->oDatabase->exec('CREATE TABLE ph7_members (profileId INTEGER, username TEXT, firstName TEXT, sex TEXT)');
        $this->oDatabase->exec("INSERT INTO ph7_blogs VALUES (3, 'sample-blog', 'Sample blog')");
        $this->oDatabase->exec("INSERT INTO ph7_notes VALUES (5, 17, 'sample-note', 1)");
        $this->oDatabase->exec("INSERT INTO ph7_members VALUES (17, 'member', 'Member', 'male')");

        (new \ReflectionProperty(Db::class, 'oInstance'))->setValue(null, (new \ReflectionClass(Db::class))->newInstanceWithoutConstructor());
        (new \ReflectionProperty(Db::class, 'oDb'))->setValue(null, $this->oDatabase);
        (new \ReflectionProperty(Db::class, 'sPrefix'))->setValue(null, 'ph7_');

        require_once PH7_PATH_SYS_MOD . 'blog/models/BlogModel.php';
        require_once PH7_PATH_SYS_MOD . 'note/models/NoteModel.php';
    }

    #[DataProvider('categoryProvider')]
    public function testBlogIdentityDoesNotDependOnCategoryLinks(bool $bHasCategory): void
    {
        if ($bHasCategory) {
            $this->oDatabase->exec('INSERT INTO ph7_blogs_categories VALUES (2, 3)');
        }

        $oPost = (new BlogModel())->readPost('sample-blog');

        self::assertIsObject($oPost);
        self::assertSame(3, $oPost->blogId);
        self::assertSame('Sample blog', $oPost->title);
        self::assertSame($bHasCategory ? 2 : null, $oPost->categoryId);
    }

    #[DataProvider('categoryProvider')]
    public function testNoteIdentityAndOwnerDoNotDependOnCategoryLinks(bool $bHasCategory): void
    {
        if ($bHasCategory) {
            $this->oDatabase->exec('INSERT INTO ph7_notes_categories VALUES (2, 5, 17)');
        }

        $oModel = new NoteModel();
        $oPost = $oModel->readPost('sample-note', 17);

        self::assertIsObject($oPost);
        self::assertSame(5, $oPost->noteId);
        self::assertSame(17, $oPost->profileId);
        self::assertSame('member', $oPost->username);
        self::assertSame($bHasCategory ? 2 : null, $oPost->categoryId);
        self::assertFalse($oModel->readPost('sample-note', 99));
        self::assertFalse($oModel->readPost('sample-note', 17, 0));
    }

    public static function categoryProvider(): array
    {
        return [
            'with category' => [true],
            'without category' => [false]
        ];
    }
}
