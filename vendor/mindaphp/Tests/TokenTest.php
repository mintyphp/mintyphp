<?php
namespace MindaPHP\Tests;

use MindaPHP\Token;

class TokenTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultToken()
    {
        $token = "eyJhbGciOiJOT05FIiwidHlwIjoiSldUIn0.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.";
        $claims = Token::getClaims(array('Authorization' => "Bearer $token"));
        $this->assertEquals(false, $claims);
    }

    public function testJwtIoExample()
    {
        Token::$secret = 'secret';
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ";
        $claims = Token::getClaims(array('Authorization' => "Bearer $token"));
        $this->assertEquals(array('sub' => '1234567890', 'name' => 'John Doe', 'admin' => true), $claims);
    }

    public function testNoneAlgorithm()
    {
        Token::$secret = 'secret';
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.";
        $claims = Token::getClaims(array('Authorization' => "Bearer $token"));
        $this->assertEquals(false, $claims);
    }

    public function testTokenGenerationAndVerification()
    {
        Token::$secret = 'secret';
        $claims = array('customer_id' => 4, 'user_id' => 2);
        $token = Token::getToken($claims);
        $claims = Token::getClaims(array('Authorization' => "Bearer $token"));
        $this->assertNotFalse($claims);
        $this->assertEquals(4, $claims['customer_id']);
        $this->assertEquals(2, $claims['user_id']);
    }

}
