<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

/**
 * Emarsys Contact System Field IDs
 *
 * Maps contact system field names to their corresponding numeric IDs
 * used by the Emarsys API.
 */
enum ContactSystemFieldId: int
{
    // Special fields
    case INTERESTS = 0;

    // Core identity fields
    case FIRST_NAME = 1;
    case LAST_NAME = 2;
    case EMAIL = 3;

    // Personal information
    case DATE_OF_BIRTH = 4;
    case GENDER = 5;
    case MARITAL_STATUS = 6;
    case CHILDREN = 7;
    case EDUCATION = 8;
    case TITLE = 9;

    // Address information
    case ADDRESS = 10;
    case CITY = 11;
    case STATE = 12;
    case ZIP = 13;
    case COUNTRY = 14;

    // Contact information
    case PHONE = 15;
    case FAX = 16;

    // Company information
    case JOB_POSITION = 17;
    case COMPANY = 18;
    case DEPARTMENT = 19;
    case INDUSTRY = 20;
    case PHONE_OFFICE = 21;
    case FAX_OFFICE = 22;
    case EMPLOYEES_COUNT = 23;
    case ANNUAL_REVENUE = 24;

    // Web and communication
    case URL = 25;
    case EMAIL_FORMAT = 26;

    // Engagement metrics (read-only)
    case AVG_VISIT_LENGTH = 27;
    case PAGE_VIEWS_PER_DAY = 28;
    case DAYS_SINCE_LAST_EMAIL = 29;
    case RESPONSE_RATE = 30;

    // System fields
    case OPT_IN = 31;
    case CONTACT_SOURCE = 33;
    case CONTACT_FORM = 34;
    case REGISTRATION_LANGUAGE = 35;
    case NEWSLETTER = 36;

    // Additional contact info
    case MOBILE = 37;

    // Partner information
    case PARTNER_FIRST_NAME = 38;
    case PARTNER_BIRTH_DATE = 39;
    case ANNIVERSARY = 40;

    // Company address
    case COMPANY_ADDRESS = 41;
    case ZIP_OFFICE = 42;
    case CITY_OFFICE = 43;
    case STATE_OFFICE = 44;
    case COUNTRY_OFFICE = 45;

    // Additional fields
    case SALUTATION = 46;
    case EMAIL_VALID = 47;
    case FIRST_REGISTRATION_DATE = 48;
}
