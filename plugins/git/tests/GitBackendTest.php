<?php
/*
 * Copyright (c) STMicroelectronics, 2011. All Rights Reserved.
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once dirname(__FILE__).'/../include/GitBackend.class.php';
Mock::generatePartial('GitBackend', 'GitBackendTestVersion', array('getDao', 'getDriver'));
Mock::generatePartial('GitBackend', 'GitBackend4SetUp', array('getDao', 'getDriver', 'deployPostReceive', 'setRepositoryPermissions', 'changeRepositoryAccess'));

Mock::generate('GitDriver');
Mock::generate('GitRepository');
Mock::generate('GitDao');
Mock::generate('Project');

class GitBackendTest extends UnitTestCase {

    protected $_globals;

    public function setUp() {
        $this->_globals = $GLOBALS;
        $this->fixturesPath = dirname(__FILE__).'/_fixtures';
    }

    public function tearDown() {
        $GLOBALS = $this->_globals;
        @unlink($this->fixturesPath.'/tmp/hooks/post-receive');
    }

    public function testIncludePostReceive() {
        // Copy reference hook to temporay path
        $repoPath = $this->fixturesPath.'/tmp';
        $hookPath = $repoPath.'/hooks/post-receive';
        copy($this->fixturesPath.'/hooks/post-receive', $hookPath);
             
        $driver = new MockGitDriver($this);

        $backend = new GitBackendTestVersion($this);
        $backend->setReturnValue('getDriver', $driver);

        $backend->deployPostReceive($repoPath);

        // verify that post-receive codendi hook is added
        $expect = '. '.$GLOBALS['sys_pluginsroot'].'git/hooks/post-receive 2>/dev/null';
        $lineFound = false;
        $lines = file($hookPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, $expect) !== false) {
                $lineFound = true;
            }
        }
        $this->assertTrue($lineFound, "post-receive hook must contains $expect");
    }

    public function testPostReceiveIsExecutable() {
        // Copy reference hook to temporay path
        $repoPath = $this->fixturesPath.'/tmp';
        $hookPath = $repoPath.'/hooks/post-receive';
        copy($this->fixturesPath.'/hooks/post-receive', $hookPath);

        $driver = new MockGitDriver($this);
        $driver->expectOnce('activateHook', array('post-receive', $repoPath));

        $backend = new GitBackendTestVersion($this);
        $backend->setReturnValue('getDriver', $driver);

        $backend->deployPostReceive($repoPath);
    }

    public function testAddMailingShowRev() {        
        $GLOBALS['sys_https_host'] = 'codendi.org';

        $repo = new MockGitRepository($this);
        $repo->setReturnValue('getPath', 'prj/repo.git');
        $repo->setReturnValue('getProjectId', 1750);
        $repo->setReturnValue('getId', 290);

        $driver = new MockGitDriver($this);
        $driver->expectOnce('setConfig', array('/var/lib/codendi/gitroot/prj/repo.git', 'hooks.showrev', "t=%s; git log --name-status --pretty='format:URL:    https://codendi.org/plugins/git/index.php/1750/view/290/?p=repo.git&a=commitdiff&h=%%H%%nAuthor: %%an <%%ae>%%nDate:   %%aD%%n%%n%%s%%n%%b' \$t~1..\$t"));

        $backend = new GitBackendTestVersion($this);
        $backend->setGitRootPath(GitBackend::GIT_ROOT_PATH);
        $backend->setReturnValue('getDriver', $driver);

        $backend->setUpMailingHook($repo);
    }

    /**
     * This is almost the same test than testAddMailingShowRev but
     * through the higher level setUpRepository.
     * What we want to be sure it that the right repository id is set.
     */
    public function testSetUpRepositoryConfigWithRightRepoId() {
        $GLOBALS['sys_https_host'] = 'codendi.org';

        $prj = new MockProject($this);
        $prj->setReturnValue('getId', 1750);

        // Use real git object because we need to store values (id)
        $repo = new GitRepository();
        $repo->setPath('prj/repo.git');
        $repo->setProject($prj);

        $dao = new MockGitDao($this);
        $dao->expectOnce('save');
        $dao->setReturnValue('save', 290); // The id we expect below

        $driver = new MockGitDriver($this);
        $driver->expectOnce('setConfig', array('/var/lib/codendi/gitroot/prj/repo.git', 'hooks.showrev', "t=%s; git log --name-status --pretty='format:URL:    https://codendi.org/plugins/git/index.php/1750/view/290/?p=repo.git&a=commitdiff&h=%%H%%nAuthor: %%an <%%ae>%%nDate:   %%aD%%n%%n%%s%%n%%b' \$t~1..\$t"));

        $backend = new GitBackend4SetUp($this);
        $backend->setGitRootPath(GitBackend::GIT_ROOT_PATH);
        $backend->setReturnValue('getDriver', $driver);
        $backend->setReturnValue('getDao', $dao);

        $backend->setUpRepository($repo);
    }
    
    public function testProcessMailToBeRemoved() {
        $backend = new GitBackend4SetUp($this);
        $output = "codendiadm@codendi.org,codendiadm@codendi.com,codendiadm@codendi.net";
        $mails = array('codendiadm@codendi.org', 'codendiadm@codendi.com', 'codendiadm@codendi.net');
        $this->assertEqual($backend->processMailToBeRemoved($output, $mails[0]), "codendiadm@codendi.com,codendiadm@codendi.net");
        $this->assertEqual($backend->processMailToBeRemoved($output, $mails[1]), "codendiadm@codendi.org,codendiadm@codendi.net");
        $this->assertEqual($backend->processMailToBeRemoved($output, $mails[2]), "codendiadm@codendi.org,codendiadm@codendi.com");
        $output = "codendiadm@codendi.org";
        $this->assertEqual($backend->processMailToBeRemoved($output, $mails[0]), "");
        $this->assertEqual($backend->processMailToBeRemoved($output, $mails[1]), "");
    }
}

?>
