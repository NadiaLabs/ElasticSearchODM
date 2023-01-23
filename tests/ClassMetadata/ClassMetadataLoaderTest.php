<?php

namespace Nadia\ElasticSearchODM\Tests\ClassMetadata;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument1;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument3;
use PHPUnit\Framework\TestCase;

class ClassMetadataLoaderTest extends TestCase
{
    protected function setUp()
    {
        $cacheDir = $this->getCacheDir();

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    public function testLoad()
    {
        foreach (['TestDocument1', 'TestDocument2'] as $documentName) {
            list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, true));

            if (file_exists($cacheFilePath)) {
                unlink($cacheFilePath);
            }

            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);

            unlink($cacheFilePath);

            list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, true));
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);
            list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, true));
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);
            list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, false));
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);

            unlink($cacheFilePath);
        }
    }

    public function testLoadInvalidAnnotation()
    {
        $this->expectException(\InvalidArgumentException::class);

        list($loader) = array_values($this->createLoader('TestDocument3', true));

        $loader->load(TestDocument3::class);
    }

    public function testLoadWhenFileVersionChanged()
    {
        $documentName = 'TestDocument1';
        list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, true));

        // Make sure cache file is created
        $loader->load(TestDocument1::class);

        $metadata = require $cacheFilePath;
        $metadata['version'] = $metadata['version'] . '-changed';
        file_put_contents($cacheFilePath, '<?php return ' . var_export($metadata, true) . ";\n");

        list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, true));

        $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);

        unlink($cacheFilePath);
    }

    private function doTestLoad(ClassMetadataLoader $loader, $documentName, $cacheFilePath, $cacheFileName)
    {
        $documentClassName = '\\Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\' . $documentName;
        $metadata = $loader->load($documentClassName);

        $this->assertFileExists($cacheFilePath);

        if (file_exists($cacheFilePath)) {
            $cachedMetadata = require $cacheFilePath;
            $expectedCachedMetadata = require __DIR__ . '/../Fixtures/cache/' . $cacheFileName;

            unset($cachedMetadata['version']);
            unset($expectedCachedMetadata['version']);

            $this->assertEquals($expectedCachedMetadata, $cachedMetadata);
        }
    }

    private function createLoader($documentFileName, $updateCache)
    {
        $cacheDir = $this->getCacheDir();
        $cacheFileName = 'Nadia-ElasticSearchODM-Tests-Stubs-Document-' . $documentFileName . '.dev.php';
        $cacheFilePath = $cacheDir . '/' . $cacheFileName;

        return [
            'loader' => new ClassMetadataLoader($cacheDir, $updateCache, 'dev-', 'dev'),
            'cacheFileName' => $cacheFileName,
            'cacheFilePath' => $cacheFilePath,
        ];
    }

    private function getCacheDir()
    {
        return __DIR__ . '/../.cache';
    }
}
