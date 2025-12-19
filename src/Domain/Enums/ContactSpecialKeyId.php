<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

/**
 * The key is only used for Contact identification in certain API calls.
 *
 * Usage:
 *  $keyId = ContactSpecialKeyId::id->name;  // 'id'
 *  $keyUid = ContactSpecialKeyId::uid->name; // 'uid'
 */
enum ContactSpecialKeyId
{
    case id;
    case uid;
}
