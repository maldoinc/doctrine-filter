<?php

namespace App\Tests\Doctrine\Filter\Action;

use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\Action\FilterAction;
use PHPUnit\Framework\TestCase;

class ActionListTest extends TestCase
{
    public function fromQueryStringSimpleEqualityDataProvider(): iterable
    {
        yield [
            'id=5&name=hello&page=3&foo=bar&ignored[maybe]=yes',

            // Now that simple equality is enabled we're pulling all sorts of actions, but they will
            // be ignored by DoctrineFilter as they are unmapped.
            [
                new FilterAction('id', 'eq', '5'),
                new FilterAction('name', 'eq', 'hello'),
                new FilterAction('page', 'eq', '3'),
                new FilterAction('foo', 'eq', 'bar'),
                new FilterAction('ignored', 'maybe', 'yes'),
            ],
            [],
        ];
    }

    /**
     * @dataProvider fromQueryStringSimpleEqualityDataProvider
     *
     * @return void
     */
    public function testFromQueryStringSimpleEquality(
        string $queryString,
        array $expectedFilters,
        array $expectedOrderBy
    ) {
        $actionList = ActionList::fromQueryString($queryString, null, true);

        $this->assertEquals($expectedFilters, $actionList->getFilterActions());
        $this->assertEquals($expectedOrderBy, $actionList->getOrderByActions());
    }
}
