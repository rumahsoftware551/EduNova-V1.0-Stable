<?php
namespace App\Services;

use App\Models\AttendanceSession;
use Carbon\CarbonInterface;

class AttendanceService
{
    public function qrPayload(AttendanceSession $session, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $seconds = max(10, (int) $session->qr_rotation_seconds);
        $slot = intdiv($at->timestamp, $seconds);
        $token = $this->tokenForSlot($session, $slot);
        $expiresTs = ($slot + 1) * $seconds;

        return [
            'token' => $token,
            'slot' => $slot,
            'expires_at' => now()->setTimestamp($expiresTs)->toIso8601String(),
            'rotation_seconds' => $seconds,
        ];
    }

    public function validateQrToken(AttendanceSession $session, ?string $token, ?CarbonInterface $at = null): bool
    {
        if (!$session->require_dynamic_qr) return true;
        if (!$token) return false;
        $at ??= now();
        $seconds = max(10, (int) $session->qr_rotation_seconds);
        $slot = intdiv($at->timestamp, $seconds);
        foreach ([$slot, $slot - 1] as $candidate) {
            if (hash_equals($this->tokenForSlot($session, $candidate), strtoupper(trim($token)))) return true;
        }
        return false;
    }

    private function tokenForSlot(AttendanceSession $session, int $slot): string
    {
        $secret = (string) $session->getRawOriginal('qr_secret');
        return strtoupper(substr(hash_hmac('sha256', $session->id.'|'.$slot, $secret), 0, 12));
    }

    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000.0;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dPhi = deg2rad($lat2 - $lat1);
        $dLambda = deg2rad($lng2 - $lng1);
        $a = sin($dPhi/2)**2 + cos($phi1) * cos($phi2) * sin($dLambda/2)**2;
        return $earth * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }
}
