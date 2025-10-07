<?php
/**
 * All Authenticate plugin tests
 *
 * @package       Cake.Test.Case.Controller.Component.Auth
 */
class AllAuthenticateTest extends CakeTestCase
{
    /**
     * Suite define the tests for this suite
     *
     * @return CakeTestSuite
     */
    public static function suite(): CakeTestSuite
    {
        $suite = new CakeTestSuite('All Authenticate test');

        $path = CakePlugin::path('Authenticate') . 'Test' . DS . 'Case' . DS;
        $suite->addTestDirectoryRecursive($path);

        return $suite;
    }
}
