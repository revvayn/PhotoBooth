<?php

namespace Database\Seeders;

use App\Models\Frame;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@photobooth.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@photobooth.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $frames = [
            ['name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'color_class' => 'from-pink-200 via-rose-100 to-violet-200', 'border_class' => 'border-rose-300', 'accent_class' => 'text-rose-500', 'caption' => 'K-POP', 'sort_order' => 1],
            ['name' => 'Classic Frame', 'slug' => 'classic', 'category' => 'Classic', 'color_class' => 'from-amber-100 via-orange-50 to-yellow-100', 'border_class' => 'border-amber-400', 'accent_class' => 'text-amber-600', 'caption' => 'CLASSIC MOMENTS', 'sort_order' => 2],
            ['name' => 'Minimalis Frame', 'slug' => 'minimalis', 'category' => 'Minimalis', 'color_class' => 'from-slate-100 via-white to-slate-100', 'border_class' => 'border-slate-300', 'accent_class' => 'text-slate-400', 'caption' => 'MINIMALIS', 'sort_order' => 3],
            ['name' => 'Neon Frame', 'slug' => 'neon', 'category' => 'Neon', 'color_class' => 'from-violet-200 via-fuchsia-100 to-cyan-100', 'border_class' => 'border-fuchsia-300', 'accent_class' => 'text-fuchsia-500', 'caption' => 'NEON', 'sort_order' => 4],
            ['name' => 'Estetik Frame', 'slug' => 'estetik', 'category' => 'Estetik', 'color_class' => 'from-emerald-100 via-teal-50 to-sky-100', 'border_class' => 'border-teal-300', 'accent_class' => 'text-teal-500', 'caption' => 'ESTETIK', 'sort_order' => 5],
        ];

        foreach ($frames as $f) {
            Frame::updateOrInsert(['slug' => $f['slug']], $f);
        }
    }
}
