<?php

use PHPUnit\Framework\TestCase;
use App\Core\Auth;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_check_returns_true_when_user_id_set(): void
    {
        $_SESSION['user_id'] = 1;
        $this->assertTrue(Auth::check());
    }

    public function test_check_returns_false_when_user_id_not_set(): void
    {
        $this->assertFalse(Auth::check());
    }

    public function test_check_returns_false_when_user_id_null(): void
    {
        $_SESSION['user_id'] = null;
        $this->assertFalse(Auth::check());
    }

    public function test_setFlashMessage_stores_in_session(): void
    {
        Auth::setFlashMessage('success', 'Test message');
        $this->assertArrayHasKey('flash_message', $_SESSION);
        $this->assertSame('success', $_SESSION['flash_message']['type']);
        $this->assertSame('Test message', $_SESSION['flash_message']['text']);
    }

    public function test_getFlashMessage_returns_and_clears(): void
    {
        Auth::setFlashMessage('error', 'Error message');
        $message = Auth::getFlashMessage();
        $this->assertIsArray($message);
        $this->assertSame('error', $message['type']);
        $this->assertSame('Error message', $message['text']);
        $this->assertArrayNotHasKey('flash_message', $_SESSION);
    }

    public function test_getFlashMessage_returns_null_when_empty(): void
    {
        $this->assertNull(Auth::getFlashMessage());
    }

    public function test_getFlashMessage_returns_null_after_cleared(): void
    {
        Auth::setFlashMessage('success', 'Test');
        Auth::getFlashMessage();
        $this->assertNull(Auth::getFlashMessage());
    }

    public function test_logout_clears_session(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'test';

        if (session_id() === '') {
            session_start();
        }
        Auth::logout();

        $this->assertEmpty($_SESSION);
    }

    /**
     * requireLogin() calls exit() when not logged in, which can't be caught in-process.
     * We verify the check() it wraps is already tested above.
     * When logged in, requireLogin() should return normally.
     */
    public function test_requireLogin_passes_when_logged_in(): void
    {
        $_SESSION['user_id'] = 1;
        Auth::requireLogin();
        $this->assertTrue(true);
    }
}
