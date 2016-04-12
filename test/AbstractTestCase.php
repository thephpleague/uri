<?php

namespace League\Uri\Test;

use PHPUnit_Framework_TestCase as TestCase;

class DebugInfoDummyClass
{
    public function __debugInfo()
    {
        return ['bar'];
    }
}

class AbstractTestCase extends TestCase
{
    protected function checkRequirements()
    {
        parent::checkRequirements();
        $annotations = $this->getAnnotations();
        foreach ($annotations as $type => $bag) {
            if (!array_key_exists('supportsDebugInfo', $bag)) {
                continue;
            }
            if (!$this->supportsDebugInfo()) {
                $this->markTestSkipped(
                    'your PHP/HHVM version does not support `__debugInfo`'
                );
            }
        }
    }

    protected function supportsDebugInfo()
    {
        ob_start();
        var_dump(new DebugInfoDummyClass());
        $res = ob_get_clean();

        return strpos($res, 'bar') !== false;
    }
}
