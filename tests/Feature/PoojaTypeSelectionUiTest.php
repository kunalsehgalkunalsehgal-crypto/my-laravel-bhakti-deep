<?php

namespace Tests\Feature;

use App\Models\Admin\Pooja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class PoojaTypeSelectionUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_renders_both_enabled_types(): void
    {
        $pooja = $this->pooja();

        $this->bookingPage($pooja)
            ->assertSee('Live Admin Pooja')
            ->assertSee('Live description from admin.')
            ->assertSee('Rs.1,501')
            ->assertSee('Digital Admin Pooja')
            ->assertSee('Digital description from admin.')
            ->assertSee('Rs.751')
            ->assertSee('Pooja Mode')
            ->assertSee('Book an eligible local Pandit')
            ->assertSee('pooja_type: selectedPoojaType', false)
            ->assertSee("selectedPoojaType === 'digital' ? 5 : 4", false)
            ->assertDontSee('Digital Pooja booking will be available soon')
            ->assertDontSee('Standard Pooja')
            ->assertDontSee('Premium Pooja')
            ->assertDontSee('Special Pooja');
    }

    public function test_booking_page_renders_only_live_when_digital_is_disabled(): void
    {
        $pooja = $this->pooja(['digital_pooja_enabled' => false]);

        $this->bookingPage($pooja)
            ->assertSee('Live Admin Pooja')
            ->assertDontSee('Digital Admin Pooja');
    }

    public function test_booking_page_renders_only_digital_when_live_is_disabled(): void
    {
        $pooja = $this->pooja(['live_pooja_enabled' => false]);

        $this->bookingPage($pooja)
            ->assertDontSee('Live Admin Pooja')
            ->assertSee('Digital Admin Pooja')
            ->assertSee('Continue to Review');
    }

    private function pooja(array $overrides = []): Pooja
    {
        return Pooja::create(array_merge([
            'name' => 'Render Test Pooja',
            'slug' => 'render-test-pooja',
            'base_price' => 1001,
            'live_pooja_enabled' => true,
            'live_pooja_title' => 'Live Admin Pooja',
            'live_pooja_description' => 'Live description from admin.',
            'live_pooja_price' => 1501,
            'digital_pooja_enabled' => true,
            'digital_pooja_title' => 'Digital Admin Pooja',
            'digital_pooja_description' => 'Digital description from admin.',
            'digital_pooja_price' => 751,
            'mode' => 'Live + Replay',
            'status' => 'active',
        ], $overrides));
    }

    private function bookingPage(Pooja $pooja): TestView
    {
        $bufferLevel = ob_get_level();
        $view = $this->view('pages.pooja-booking-show', [
            'pooja' => $pooja->toBookingArray(),
            'selectedPandit' => null,
            'openReviewStep' => false,
        ]);

        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }

        return $view;
    }
}
