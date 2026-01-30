<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SecurityScan extends Command
{
    protected $signature = 'security:scan';
    protected $description = 'Run full-stack Laravel security audit (PHP, Config, Files, Dependencies, OWASP, Routes)';

    // Menyimpan hasil scan untuk paparan akhir
    protected $results = [];

    public function handle()
    {
        $this->info('🛡️  Starting Full-Stack Security Scan (incl. OWASP & Routes)...');
        $this->info('📅 Date: ' . now()->toDateTimeString());
        $this->newLine();

        // ----------------------------------------------------------------
        // 1. Core Configuration & Environment
        // ----------------------------------------------------------------
        $this->section('1. Core Configuration');
        $this->checkConfig('app.debug', false, 'App Debug Mode');
        $this->checkConfig('app.env', 'production', 'Environment');
        
        // Periksa APP_URL ada HTTPS
        $appUrl = config('app.url');
        if (strpos($appUrl, 'https://') !== 0) {
            $this->logResult('APP_URL HTTPS', $appUrl, 'WARNING');
            $this->warn('   -> APP_URL does not start with https://');
        } else {
            $this->logResult('APP_URL HTTPS', 'Yes', 'OK');
        }

        if (empty(config('app.key'))) {
            $this->logResult('App Key', 'Missing', 'CRITICAL');
        } else {
            $this->logResult('App Key', 'Present', 'OK');
        }

        // ----------------------------------------------------------------
        // 2. PHP Server Settings (php.ini)
        // ----------------------------------------------------------------
        $this->section('2. PHP Server Configuration (php.ini)');
        
        // expose_php: Sebaiknya Off supaya header tidak memberitahu versi PHP kepada hacker
        $this->checkPhpIni('expose_php', false, 'Expose PHP Version');
        
        // display_errors: Mesti Off di production supaya error tidak paparkan path/database credentials
        $this->checkPhpIni('display_errors', false, 'Display Errors');
        
        // allow_url_fopen: Risiko RFI (Remote File Inclusion) jika On
        $allowUrlFopen = ini_get('allow_url_fopen');
        if ($allowUrlFopen) {
            $this->logResult('allow_url_fopen', 'On', 'WARNING');
            $this->comment('   -> Consider disabling allow_url_fopen if not fetching remote URLs via file functions.');
        } else {
            $this->logResult('allow_url_fopen', 'Off', 'OK');
        }

        // ----------------------------------------------------------------
        // 3. Database Security
        // ----------------------------------------------------------------
        $this->section('3. Database Security');
        $defaultConn = config('database.default');
        $dbPassword = config("database.connections.{$defaultConn}.password");

        if (empty($dbPassword) && config('app.env') === 'production') {
            $this->logResult('DB Password', 'Empty', 'CRITICAL');
            $this->error('   -> Database password is EMPTY in production!');
        } else {
            $this->logResult('DB Password', 'Set', 'OK');
        }

        // ----------------------------------------------------------------
        // 4. Session & Cookie Security
        // ----------------------------------------------------------------
        $this->section('4. Session & Cookie Security');
        $this->checkConfig('session.secure', true, 'Session Secure (HTTPS Only)');
        $this->checkConfig('session.http_only', true, 'Session HTTP Only (No JS Access)');
        $this->checkConfig('session.same_site', 'lax', 'Session SameSite');

        // ----------------------------------------------------------------
        // 5. Exposed Files & Directories
        // ----------------------------------------------------------------
        $this->section('5. Public Exposure Check');
        $sensitiveFiles = [
            '.env', '.env.example', '.git', '.vscode', '.idea',
            'composer.json', 'composer.lock', 'package.json', 'yarn.lock',
            'storage/logs/laravel.log', 'storage/oauth-private.key'
        ];

        foreach ($sensitiveFiles as $file) {
            $this->checkPublicExposure($file);
        }

        // ----------------------------------------------------------------
        // 6. Dangerous Dev Tools in Production
        // ----------------------------------------------------------------
        $this->section('6. Dev Tools Inspection');
        if (config('app.env') === 'production') {
            // Check Laravel Debugbar
            if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class) && config('debugbar.enabled')) {
                $this->logResult('Laravel Debugbar', 'ENABLED', 'CRITICAL');
                $this->error('   -> Debugbar is ENABLED in Production! Disable it immediately.');
            } else {
                $this->logResult('Laravel Debugbar', 'Disabled/Not Installed', 'OK');
            }

            // Check Telescope
            if (class_exists(\Laravel\Telescope\Telescope::class) && config('telescope.enabled')) {
                $this->logResult('Laravel Telescope', 'ENABLED', 'WARNING');
                $this->comment('   -> Ensure Telescope is protected by a Gate definition in production.');
            }
        } else {
            $this->info('Skipping Dev Tools check (Not in Production).');
        }

        // ----------------------------------------------------------------
        // 7. File Permissions
        // ----------------------------------------------------------------
        $this->section('7. FileSystem Permissions');
        $this->checkPermission('storage', ['775', '755']);
        $this->checkPermission('bootstrap/cache', ['775', '755']);
        
        if (file_exists(base_path('.env')) && is_writable(base_path('.env'))) {
             $this->logResult('File: .env', 'Writable', 'WARNING');
             $this->warn('   -> .env is writable via PHP. Ideally read-only (chmod 400/440/640) in prod.');
        } else {
             $this->logResult('File: .env', 'Read-only', 'OK');
        }

        // ----------------------------------------------------------------
        // 8. OWASP Top 10 Compliance Checks
        // ----------------------------------------------------------------
        $this->section('8. OWASP Top 10 Compliance Checks');

        // A02: Cryptographic Failures (Hashing & Ciphers)
        $hashingDriver = config('hashing.driver');
        if (in_array($hashingDriver, ['bcrypt', 'argon', 'argon2id'])) {
            $this->logResult('Hashing Driver', $hashingDriver, 'OK');
        } else {
            $this->logResult('Hashing Driver', $hashingDriver, 'WARNING');
            $this->warn('   -> Weak hashing driver detected. Use bcrypt or argon2id.');
        }

        $cipher = config('app.cipher');
        if ($cipher === 'AES-256-CBC') {
            $this->logResult('Encryption Cipher', 'AES-256-CBC', 'OK');
        } else {
            $this->logResult('Encryption Cipher', $cipher, 'WARNING');
        }

        // A03: Injection (SQL Strict Mode)
        // Strict mode prevents data truncation which can lead to vulnerabilities
        $dbConn = config('database.default');
        $dbStrict = config("database.connections.$dbConn.strict");
        if ($dbStrict) {
            $this->logResult('DB Strict Mode', 'Enabled', 'OK');
        } else {
            $this->logResult('DB Strict Mode', 'Disabled', 'WARNING');
            $this->warn('   -> Enabling Strict Mode prevents SQL truncation attacks.');
        }

        // A05: Security Misconfiguration (CORS)
        // Check for loose CORS (Wildcard origin with credentials)
        $corsPaths = config('cors.paths', []);
        $corsOrigins = config('cors.allowed_origins', []);
        $corsCredentials = config('cors.supports_credentials', false);

        if ($corsCredentials && (in_array('*', $corsOrigins))) {
             $this->logResult('CORS Config', 'Vulnerable (* + Creds)', 'CRITICAL');
             $this->error('   -> CORS allows Wildcard Origin (*) AND Credentials! This is a high risk configuration.');
        } else {
             $this->logResult('CORS Config', 'Safe', 'OK');
        }

        // A07: Identification Failures (Session Timeout)
        $sessionLifetime = config('session.lifetime');
        if ($sessionLifetime > 120) {
            $this->logResult('Session Lifetime', "$sessionLifetime min", 'WARNING');
            $this->warn('   -> Long session lifetime increases risk of session hijacking (Rec: 120m or less).');
        } else {
             $this->logResult('Session Lifetime', "$sessionLifetime min", 'OK');
        }

        // ----------------------------------------------------------------
        // 9. Route Security Analysis
        // ----------------------------------------------------------------
        $this->section('9. Route Security Analysis');
        
        $routes = Route::getRoutes();
        $insecureRoutes = [];
        $apiWithoutThrottle = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $middleware = $route->gatherMiddleware();
            
            // Skip predefined routes (telescope, debugbar, ignition, assets)
            if (str_contains($uri, '_ignition') || str_contains($uri, 'telescope') || str_contains($uri, '_debugbar') || str_contains($uri, 'sanctum/csrf-cookie')) {
                continue;
            }

            // Check 1: Sensitive paths without Auth (Heuristic check)
            $sensitiveKeywords = ['admin', 'dashboard', 'profile', 'settings', 'user', 'billing', 'account'];
            $isSensitive = false;
            foreach ($sensitiveKeywords as $keyword) {
                if (str_contains($uri, $keyword)) {
                    $isSensitive = true;
                    break;
                }
            }

            // If path looks sensitive, ensure it has auth middleware
            if ($isSensitive) {
                $hasAuth = false;
                foreach ($middleware as $m) {
                    if (str_contains($m, 'auth') || str_contains($m, 'sanctum') || str_contains($m, 'verified')) {
                        $hasAuth = true;
                        break;
                    }
                }
                
                // Allow specific exceptions like login/register/forgot-password pages
                if (str_contains($uri, 'login') || str_contains($uri, 'register') || str_contains($uri, 'password') || str_contains($uri, 'verify')) {
                    $hasAuth = true; 
                }

                if (!$hasAuth) {
                    $insecureRoutes[] = "[$methods[0]] $uri (Missing Auth)";
                }
            }

            // Check 2: API routes without throttling
            if (str_starts_with($uri, 'api/')) {
                $hasThrottle = false;
                foreach ($middleware as $m) {
                    if (str_contains($m, 'throttle')) {
                        $hasThrottle = true;
                        break;
                    }
                }
                if (!$hasThrottle) {
                    $apiWithoutThrottle++;
                }
            }
        }

        // Report Sensitive Public Routes
        if (count($insecureRoutes) > 0) {
            $this->logResult('Sensitive Public Routes', count($insecureRoutes) . ' Found', 'WARNING');
            foreach (array_slice($insecureRoutes, 0, 5) as $r) {
                 $this->warn("   -> Potential Risk: $r");
            }
            if (count($insecureRoutes) > 5) $this->warn("   -> ... and " . (count($insecureRoutes) - 5) . " more.");
        } else {
            $this->logResult('Sensitive Public Routes', 'Clean', 'OK');
        }

        // Report API Throttling
        if ($apiWithoutThrottle > 0) {
            $this->logResult('API Throttling', "$apiWithoutThrottle routes missing", 'WARNING');
            $this->warn('   -> Public API routes found without rate limiting (throttle:api).');
        } else {
            $this->logResult('API Throttling', 'OK', 'OK');
        }

        // ----------------------------------------------------------------
        // 10. Dependency Audits (Composer & NPM)
        // ----------------------------------------------------------------
        $this->section('10. Dependency Vulnerability Scan (A06: Vulnerable Components)');
        
        // Composer
        $this->info('Scanning PHP dependencies (composer audit)...');
        exec('composer audit 2>&1', $cOutput, $cReturn);
        if ($cReturn === 0) {
            $this->logResult('Composer Audit', 'Clean', 'OK');
        } else {
            $this->logResult('Composer Audit', 'VULNERABLE', 'CRITICAL');
            $this->error('   -> Vulnerabilities found!');
        }

        // NPM (Node.js)
        if (file_exists(base_path('package.json'))) {
            $this->info('Scanning JS dependencies (npm audit)...');
            // Check if npm exists
            exec('npm -v', $npmCheck, $npmExists);
            
            if ($npmExists === 0) {
                exec('npm audit --audit-level=high 2>&1', $nOutput, $nReturn);
                if ($nReturn === 0) {
                    $this->logResult('NPM Audit', 'Clean', 'OK');
                } else {
                    $this->logResult('NPM Audit', 'ISSUES FOUND', 'WARNING');
                    $this->warn('   -> Run "npm audit" manually to fix JS vulnerabilities.');
                }
            } else {
                $this->warn('   -> NPM not found, skipping JS audit.');
            }
        }

        // ----------------------------------------------------------------
        // Summary
        // ----------------------------------------------------------------
        $this->newLine();
        $this->table(
            ['Category / Check', 'Status', 'Result'],
            $this->results
        );

        // Kira skor mudah
        $issues = collect($this->results)->whereIn(2, ['WARNING', 'CRITICAL', 'ERROR'])->count();
        if ($issues === 0) {
            $this->info('✅ Full Scan Completed: System appears SECURE.');
        } else {
            $this->error("❌ Full Scan Completed: Found $issues potential issues.");
        }

        return Command::SUCCESS;
    }

    /**
     * Helper: Semak PHP INI settings
     */
    protected function checkPhpIni($key, $shouldBeOn, $label)
    {
        $value = ini_get($key);
        // Normalize PHP ini values (empty string, "0", "Off" all mean false usually)
        $isOn = !empty($value) && strtolower($value) !== 'off' && $value !== '0';

        if ($shouldBeOn === $isOn) {
            $this->logResult("PHP: $key", $value ?: 'Off', 'OK');
        } else {
            $expected = $shouldBeOn ? 'On' : 'Off';
            $this->logResult("PHP: $key", $value ?: 'Off', 'WARNING');
            $this->warn("   -> Expected $key to be '$expected'.");
        }
    }

    /**
     * Helper: Semak config Laravel
     */
    protected function checkConfig($key, $expected, $label)
    {
        $value = config($key);
        $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        $displayExpected = is_bool($expected) ? ($expected ? 'true' : 'false') : $expected;

        if ($value !== $expected) {
            $this->logResult($label, $displayValue, 'WARNING');
            $this->warn("   -> Expected '$displayExpected', got '$displayValue'.");
        } else {
            $this->logResult($label, $displayValue, 'OK');
        }
    }

    /**
     * Helper: Semak fail public
     */
    protected function checkPublicExposure($file)
    {
        $path = public_path($file);
        if (File::exists($path)) {
            $this->logResult("Public File: $file", 'EXPOSED', 'CRITICAL');
            $this->error("   -> DANGER: $file is accessible via browser!");
        }
    }

    /**
     * Helper: Semak permission
     */
    protected function checkPermission($path, $expectedArray)
    {
        if (!file_exists(base_path($path))) return;

        $perm = substr(sprintf('%o', fileperms(base_path($path))), -3);
        if (!in_array($perm, $expectedArray)) {
            $this->logResult("Perm: $path", $perm, 'WARNING');
            $this->warn("   -> Recommended permissions: " . implode(' or ', $expectedArray));
        } else {
            $this->logResult("Perm: $path", $perm, 'OK');
        }
    }

    protected function section($title)
    {
        $this->newLine();
        $this->line("<bg=blue;fg=white;options=bold> $title </>");
    }

    protected function logResult($check, $status, $type)
    {
        // Warnakan output dalam jadual
        $coloredType = match($type) {
            'OK' => '<info>OK</info>',
            'WARNING' => '<comment>WARNING</comment>',
            'CRITICAL', 'ERROR' => '<error>CRITICAL</error>',
            default => $type
        };

        $this->results[] = [$check, $status, $coloredType];
    }
}