<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman utama hanya bisa diakses setelah login.
     */
    public function test_halaman_utama_mengarahkan_ke_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
