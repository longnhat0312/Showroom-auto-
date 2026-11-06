<?php
declare(strict_types=1);

function vnp_fields(array $input): array {
    $data = [];
    foreach ($input as $key => $value) {
        if (!str_starts_with((string) $key, 'vnp_')) continue;
        if (!is_string($value)) throw new InvalidArgumentException('Invalid VNPay parameter');
        if (in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true)) continue;
        $data[$key] = $value;
    }
    ksort($data, SORT_STRING);
    return $data;
}

function vnp_sign(array $data, string $secret): string {
    $data = vnp_fields($data);
    $query = http_build_query($data, '', '&', PHP_QUERY_RFC1738);
    return hash_hmac('sha512', $query, $secret);
}

function vnp_verify(array $input, string $secret): bool {
    $hash = $input['vnp_SecureHash'] ?? null;
    if (!is_string($hash) || !preg_match('/^[a-fA-F0-9]{128}$/D', $hash)) return false;
    try { return hash_equals(vnp_sign($input, $secret), strtolower($hash)); }
    catch (InvalidArgumentException) { return false; }
}

function vnp_amount(string $vnd): string {
    // CSDL DECIMAL(15,2), VND trong ứng dụng này chỉ nhận số đồng nguyên.
    if (!preg_match('/^([0-9]{1,13})(?:\.00)?$/D', $vnd, $matches))
        throw new InvalidArgumentException('Số tiền VND phải là số đồng nguyên.');
    $amount = ltrim($matches[1] . '00', '0');
    if ($amount === '') throw new InvalidArgumentException('Số tiền phải lớn hơn 0.');
    return $amount;
}

function vnp_payment_url(array $config, string $paymentId, string $vnd,
    string $ip, DateTimeImmutable $expires, ?DateTimeImmutable $created = null): string {
    $zone = new DateTimeZone('Asia/Ho_Chi_Minh');
    $now = new DateTimeImmutable('now', $zone);
    if ($expires <= $now) throw new InvalidArgumentException('Phiên đã hết hạn.');
    if (!filter_var($ip, FILTER_VALIDATE_IP)) throw new InvalidArgumentException('IP không hợp lệ.');
    if (!preg_match('/^[A-Za-z0-9_-]{1,50}$/D', $paymentId)) throw new InvalidArgumentException('Mã phiên không hợp lệ.');
    $params = [
        'vnp_Version' => '2.1.0',
        'vnp_Command' => 'pay',
        'vnp_TmnCode' => $config['tmn_code'],
        'vnp_Amount' => vnp_amount($vnd),
        'vnp_CurrCode' => 'VND',
        'vnp_TxnRef' => $paymentId,
        'vnp_OrderInfo' => 'Thanh toan ' . $paymentId,
        'vnp_OrderType' => 'other',
        'vnp_Locale' => 'vn',
        'vnp_ReturnUrl' => $config['return_url'],
        'vnp_IpAddr' => $ip,
        'vnp_CreateDate' => ($created ?? $now)->setTimezone($zone)->format('YmdHis'),
        'vnp_ExpireDate' => $expires->setTimezone($zone)->format('YmdHis'),
    ];
    $params = vnp_fields($params);
    $params['vnp_SecureHash'] = vnp_sign($params, $config['hash_secret']);
    return $config['payment_url'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC1738);
}
