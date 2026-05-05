<?php

use App\Libraries\StudentRoleService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class StudentRoleServiceTest extends CIUnitTestCase
{
    public function testNormalizeDomainLowercasesAndPrefixesAtSymbol(): void
    {
        $service = new StudentRoleService();

        $this->assertSame('@siswa.uthm.edu.my', $service->normalizeDomain(' Siswa.UTHM.EDU.MY '));
    }

    public function testResolveStudentEmailDomainFallsBackToDefault(): void
    {
        $service = new StudentRoleService();

        $this->assertSame(
            StudentRoleService::DEFAULT_STUDENT_EMAIL_DOMAIN,
            $service->resolveStudentEmailDomain('')
        );
    }

    public function testEmailMatchesStudentDomainWithExplicitConfiguredValue(): void
    {
        $service = new StudentRoleService();

        $this->assertTrue(
            $service->emailMatchesStudentDomain('a123@student.uthm.edu.my', '@student.uthm.edu.my')
        );
        $this->assertTrue(
            $service->emailMatchesStudentDomain('a123@siswa.uthm.edu.my', 'siswa.uthm.edu.my')
        );
        $this->assertFalse(
            $service->emailMatchesStudentDomain('a123@uthm.edu.my', '@student.uthm.edu.my')
        );
    }
}
