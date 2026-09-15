<?php

namespace Modules\Frontend\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontend\Models\Icon;

class IconSeeder extends Seeder
{
    /**
     * Starter Font Awesome icons for Hajj / Umrah packages.
     * Every class below is verified against Font Awesome 6.5.2 (free).
     */
    public function run(): void
    {
        $icons = [
            ['Kaaba', 'fa-solid fa-kaaba', 'kaaba, haram, makkah, tawaf'],
            ['Mosque', 'fa-solid fa-mosque', 'mosque, masjid, madinah, namaz'],
            ['Quran', 'fa-solid fa-book-quran', 'quran, quran, tilawat, kitab'],
            ['Prayer Time', 'fa-solid fa-clock', 'time, prayer, waqt, namaz'],
            ['Flight', 'fa-solid fa-plane', 'flight, air, plane, ticket'],
            ['Departure', 'fa-solid fa-plane-departure', 'departure, takeoff, flight'],
            ['Arrival', 'fa-solid fa-plane-arrival', 'arrival, landing, flight'],
            ['Hotel', 'fa-solid fa-hotel', 'hotel, accommodation, room'],
            ['Bed', 'fa-solid fa-bed', 'bed, room, sleep, night'],
            ['Meals', 'fa-solid fa-utensils', 'food, meal, khabar, dining'],
            ['Breakfast', 'fa-solid fa-coffee', 'breakfast, tea, coffee'],
            ['Fruit', 'fa-solid fa-apple-whole', 'fruit, snack, food'],
            ['Drinking Water', 'fa-solid fa-bottle-water', 'water, zamzam, drink'],
            ['Bus', 'fa-solid fa-bus', 'bus, transport, coach'],
            ['Van', 'fa-solid fa-van-shuttle', 'van, transport, shuttle'],
            ['Car', 'fa-solid fa-car', 'car, transport, taxi'],
            ['Taxi', 'fa-solid fa-taxi', 'taxi, cab, transport'],
            ['Train', 'fa-solid fa-train', 'train, rail, transport'],
            ['Ship', 'fa-solid fa-ship', 'ship, cruise, sea'],
            ['Group', 'fa-solid fa-user-group', 'group, team, jamaat'],
            ['Guide', 'fa-solid fa-person-walking-luggage', 'guide, mutawwif, leader'],
            ['Passport', 'fa-solid fa-passport', 'passport, travel document'],
            ['Visa', 'fa-solid fa-id-card', 'visa, id, document, iqama'],
            ['Contract', 'fa-solid fa-file-contract', 'contract, agreement, document'],
            ['Insurance', 'fa-solid fa-file-shield', 'insurance, takaful, protection'],
            ['Security', 'fa-solid fa-shield-halved', 'security, safety, safe'],
            ['Medical', 'fa-solid fa-stethoscope', 'medical, doctor, checkup'],
            ['Hospital', 'fa-solid fa-briefcase-medical', 'medical, hospital, first aid'],
            ['Health', 'fa-solid fa-heart-pulse', 'health, heart, emergency'],
            ['Wheelchair', 'fa-solid fa-wheelchair', 'wheelchair, accessibility, disabled'],
            ['Ziyarah', 'fa-solid fa-map-location-dot', 'ziyarah, tour, visit, place'],
            ['Route', 'fa-solid fa-route', 'route, itinerary, path'],
            ['Location', 'fa-solid fa-location-dot', 'location, place, makkah'],
            ['Kalima', 'fa-solid fa-language', 'language, arabic, translation'],
            ['Globe', 'fa-solid fa-globe', 'globe, world, international'],
            ['Mina Camp', 'fa-solid fa-campground', 'mina, arafah, tent, camp'],
            ['Shopping', 'fa-solid fa-suitcase-rolling', 'shopping, luggage, bag'],
            ['Bag', 'fa-solid fa-suitcase', 'bag, luggage, backpack'],
            ['Ihram', 'fa-solid fa-shirt', 'ihram, cloth, dress'],
            ['Umbrella', 'fa-solid fa-umbrella', 'umbrella, shade, sun, rain'],
            ['Ticket', 'fa-solid fa-ticket', 'ticket, coupon, booking'],
            ['Schedule', 'fa-solid fa-calendar-days', 'schedule, date, calendar'],
            ['Payment', 'fa-solid fa-money-check-dollar', 'payment, money, installment'],
            ['Zakat', 'fa-solid fa-hand-holding-dollar', 'zakat, sadqah, donation'],
            ['Gift', 'fa-solid fa-gift', 'gift, package, hamper'],
            ['Star', 'fa-solid fa-star', 'star, featured, premium'],
            ['Included', 'fa-solid fa-circle-check', 'included, yes, available'],
            ['Internet', 'fa-solid fa-wifi', 'wifi, internet, sim'],
            ['Reception', 'fa-solid fa-bell-concierge', 'reception, service, help desk'],
            ['Contact', 'fa-solid fa-phone', 'phone, contact, call'],
            ['Email', 'fa-solid fa-envelope', 'email, mail, message'],
            ['Family', 'fa-solid fa-users', 'family, group, people'],
            ['Child', 'fa-solid fa-child', 'child, kid, family'],
            ['Photo', 'fa-solid fa-camera', 'photo, camera, memory'],
            ['Gallery', 'fa-solid fa-images', 'gallery, photo, album'],
            ['Video', 'fa-solid fa-video', 'video, footage, record'],
            ['Flag', 'fa-solid fa-flag', 'flag, country, bangladesh'],
        ];

        $userId    = auth()->id();
        $sortOrder = ((int) Icon::max('sort_order'));

        foreach ($icons as [$name, $class, $keywords]) {
            $sortOrder++;

            // Skip icons that already exist so the seeder can be re-run safely.
            if (Icon::where('class', $class)->exists()) {
                continue;
            }

            Icon::create([
                'name'       => $name,
                'class'      => $class,
                'keywords'   => $keywords,
                'sort_order' => $sortOrder,
                'is_active'  => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }
    }
}
