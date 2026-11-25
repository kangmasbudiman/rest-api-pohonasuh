<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Support\Facades\DB;

// Catat semua aktivitas tulis (POST/PUT/PATCH/DELETE) ke audit_logs.
// Identitas diambil dari field idmember/iduser yang dikirim klien, atau
// session auth bila tersedia. Webhook gateway sengaja dilewati.
class AuditLog
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        try {
            if (!in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return $response;
            }
            $path = $request->path();
            if (str_contains($path, 'webhook')) {
                return $response;
            }

            $idmember = (int) ($request->input('idmember') ?? $request->input('iduser') ?? 0);
            if ($idmember <= 0 && auth()->check()) {
                $idmember = (int) auth()->id();
            }
            $member = $idmember > 0 ? Member::find($idmember) : null;
            $role = 'sistem';
            if ($member) {
                $role = (int)($member->admin ?? 0) === 1
                    ? 'admin'
                    : ((int)($member->mitra_id ?? 0) > 0 ? 'mitra' : 'user');
            }

            DB::table('audit_logs')->insert([
                'user_id' => $idmember > 0 ? $idmember : null,
                'user_name' => $member ? (string) $member->name : 'sistem',
                'role' => $role,
                'method' => strtoupper($request->method()),
                'endpoint' => $path,
                'ip' => $request->ip(),
                'status' => method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // audit gagal tidak boleh menggagalkan request utama
        }

        return $response;
    }
}
