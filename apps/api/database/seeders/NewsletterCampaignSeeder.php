<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsletterCampaignSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->campaigns() as $campaign) {
            DB::table('newsletter_campaigns')->updateOrInsert(
                ['id' => $campaign['id']],
                $campaign,
            );
        }
    }

    protected function campaigns(): array
    {
        return [
            [
                'id' => 1,
                'subject' => 'Hi',
                'title' => 'Hi',
                'message' => 'Hi mr mohamed yahea',
                'cta_label' => 'Apply',
                'cta_url' => '/apply',
                'locale' => null,
                'sent_count' => 8,
                'sent_at' => '2026-08-04 13:59:39',
                'created_by' => 1,
                'created_at' => '2026-08-04 13:59:39',
                'updated_at' => '2026-08-04 13:59:39',
            ],
        ];
    }
}
