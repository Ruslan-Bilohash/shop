<?php

function sh_leads_file(): string
{
    return sh_data_path('quick-leads.json');
}

/** @return list<array<string, mixed>> */
function sh_leads_load(): array
{
    $file = sh_leads_file();
    if (!is_readable($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file) ?: '[]', true);
    return is_array($data) ? $data : [];
}

function sh_leads_save(array $leads): bool
{
    $json = json_encode(array_values($leads), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents(sh_leads_file(), $json, LOCK_EX) !== false;
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
    $ok = false;
    foreach ($leads as &$lead) {
        if (($lead['id'] ?? '') !== $id) {
            continue;
        }
        $lead['status'] = in_array($status, ['new', 'contacted', 'closed'], true) ? $status : 'new';
        if ($note !== '') {
            $lead['note'] = $note;
        }
        $lead['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        $ok = true;
        break;
    }
    unset($lead);
    return $ok && sh_leads_save($leads);
}

function sh_leads_count_by_status(string $status = 'new'): int
{
    $n = 0;
    foreach (sh_leads_load() as $lead) {
        if (($lead['status'] ?? 'new') === $status) {
            $n++;
        }
    }
    return $n;
}