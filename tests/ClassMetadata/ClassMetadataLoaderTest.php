<?php

namespace Nadia\ElasticsearchODM\Tests\ClassMetadata;

use Nadia\ElasticsearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticsearchODM\Exception\InvalidAnnotationParameterException;
use Nadia\ElasticsearchODM\Exception\MissingRequiredAnnotationException;
use Nadia\ElasticsearchODM\Helper\ElasticsearchHelper;
use Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument1;
use Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument3;
use Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument8;
use PHPUnit\Framework\TestCase;

class ClassMetadataLoaderTest extends TestCase
{
    /**
     * @before
     */
    public function beforeTest()
    {
        $cacheDir = $this->getCacheDir();

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    public function testLoad()
    {
        foreach (['TestDocument1', 'TestDocument2', 'TestDocument6', 'TestDocument7'] as $documentName) {
            /** @var ClassMetadataLoader $loader */
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

            list($loader, $cacheFileName, $cacheFilePath) = array_values($this->createLoader($documentName, false));

            $loader->enableUpdateCache();
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);

            $loader->disableUpdateCache();
            $this->doTestLoad($loader, $documentName, $cacheFilePath, $cacheFileName);

            unlink($cacheFilePath);
        }
    }

    public function testLoadInvalidAnnotation1()
    {
        $this->expectException(MissingRequiredAnnotationException::class);

        list($loader) = array_values($this->createLoader('TestDocument3', true));

        $loader->load(TestDocument3::class);
    }

    public function testLoadInvalidAnnotation2()
    {
        $this->expectException(InvalidAnnotationParameterException::class);

        list($loader) = array_values($this->createLoader('TestDocument8', true));

        $loader->load(TestDocument8::class);
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
        $documentClassName = '\\Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\' . $documentName;
        $metadata = $loader->load($documentClassName);

        $this->assertFileExists($cacheFilePath);

        if (file_exists($cacheFilePath)) {
            $cachedMetadata = require $cacheFilePath;
            $expectedCachedMetadata = require __DIR__ . '/../Fixtures/cache/' . $cacheFileName;

            if (version_compare(ElasticsearchHelper::getClientVersion(), '7.0.0', '>=')) {
                $expectedCachedMetadata['template']['mappings'] =
                    $expectedCachedMetadata['template']['mappings']['log'];
            }

            unset($cachedMetadata['version']);
            unset($expectedCachedMetadata['version']);

            $this->assertEquals($expectedCachedMetadata, $cachedMetadata);
        }
    }

    private function createLoader($documentFileName, $updateCache)
    {
        $cacheDir = $this->getCacheDir();
        $cacheFileName = 'Nadia-ElasticsearchODM-Tests-Stubs-Document-' . $documentFileName . '.dev.php';
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
