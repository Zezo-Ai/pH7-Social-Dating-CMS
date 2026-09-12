<?php

/**
 * @author         Pierre-Henry Soria <hello@ph7builder.com>
 * @copyright      (c) 2026, Pierre-Henry Soria and pH7Builder contributors.
 * @license        MIT License; See LICENSE.md and COPYRIGHT.md in the root directory.
 */

declare(strict_types=1);

namespace PH7\Test\Unit\App\System\Module\User\Forms\Processing;

use PH7\CredentialStatusCore;
use PH7\LoginFormProcess;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class LoginFormProcessTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testFailureHandlerAcceptsIntegerCredentialStatuses(): void
    {
        require_once PH7_PATH_SYS . 'core/classes/CredentialStatusCore.php';
        require_once PH7_PATH_SYS_MOD . 'user/forms/processing/LoginFormProcess.php';

        $oStatusParameter = (new ReflectionMethod(LoginFormProcess::class, 'handleLoginFailure'))->getParameters()[0];

        foreach ([CredentialStatusCore::INCORRECT_EMAIL_IN_DB, CredentialStatusCore::INCORRECT_PASSWORD_IN_DB] as $iStatus) {
            $this->assertSame(get_debug_type($iStatus), (string)$oStatusParameter->getType());
        }
    }

    public function testMissingGeoLocationDoesNotBlockLogin(): void
    {
        $sSource = file_get_contents(
            PH7_PATH_SYS_MOD . 'user/forms/processing/LoginFormProcess.php'
        );

        $this->assertIsString($sSource);
        $this->assertStringContainsString(
            'function isForeignLocation(int $iProfileId, ?string $sLocationName): bool',
            $sSource
        );
        $this->assertStringContainsString("\$sLocationName === null || \$sLocationName === ''", $sSource);
    }
}
