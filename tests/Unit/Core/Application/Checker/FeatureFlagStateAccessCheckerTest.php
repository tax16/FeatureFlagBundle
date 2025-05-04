<?php

namespace App\Tests\Unit\Core\Application\Checker;

use Codeception\PHPUnit\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker\FeatureFlagStateAccessChecker;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception\FeatureFlagActiveException;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

class FeatureFlagStateAccessCheckerTest extends TestCase
{
    /**
     * @throws \Throwable
     * @throws Exception
     */
    public function testCheckFeatureActive()
    {
        $featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $logger = $this->createMock(ApplicationLoggerInterface::class);

        $featureFlagProvider->method('areAllFeaturesActive')->willReturn(true);

        $checker = new FeatureFlagStateAccessChecker($featureFlagProvider, $logger);

        $config = new IsFeatureActive(['feature1', 'feature2'], [], InvalidArgumentException::class);

        $checker->check($config);
    }

    public function testCheckFeatureInactiveWhenFeatureShouldBeActive()
    {
        $featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $logger = $this->createMock(ApplicationLoggerInterface::class);

        $featureFlagProvider->method('areAllFeaturesActive')->willReturn(false);

        $logger->expects($this->once())->method('error')->with('State of Feature(s) is not compliant');

        $checker = new FeatureFlagStateAccessChecker($featureFlagProvider, $logger);

        $config = new IsFeatureActive(['feature1', 'feature2'], [], InvalidArgumentException::class);

        $this->expectException(InvalidArgumentException::class);
        $checker->check($config);
    }

    public function testCheckCustomException()
    {
        $featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $logger = $this->createMock(ApplicationLoggerInterface::class);

        $featureFlagProvider->method('areAllFeaturesActive')->willReturn(false);

        $logger->expects($this->once())->method('error')->with('State of Feature(s) is not compliant');

        $checker = new FeatureFlagStateAccessChecker($featureFlagProvider, $logger);

        $exceptionClass = class_exists('MyCustomException') ? 'MyCustomException' : InvalidArgumentException::class;

        $config = new IsFeatureActive(['feature1'], [], $exceptionClass);

        $this->expectException($exceptionClass);
        $checker->check($config);
    }

    public function testCheckInvalidExceptionClass()
    {
        $featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $logger = $this->createMock(ApplicationLoggerInterface::class);

        $featureFlagProvider->method('areAllFeaturesActive')->willReturn(false);

        $logger->expects($this->once())->method('error')->with('State of Feature(s) is not compliant');

        $checker = new FeatureFlagStateAccessChecker($featureFlagProvider, $logger);

        $invalidExceptionClass = 'NonExistentException';

        $config = new IsFeatureActive(['feature1'], [], $invalidExceptionClass);

        $this->expectException(InvalidArgumentException::class);
        $checker->check($config);
    }

    public function testCheckExceptionWithoutConstructorParameters()
    {
        $featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $logger = $this->createMock(ApplicationLoggerInterface::class);

        $featureFlagProvider->method('areAllFeaturesActive')->willReturn(false);

        $logger->expects($this->once())->method('error')->with('State of Feature(s) is not compliant');

        $checker = new FeatureFlagStateAccessChecker($featureFlagProvider, $logger);

        $config = new IsFeatureActive(['feature1'], [], FeatureFlagActiveException::class);

        $this->expectException(FeatureFlagActiveException::class);
        $checker->check($config);
    }
}