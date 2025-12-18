<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

/**
 * Emarsys Contact System Field.
 *
 * Maps contact system field names (Emarsys string id) to their corresponding numeric IDs used by the Emarsys API.
 *
 * Usage:
 *     $fieldName = ContactSystemField::first_name->name; // 'first_name'
 *     $fieldId = ContactSystemField::first_name->value;   // 1
 */
enum ContactSystemField: int
{
    // General fields
    case interests = 0;
    case salutation = 46;
    case title = 9;
    case first_name = 1;
    case last_name = 2;
    case email = 3;
    case preferred_email_format = 26;
    case source = 33;
    case form = 34;
    case registration_date = 48;
    case registration_language = 35;
    case optin = 31;
    case newsletter = 36;
    case status = 32;

    // Personal information
    case address = 10;
    case zip_code = 13;
    case city = 11;
    case state = 12;
    case country = 14;
    case phone = 15;
    case mobile = 37;
    case fax = 16;
    case gender = 5;
    case birth_date = 4;
    case education = 8;
    case marital_status = 6;
    case partner_first_name = 38;
    case partner_birth_date = 39;
    case anniversary = 40;
    case children = 7;

    // Company information
    case company_name = 18;
    case company_position = 17;
    case company_department = 19;
    case company_address = 41;
    case company_industry = 20;
    case company_employees = 23;
    case company_annual_revenue = 24;
    case company_phone = 21;
    case company_fax = 22;
    case url = 25;
    case company_zip_code = 42;
    case company_city = 43;
    case company_state = 44;
    case company_country = 45;

    // Other
    case average_visit_duration = 27;
    case pageviews_per_day = 28;
    case days_since_last_email_sent = 29;
    case email_valid = 47;
    case do_not_track_me = 456;
    case do_not_track_me_in_email = 457;
    case ietf_language_tag = 458;
}
