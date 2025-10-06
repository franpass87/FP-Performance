<?php

use FP\PerfSuite\ValueObjects\PerformanceScore;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

final class PerformanceScoreTest extends TestCase
{
    public function testConstructor(): void
    {
        $score = new PerformanceScore(85, ['cache' => 10, 'assets' => 15], ['Enable WebP']);
        
        $this->assertSame(85, $score->total);
        $this->assertSame(['cache' => 10, 'assets' => 15], $score->breakdown);
        $this->assertSame(['Enable WebP'], $score->suggestions);
    }

    public function testThrowsExceptionForInvalidScore(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PerformanceScore(150, []); // Over 100
    }

    public function testThrowsExceptionForNegativeScore(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PerformanceScore(-10, []);
    }

    public function testGetGrade(): void
    {
        $this->assertSame('A', (new PerformanceScore(95, []))->getGrade());
        $this->assertSame('B', (new PerformanceScore(80, []))->getGrade());
        $this->assertSame('C', (new PerformanceScore(65, []))->getGrade());
        $this->assertSame('D', (new PerformanceScore(50, []))->getGrade());
        $this->assertSame('F', (new PerformanceScore(30, []))->getGrade());
    }

    public function testGetColor(): void
    {
        $scoreA = new PerformanceScore(95, []);
        $this->assertNotEmpty($scoreA->getColor());
        $this->assertStringStartsWith('#', $scoreA->getColor());
    }

    public function testIsPassing(): void
    {
        $passing = new PerformanceScore(70, []);
        $failing = new PerformanceScore(50, []);
        
        $this->assertTrue($passing->isPassing());
        $this->assertFalse($failing->isPassing());
    }

    public function testGetEmoji(): void
    {
        $score = new PerformanceScore(95, []);
        $emoji = $score->getEmoji();
        
        $this->assertNotEmpty($emoji);
    }

    public function testCompareTo(): void
    {
        $score1 = new PerformanceScore(80, []);
        $score2 = new PerformanceScore(70, []);
        $score3 = new PerformanceScore(80, []);
        
        $this->assertGreaterThan(0, $score1->compareTo($score2));
        $this->assertLessThan(0, $score2->compareTo($score1));
        $this->assertSame(0, $score1->compareTo($score3));
    }

    public function testIsImprovedFrom(): void
    {
        $current = new PerformanceScore(85, []);
        $previous = new PerformanceScore(70, []);
        
        $this->assertTrue($current->isImprovedFrom($previous));
        $this->assertFalse($previous->isImprovedFrom($current));
    }

    public function testGetDelta(): void
    {
        $current = new PerformanceScore(85, []);
        $previous = new PerformanceScore(70, []);
        
        $this->assertSame(15, $current->getDelta($previous));
        $this->assertSame(-15, $previous->getDelta($current));
    }

    public function testFromArray(): void
    {
        $score = PerformanceScore::fromArray([
            'total' => 75,
            'breakdown' => ['test' => 10],
            'suggestions' => ['Improve cache'],
        ]);
        
        $this->assertSame(75, $score->total);
        $this->assertSame(['test' => 10], $score->breakdown);
    }

    public function testToArray(): void
    {
        $score = new PerformanceScore(85, ['cache' => 15]);
        $array = $score->toArray();
        
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('grade', $array);
        $this->assertArrayHasKey('color', $array);
        $this->assertSame(85, $array['total']);
        $this->assertSame('B', $array['grade']);
    }
}
