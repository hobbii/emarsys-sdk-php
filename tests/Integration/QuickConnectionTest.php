<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Client;

class QuickConnectionTest
{
    public function run(): void
    {
        global $clientId, $clientSecret;

        echo "Testing Emarsys SDK...\n\n";

        $client = new Client($clientId, $clientSecret);

        echo "✅ Client created successfully\n";
        echo "🔍 Testing API connection by listing contact lists...\n\n";

        $lists = $client->contactLists()->list();

        echo "🎉 SUCCESS! API connection working.\n";
        echo "📊 Found {$lists->count()} contact lists in your Emarsys account.\n\n";

        if (! $lists->isEmpty()) {
            echo "📝 Your contact lists:\n";
            foreach ($lists->items as $list) {
                echo "   - {$list->name} (ID: {$list->id})\n";
            }
        }
    }
}
