<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Parsers;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ArrayKeyVisitor extends NodeVisitorAbstract
{
    private string $methodName;

    private array $keys = [];

    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function enterNode(Node $node): void
    {
        // Check if the node represents the target method
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === $this->methodName) {
            foreach ($node->getStmts() as $stmt) {
                if ($stmt instanceof Node\Stmt\Return_ && $stmt->expr instanceof Node\Expr\Array_) {
                    $this->extractArrayKeys($stmt->expr);
                }
            }
        }
    }

    /**
     * Extract keys from an array node.
     */
    private function extractArrayKeys(Node\Expr\Array_ $arrayNode): void
    {
        foreach ($arrayNode->items as $item) {
            if ($item->key instanceof Node\Scalar\String_) {
                $this->keys[] = $item->key->value;
            }
        }
    }

    /**
     * Get the extracted keys.
     *
     * @return array<string>
     */
    public function getKeys(): array
    {
        return $this->keys;
    }
}
