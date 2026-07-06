<?php

require_once __DIR__ . '/database.php';

/** @return list<array<string, mixed>> */
function sh_leads_load(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        return sh_db_load_leads();
    } catch (Throwable $e) {
        return [];
    }
}

function sh_leads_save(array $leads): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    return sh_db_save_leads(array_values($leads));
}

function sh_lead_add(array $payload): ?array
{
    $phone = preg_replace('/[^\d+]/', '', trim($payload['phone'] ?? ''));
    if ($phone === '' || strlen($phone) < 6) {
        return null;
    }
    $leads = sh_leads_load();
    $lead = [
        'id'           => 'lead-' . bin2hex(random_bytes(6)),
        'phone'        => $phone,
        'product_id'   => trim($payload['product_id'] ?? ''),
        'product_name' => trim($payload['product_name'] ?? ''),
        'name'         => trim($payload['name'] ?? ''),
        'lang'         => trim($payload['lang'] ?? 'no'),
        'status'       => 'new',
        'created_at'   => gmdate('Y-m-d\TH:i:s\Z'),
        'updated_at'   => gmdate('Y-m-d\TH:i:s\Z'),
        'note'         => '',
    ];
    array_unshift($leads, $lead);
    if (count($leads) > 500) {
        $leads = array_slice($leads, 0, 500);
    }
    return sh_leads_save($leads) ? $lead : null;
}

function sh_lead_update_status(string $id, string $status, string $note = ''): bool
{
    $leads = sh_leads_load();
    $changed = false;
    foreach ($leads as &$lead) {
        if (($lead['id'] ?? '') !== $id) {
            continue;
        }
        $lead['status'] = $status;
        $lead['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        if ($note !== '') {
            $lead['note'] = $note;
        }
        $changed = true;
        break;
    }
    unset($lead);
    return $changed && sh_leads_save($leads);
}

function sh_lead_delete(string $id): bool
{
    $leads = array_values(array_filter(
        sh_leads_load(),
        static fn(array $l): bool => ($l['id'] ?? '') !== $id
    ));
    return sh_leads_save($leads);
}