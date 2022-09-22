<?php

namespace Vellichore\Testing;

use PHPUnit\Framework\Assert as PHPUnit;
use Vellichore\Testing\Fluent\AssertableUri;
use Illuminate\Testing\TestResponse as BaseTestResponse;

class TestResponse
{
    /** @var BaseTestResponse $response */
    protected $response;

    public function __construct(BaseTestResponse $testResponse)
    {
        $this->response = $testResponse;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string|callable|null $value
     * @return BaseTestResponse
     */
    public function assertRedirect($value = null)
    {
        PHPUnit::assertTrue(
            $this->response->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->response->getStatusCode()),
        );

        if (!is_null($value)) {
            $this->assertLocation($value);
        }

        return $this->response;
    }

    /**
     * Get an assertion message for a status assertion containing extra details when available.
     *
     * @param string|int $expected
     * @param string|int $actual
     * @return string
     */
    protected function statusMessageWithDetails($expected, $actual)
    {
        return "Expected response status code [{$expected}] but received {$actual}.";
    }

    /**
     * Assert that the current location header matches the given URI.
     *
     * @param string|callable $value
     * @return BaseTestResponse
     */
    public function assertLocation($value)
    {
        $location = app('url')->to($this->response->headers->get('Location'));

        if (is_string($value)) {
            PHPUnit::assertEquals(app('url')->to($value), $location);
        } else {
            $assert = new AssertableUri($location);

            $value($assert);

            $assert->interacted();
        }

        return $this->response;
    }
}
