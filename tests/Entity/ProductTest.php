<?php

namespace App\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Product.
 */
class ProductTest extends TestCase
{
    /**
     * Construct object
     */
    public function testCreateObject(): void
    {
        $product = new Product();
        $this->assertInstanceOf("App\Entity\Product", $product);
    }

    public function testGetSetName(): void
    {
        $product = new Product();
        $product->setName("Kalle");
        $this->assertEquals($product->getName(), "Kalle");
    }

    public function testGetSetValue(): void
    {
        $product = new Product();
        $product->setValue(5);
        $this->assertEquals($product->getValue(), 5);
    }

    public function testID(): void
    {
        $product = new Product();
        $this->assertEquals($product->getId(), 0);
    }
}