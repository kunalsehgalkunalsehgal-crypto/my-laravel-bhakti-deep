<?php

namespace Database\Seeders;

use App\Models\Admin\Hawan;
use Illuminate\Database\Seeder;

class HawanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->hawans() as $hawan) {
            Hawan::updateOrCreate(
                ['slug' => $hawan['slug']],
                $hawan
            );
        }
    }

    private function hawans(): array
    {
        $commonSlots = ['7:00 AM - 9:00 AM', '12:00 PM - 2:00 PM', '5:00 PM - 7:00 PM'];
        $commonDonations = [501, 1100, 2100, 5100];
        $samuhikDescription = 'Personalized sankalp, live access and digital receipt.';
        $specialDescription = 'Priority slot, extended ritual, family join and replay.';

        return [
            [
                'name' => 'Mahamrityunjaya Hawan',
                'slug' => 'mahamrityunjaya-hawan',
                'short_description' => 'Health, protection, long life aur spiritual strength ke liye live sacred fire ritual.',
                'full_description' => 'Mahamrityunjaya Hawan Lord Shiva ko samarpit ek powerful Vedic ritual hai. Isme Mahamrityunjaya Mantra ke saath ahuti di jati hai. Ye health support, protection, fear removal, mental peace aur family well-being ke liye kiya jata hai.',
                'featured_image' => 'assets/hawan/mahamrityunjaya-hawan.jpg',
                'base_price' => 2101,
                'samuhik_hawan_enabled' => true,
                'samuhik_hawan_title' => 'Samuhik Hawan',
                'samuhik_hawan_description' => $samuhikDescription,
                'samuhik_hawan_price' => 2101,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_description' => $specialDescription,
                'special_hawan_price' => 4601,
                'duration' => '75 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Health Support', 'Protection Energy', 'Mental Peace', 'Fear Removal', 'Spiritual Strength', 'Negative Energy Removal'],
                'included_items' => ['Personalized Sankalp', 'Live Hawan Access', 'Mahamrityunjaya Mantra Chanting', 'Real-time Ahuti Counter', 'Session Replay', 'Digital Certificate', 'Family Invite Link', 'Donation Receipt'],
                'session_timeline' => [['title' => 'Kund Sthapana', 'duration' => '8 min'], ['title' => 'Sankalp', 'duration' => '5 min'], ['title' => 'Mantra Japa', 'duration' => '32 min'], ['title' => 'Purna Ahuti', 'duration' => '10 min'], ['title' => 'Aarti & Blessing', 'duration' => '8 min']],
                'donation_options' => $commonDonations,
                'available_slots' => $commonSlots,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Lakshmi Hawan',
                'slug' => 'lakshmi-hawan',
                'short_description' => 'Prosperity, abundance aur financial blessings ke liye Lakshmi Mata ko samarpit sacred fire ritual.',
                'full_description' => 'Lakshmi Hawan ek shakti-maya ritual hai jo wealth, abundance aur prosperity lane ke liye kiya jata hai. Isme Lakshmi Mantra ke saath divine offerings di jati hain. Ye business growth, financial stability aur success ke liye auspicious mana jata hai.',
                'featured_image' => 'assets/hawan/lakshmi-hawan.jpg',
                'base_price' => 2501,
                'samuhik_hawan_enabled' => true,
                'samuhik_hawan_title' => 'Samuhik Hawan',
                'samuhik_hawan_description' => $samuhikDescription,
                'samuhik_hawan_price' => 2501,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_description' => $specialDescription,
                'special_hawan_price' => 5001,
                'duration' => '60 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Wealth & Prosperity', 'Business Growth', 'Financial Stability', 'Success Blessings', 'Auspicious Energy', 'Abundance'],
                'included_items' => ['Personalized Sankalp', 'Live Hawan Access', 'Lakshmi Mantra Chanting', 'Flower & Samagri Offerings', 'Session Replay', 'Digital Certificate', 'Family Invite Link', 'Donation Receipt'],
                'session_timeline' => [['title' => 'Kund Sthapana', 'duration' => '6 min'], ['title' => 'Sankalp', 'duration' => '5 min'], ['title' => 'Mantra Chanting', 'duration' => '28 min'], ['title' => 'Purna Ahuti', 'duration' => '8 min'], ['title' => 'Aarti & Blessing', 'duration' => '7 min']],
                'donation_options' => $commonDonations,
                'available_slots' => $commonSlots,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Griha Shanti Hawan',
                'slug' => 'griha-shanti-hawan',
                'short_description' => 'Ghar ki shanti, positive energy aur family well-being ke liye vidhi-purvak hawan ritual.',
                'full_description' => 'Griha Shanti Hawan ghar ke environment ko peaceful banane, negative energy ko door karne aur family ke liye positivity lane ke liye kiya jata hai. Ye ritual ghar ki shanti, grah dosh shanti, vastu shanti aur parivarik sukh ke liye mana jata hai.',
                'featured_image' => 'assets/hawan/griha-shanti-hawan.jpg',
                'base_price' => 1100,
                'samuhik_hawan_enabled' => true,
                'samuhik_hawan_title' => 'Samuhik Hawan',
                'samuhik_hawan_description' => $samuhikDescription,
                'samuhik_hawan_price' => 1100,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_description' => $specialDescription,
                'special_hawan_price' => 3600,
                'duration' => '45-90 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Peace at Home', 'Positive Energy', 'Family Well-being', 'Spiritual Protection', 'Better Environment'],
                'included_items' => ['Personalized Sankalp', 'Live Hawan Access', 'Griha Shanti Mantra', 'Real-time Ahuti Counter', 'Session Replay', 'Digital Certificate', 'Family Invite Link', 'Donation Receipt'],
                'session_timeline' => [['title' => 'Kund Sthapana', 'duration' => '8 min'], ['title' => 'Sankalp', 'duration' => '5 min'], ['title' => 'Griha Shanti Mantra', 'duration' => '30 min'], ['title' => 'Ahuti Ritual', 'duration' => '10 min'], ['title' => 'Aarti & Blessing', 'duration' => '8 min']],
                'donation_options' => $commonDonations,
                'available_slots' => $commonSlots,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Navgrah Hawan',
                'slug' => 'navgrah-hawan',
                'short_description' => 'Planetary peace, grah shanti aur life balance ke liye powerful Vedic ritual.',
                'full_description' => 'Navgrah Hawan nau grahas ke auspicious influence lane ke liye kiya jata hai. Isme har grah ka dedicated mantra aur ahuti hoti hai. Ye ritual astrological imbalances ko theek karne, life stability aur general well-being ke liye beneficial hai.',
                'featured_image' => 'assets/hanuman.jpg',
                'base_price' => 3501,
                'samuhik_hawan_enabled' => true,
                'samuhik_hawan_title' => 'Samuhik Hawan',
                'samuhik_hawan_description' => $samuhikDescription,
                'samuhik_hawan_price' => 3501,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_description' => $specialDescription,
                'special_hawan_price' => 6001,
                'duration' => '90 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Planetary Balance', 'Grah Shanti', 'Life Stability', 'Astrological Harmony', 'Protection Energy'],
                'included_items' => ['Personalized Sankalp', 'Live Hawan Access', 'Navgrah Mantra Chanting', 'Real-time Counter', 'Session Replay', 'Digital Certificate', 'Astrological Report', 'Donation Receipt'],
                'session_timeline' => [['title' => 'Kund Sthapana', 'duration' => '8 min'], ['title' => 'Sankalp', 'duration' => '5 min'], ['title' => 'Navgrah Mantra', 'duration' => '55 min'], ['title' => 'Purna Ahuti', 'duration' => '10 min'], ['title' => 'Aarti & Blessing', 'duration' => '10 min']],
                'donation_options' => $commonDonations,
                'available_slots' => $commonSlots,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Satyanarayan Hawan',
                'slug' => 'satyanarayan-hawan',
                'short_description' => 'Truth, dharma aur auspicious blessings ke liye sacred Satyanarayan ritual.',
                'full_description' => 'Satyanarayan Hawan ek sacred ritual hai jo truth ke paath par chalne, dharma follow karne aur auspicious blessings pane ke liye kiya jata hai. Ye family welfare, success aur spiritual growth ke liye powerful hai.',
                'featured_image' => 'assets/hawan/satyanarayan-hawan.jpg',
                'base_price' => 2201,
                'samuhik_hawan_enabled' => true,
                'samuhik_hawan_title' => 'Samuhik Hawan',
                'samuhik_hawan_description' => $samuhikDescription,
                'samuhik_hawan_price' => 2201,
                'special_hawan_enabled' => true,
                'special_hawan_title' => 'Special Hawan',
                'special_hawan_description' => $specialDescription,
                'special_hawan_price' => 4701,
                'duration' => '60 min',
                'mode' => 'Live + Replay',
                'benefits' => ['Truth & Dharma', 'Auspicious Blessings', 'Family Welfare', 'Success & Prosperity', 'Spiritual Growth'],
                'included_items' => ['Personalized Sankalp', 'Live Hawan Access', 'Satyanarayan Mantra', 'Real-time Ahuti Counter', 'Session Replay', 'Digital Certificate', 'Family Invite Link', 'Donation Receipt'],
                'session_timeline' => [['title' => 'Kund Sthapana', 'duration' => '6 min'], ['title' => 'Sankalp', 'duration' => '5 min'], ['title' => 'Satyanarayan Mantra', 'duration' => '30 min'], ['title' => 'Purna Ahuti', 'duration' => '8 min'], ['title' => 'Aarti & Blessing', 'duration' => '8 min']],
                'donation_options' => $commonDonations,
                'available_slots' => $commonSlots,
                'is_featured' => false,
                'status' => 'active',
            ],
        ];
    }
}
