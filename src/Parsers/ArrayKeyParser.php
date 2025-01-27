<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Parsers;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class ArrayKeyParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    /**
     * Extract keys from the specified method of a given class.
     *
     * @param  string  $className  Fully qualified class name.
     * @param  string  $methodName  Method name to analyze.
     * @return array<string> List of keys in the array.
     *
     * @throws ReflectionException If the class or method doesn't exist.
     */
    public function extractKeys(string $className, string $methodName): array
    {
        $classCode = $this->getClassSourceCode($className);

        // Parse the class code into an AST
        $ast = $this->parser->parse($classCode);

        // Traverse the AST and extract keys
        $visitor = new ArrayKeyVisitor($methodName);
        $traverser = new NodeTraverser;
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->getKeys();
    }

    /**
     * Get the source code of a given class.
     *
     * @param  string  $className  Fully qualified class name.
     * @return string The source code of the class.
     *
     * @throws ReflectionException If the class doesn't exist.
     */
    private function getClassSourceCode(string $className): string
    {
        /**
         * @TODO User more efficient way by passing reflected class to the parser
         */
        $reflection = new ReflectionClass($className);
        $fileName = $reflection->getFileName();

        if (! $fileName || ! is_readable($fileName)) {
            throw new RuntimeException("Unable to read the file for class {$className}.");
        }

        return file_get_contents($fileName);
    }
}
