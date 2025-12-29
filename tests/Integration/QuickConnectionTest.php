<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

class QuickConnectionTest extends AbstractIntegrationTest
{
    public function run(array $args): void
    {
        echo "🔍 Testing API connection by listing contact lists...\n\n";

        $lists = $this->client->contactLists()->list();

        echo "🎉 SUCCESS! API connection working.\n";
        echo "📊 Found {$lists->count()} contact lists in your Emarsys account.\n\n";

        if (! $lists->isEmpty()) {
            echo "📝 Your contact lists:\n";
            foreach ($lists as $list) {
                echo "   - {$list->name} (ID: {$list->id})\n";
            }
        }
    }
}
