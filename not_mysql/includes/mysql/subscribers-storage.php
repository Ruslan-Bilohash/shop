<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/** @return list<array<string, mixed>> */
function sh_subscribers_load(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        return sh_db_load_subscribers();
    } catch (Throwable $e) {
        return [];
    }
}

/** @param list<array<string, mixed>> $list */
function sh_subscribers_save(array $list): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    return sh_db_save_subscribers(array_values($list));
}

function sh_subscriber_add(string $email, string $lang = 'en'): ?array
{
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    $lang = strtolower(substr(trim($lang), 0, 8));
    $subs = sh_subscribers_load();
    foreach ($subs as $row) {
        if (strtolower((string) ($row['email'] ?? '')) === $email) {
            return null;
        }
    }
    $sub = [
        'id'         => 'sub-' . bin2hex(random_bytes(6)),
        'email'      => $email,
        'lang'       => $lang,
        'status'     => 'active',
        'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];
    array_unshift($subs, $sub);
    if (count($subs) > 5000) {
        $subs = array_slice($subs, 0, 5000);
    }
    return sh_subscribers_save($subs) ? $sub : null;
}

function sh_subscriber_delete(string $id): bool
{
    $id = trim($id);
    if ($id === '') {
        return false;
    }
    $subs = array_values(array_filter(
        sh_subscribers_load(),
        static fn(array $s): bool => ($s['id'] ?? '') !== $id
    ));
    return sh_subscribers_save($subs);
}

function sh_subscribers_count(): int
{
    return count(sh_subscribers_load());
}