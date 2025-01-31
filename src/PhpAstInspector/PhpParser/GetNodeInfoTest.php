<?php

declare(strict_types=1);

namespace PhpAstInspector\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Use_;
use PHPUnit\Framework\TestCase;

final class GetNodeInfoTest extends TestCase
{
    public function testItCollectsInfoAboutTheNode(): void
    {
        $info = $this->getInfo($this->getNode(<<<'CODE_SAMPLE'
<?php

function main(int $param) {
}
CODE_SAMPLE
        ));
        self::assertEquals(
            [
                'byRef' => 'false',
                // boolean: show string equivalent
                'name' => 'PhpParser\Node\Identifier',
                // object: show class
                'attrGroups' => '[]',
                // empty array
                'params' => '[PhpParser\Node\Param]',
                // array with objects
                'returnType' => 'null',
                'stmts' => '[]',
            ],
            $info
        );
    }

    public function testItDumpsUseTypes(): void
    {
        $info = $this->getInfo(new Node\Stmt\Use_([], Use_::TYPE_FUNCTION));

        self::assertEquals([
            'type' => 'TYPE_FUNCTION (2)',
            'uses' => '[]',
        ], $info);
    }

    public function testItDumpsIncludeTypes(): void
    {
        $info = (new GetNodeInfo())->forNode(
            new Node\Expr\Include_(new Node\Scalar\String_('file.php'), Include_::TYPE_REQUIRE)
        );

        self::assertEquals([
            'type' => 'TYPE_REQUIRE (3)',
            'expr' => 'PhpParser\Node\Scalar\String_',
        ], $info);
    }

    private function getNode(string $code): Node
    {
        return NodeNavigator::selectFirstFrom((new Parser())->parse($code))->currentNode();
    }

    /**
     * @return array<string,string>
     */
    private function getInfo(Node $node): array
    {
        return (new GetNodeInfo())->forNode($node);
    }
}
