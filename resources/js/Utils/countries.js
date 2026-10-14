/**
 * Comprehensive ISO 3166-1 alpha-2 countries database.
 * Used for conference speaker origins, participant nationalities, and flag CDN rendering.
 */
export const countries = [
    // Frequent / Host Region
    { code: 'ID', name: 'Indonesia' },
    { code: 'MY', name: 'Malaysia' },
    { code: 'SG', name: 'Singapore' },
    { code: 'TH', name: 'Thailand' },
    { code: 'PH', name: 'Philippines' },
    { code: 'VN', name: 'Vietnam' },
    { code: 'BN', name: 'Brunei' },
    { code: 'KH', name: 'Cambodia' },
    { code: 'LA', name: 'Laos' },
    { code: 'MM', name: 'Myanmar' },
    { code: 'TL', name: 'Timor-Leste' },

    // Middle East & West Asia
    { code: 'IR', name: 'Iran' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'AE', name: 'United Arab Emirates' },
    { code: 'TR', name: 'Turkey' },
    { code: 'QA', name: 'Qatar' },
    { code: 'KW', name: 'Kuwait' },
    { code: 'OM', name: 'Oman' },
    { code: 'BH', name: 'Bahrain' },
    { code: 'JO', name: 'Jordan' },
    { code: 'LB', name: 'Lebanon' },
    { code: 'IQ', name: 'Iraq' },
    { code: 'PS', name: 'Palestine' },
    { code: 'YE', name: 'Yemen' },
    { code: 'SY', name: 'Syria' },

    // East & South Asia
    { code: 'JP', name: 'Japan' },
    { code: 'KR', name: 'South Korea' },
    { code: 'CN', name: 'China' },
    { code: 'TW', name: 'Taiwan' },
    { code: 'HK', name: 'Hong Kong' },
    { code: 'IN', name: 'India' },
    { code: 'PK', name: 'Pakistan' },
    { code: 'BD', name: 'Bangladesh' },
    { code: 'LK', name: 'Sri Lanka' },
    { code: 'NP', name: 'Nepal' },
    { code: 'BT', name: 'Bhutan' },
    { code: 'MV', name: 'Maldives' },
    { code: 'MN', name: 'Mongolia' },
    { code: 'UZ', name: 'Uzbekistan' },
    { code: 'KZ', name: 'Kazakhstan' },
    { code: 'KG', name: 'Kyrgyzstan' },
    { code: 'TJ', name: 'Tajikistan' },
    { code: 'TM', name: 'Turkmenistan' },

    // Americas
    { code: 'US', name: 'United States' },
    { code: 'CA', name: 'Canada' },
    { code: 'MX', name: 'Mexico' },
    { code: 'BR', name: 'Brazil' },
    { code: 'AR', name: 'Argentina' },
    { code: 'CL', name: 'Chile' },
    { code: 'CO', name: 'Colombia' },
    { code: 'PE', name: 'Peru' },
    { code: 'EC', name: 'Ecuador' },
    { code: 'VE', name: 'Venezuela' },
    { code: 'CU', name: 'Cuba' },
    { code: 'PA', name: 'Panama' },
    { code: 'CR', name: 'Costa Rica' },

    // Europe
    { code: 'GB', name: 'United Kingdom' },
    { code: 'NL', name: 'Netherlands' },
    { code: 'DE', name: 'Germany' },
    { code: 'FR', name: 'France' },
    { code: 'CH', name: 'Switzerland' },
    { code: 'IT', name: 'Italy' },
    { code: 'ES', name: 'Spain' },
    { code: 'PT', name: 'Portugal' },
    { code: 'BE', name: 'Belgium' },
    { code: 'AT', name: 'Austria' },
    { code: 'SE', name: 'Sweden' },
    { code: 'NO', name: 'Norway' },
    { code: 'DK', name: 'Denmark' },
    { code: 'FI', name: 'Finland' },
    { code: 'IE', name: 'Ireland' },
    { code: 'PL', name: 'Poland' },
    { code: 'CZ', name: 'Czech Republic' },
    { code: 'HU', name: 'Hungary' },
    { code: 'GR', name: 'Greece' },
    { code: 'RO', name: 'Romania' },
    { code: 'BG', name: 'Bulgaria' },
    { code: 'HR', name: 'Croatia' },
    { code: 'RS', name: 'Serbia' },
    { code: 'RU', name: 'Russia' },
    { code: 'UA', name: 'Ukraine' },

    // Oceania
    { code: 'AU', name: 'Australia' },
    { code: 'NZ', name: 'New Zealand' },
    { code: 'PG', name: 'Papua New Guinea' },
    { code: 'FJ', name: 'Fiji' },

    // Africa
    { code: 'EG', name: 'Egypt' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'NG', name: 'Nigeria' },
    { code: 'KE', name: 'Kenya' },
    { code: 'MA', name: 'Morocco' },
    { code: 'DZ', name: 'Algeria' },
    { code: 'TN', name: 'Tunisia' },
    { code: 'SD', name: 'Sudan' },
    { code: 'ET', name: 'Ethiopia' },
    { code: 'GH', name: 'Ghana' },
    { code: 'TZ', name: 'Tanzania' },
    { code: 'UG', name: 'Uganda' },
];

/**
 * Get country flag image URL from ISO code.
 * @param {string} code - ISO 3166-1 alpha-2 country code (e.g. 'ID', 'IR')
 * @returns {string} Flag URL or empty string
 */
export function getFlagUrl(code) {
    if (!code) return '';
    return `https://flagcdn.com/w40/${code.toLowerCase()}.png`;
}

/**
 * Find country object by country name or code.
 * @param {string} search - Name or code
 * @returns {object|undefined}
 */
export function findCountry(search) {
    if (!search) return undefined;
    const clean = search.trim().toLowerCase();
    return countries.find(
        (c) => c.name.toLowerCase() === clean || c.code.toLowerCase() === clean
    );
}
