<?php
/**
 * Canonical BILOHASH subscription URL — one plan for all CMS scripts & AI.
 */
function sh_subscription_url(): string
{
    return 'https://bilohash.com/ecosystem/join.php';
}

function sh_license_cabinet_url(): string
{
    return 'https://bilohash.com/ecosystem/cabinet.php';
}

function sh_ai_advisor_public_url(): string
{
    return 'https://bilohash.com/shop/admin/ai-agent.php';
}

function sh_subscription_external_attrs(): string
{
    return 'target="_blank" rel="noopener noreferrer"';
}