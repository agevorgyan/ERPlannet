<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_can_fetch_armenian_translations(): void
    {
        $response = $this->getJson('/api/v1/translations/hy');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('locale', 'hy')
            ->assertJsonPath('translations.app_name', 'ERPlannet')
            ->assertJsonPath('translations.modules.crm', 'Հաճախորդներ (CRM)');
    }

    public function test_can_fetch_english_translations(): void
    {
        $response = $this->getJson('/api/v1/translations/en');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('translations.modules.production', 'Production & Recipes');
    }

    public function test_can_fetch_russian_translations(): void
    {
        $response = $this->getJson('/api/v1/translations/ru');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('locale', 'ru')
            ->assertJsonPath('translations.modules.warehouse', 'Склад и Остатки');
    }
}
