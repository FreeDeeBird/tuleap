<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloada67e58524084786b8244e5a6f30fb047($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'databaseinitialization' => '/DatabaseInitialisation.class.php',
            'dbtestaccess' => '/DBTestAccess.php',
            'fakedataaccessresult' => '/TestHelper.class.php',
            'ongoingintelligentstub' => '/MockBuilder.php',
            'rest_databaseinitialization' => '/rest/DatabaseInitialization.class.php',
            'rest_testdatabuilder' => '/rest/TestDataBuilder.php',
            'restbase' => '/rest/RestBase.php',
            'signaturemapwithdefault' => '/MockBuilder.php',
            'soap_testdatabuilder' => '/soap/TestDataBuilder.php',
            'soapbase' => '/soap/SOAPBase.php',
            'test\\rest\\cache' => '/rest/Cache.php',
            'test\\rest\\requestwrapper' => '/rest/RequestWrapper.php',
            'test\\rest\\tracker\\tracker' => '/rest/tracker/Tracker.php',
            'test\\rest\\tracker\\trackerfactory' => '/rest/tracker/TrackerFactory.php',
            'test\\rest\\tuleapconfig' => '/TuleapConfig.php',
            'testdatabuilder' => '/TestDataBuilder.php',
            'testhelper' => '/TestHelper.class.php',
            'tuleap\\rest\\artifactbase' => '/rest/ArtifactBase.php',
            'tuleap\\rest\\artifactfilebase' => '/rest/ArtifactFileBase.php',
            'tuleap\\rest\\cardsbase' => '/rest/CardsBase.php',
            'tuleap\\rest\\milestonebase' => '/rest/MilestoneBase.php',
            'tuleap\\rest\\projectbase' => '/rest/ProjectBase.php',
            'tuleap\\rest\\trackerbase' => '/rest/TrackerBase.php',
            'tuleapdbtestcase' => '/TuleapDbTestCase.class.php',
            'tuleaperrortrappinginvoker' => '/TuleapErrorTrappingInvoker.class.php',
            'tuleaptestcase' => '/TuleapTestCase.class.php',
            'unimplementedthrower' => '/MockBuilder.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloada67e58524084786b8244e5a6f30fb047');
// @codeCoverageIgnoreEnd
