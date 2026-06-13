<?php
/**
 * WikipediaService
 * 
 * Fetches book synopses from the Wikipedia REST API.
 * 
 * Features:
 *  - Redis-first cache with TTL (falls back to APCu → file)
 *  - Sliding-window rate limiter (per IP + global) via Redis
 *  - Jitter on TTL to avoid cache stampede
 *  - Structured result with source attribution
 */

class WikipediaService
{
    // ── Cache settings ──────────────────────────────────────────
    /** How long a successful Wikipedia hit is cached (24 h) */
    private const CACHE_TTL_HIT  = 86_400;
    /** How long a "not found" result is cached (2 h) */
    private const CACHE_TTL_MISS = 7_200;
    /** ±10 % jitter so entries don't all expire at once */
    private const CACHE_TTL_JITTER = 0.10;
    /** Redis key prefix for synopsis cache */
    private const CACHE_PREFIX = 'wiki:synopsis:';

    // ── Rate-limit settings ─────────────────────────────────────
    /** Max Wikipedia API calls per IP per minute */
    private const RL_IP_MAX    = 10;
    private const RL_IP_WINDOW = 60;          // seconds
    /** Max Wikipedia API calls globally per minute */
    private const RL_GLOBAL_MAX    = 60;
    private const RL_GLOBAL_WINDOW = 60;      // seconds
    /** Redis key prefix for rate-limit counters */
    private const RL_PREFIX = 'wiki:rl:';

    /** @var \Redis|null  (php-redis extension) */
    private \Redis|null $redis = null;
    private bool $redisOk = false;

    // ──────────────────────────────────────────────────────────────
    public function __construct()
    {
        $this->connectRedis();
    }

    // ══ Public API ════════════════════════════════════════════════

    /**
     * Main entry point.
     *
     * @param  string $title    Book title
     * @param  string $author   Optional author name
     * @param  string $fallback Text to use if Wikipedia yields nothing
     * @return array{text:string, source:string, page_url:string, page_title:string, cached:bool, rate_limited:bool}
     */
    public function getSynopsis(string $title, string $author = '', string $fallback = ''): array
    {
        $cacheKey = self::CACHE_PREFIX . md5(strtolower($title));

        // 1. Try cache first (no network cost, no rate-limit use)
        $cached = $this->cacheGet($cacheKey);
        if ($cached !== null) {
            $cached['cached'] = true;
            $cached['rate_limited'] = false;
            return $cached;
        }

        // 2. Rate-limit check before hitting Wikipedia's API
        if (!$this->allowRequest()) {
            return $this->buildResult($fallback, 'db', '', '', cached: false, rateLimited: true);
        }

        // 3. Try Wikipedia REST API
        $wikiResult = $this->fetchFromWikipedia($title, $author);

        if ($wikiResult !== null) {
            $this->cachePut($cacheKey, $wikiResult, self::CACHE_TTL_HIT);
            $wikiResult['cached']       = false;
            $wikiResult['rate_limited'] = false;
            return $wikiResult;
        }

        // 4. Nothing found — cache the miss so we don't hammer Wikipedia
        $miss = $this->buildResult($fallback, 'db', '', '');
        $this->cachePut($cacheKey, $miss, self::CACHE_TTL_MISS);
        $miss['cached']       = false;
        $miss['rate_limited'] = false;
        return $miss;
    }

    // ══ Cache layer ═══════════════════════════════════════════════

    private function cacheGet(string $key): ?array
    {
        // — Redis —
        if ($this->redisOk) {
            try {
                $raw = $this->redis->get($key);
                if ($raw !== false) {
                    $data = json_decode($raw, true);
                    return is_array($data) ? $data : null;
                }
                return null;
            } catch (\Exception $e) {
                $this->redisOk = false;
            }
        }

        // — APCu fallback —
        if (function_exists('apcu_fetch')) {
            $data = apcu_fetch($key, $ok);
            return $ok ? $data : null;
        }

        // — File fallback —
        return $this->fileCacheGet($key);
    }

    private function cachePut(string $key, array $data, int $ttl): void
    {
        // Add jitter: ±JITTER × ttl seconds
        $jitter = (int)($ttl * self::CACHE_TTL_JITTER);
        $ttl   += rand(-$jitter, $jitter);
        $ttl    = max(60, $ttl); // never below 1 minute

        if ($this->redisOk) {
            try {
                $this->redis->setex($key, $ttl, json_encode($data));
                return;
            } catch (\Exception $e) {
                $this->redisOk = false;
            }
        }

        if (function_exists('apcu_store')) {
            apcu_store($key, $data, $ttl);
            return;
        }

        $this->fileCachePut($key, $data, $ttl);
    }

    // ══ Rate limiter ══════════════════════════════════════════════

    /**
     * Sliding-window rate limiter.
     * Returns true if the request is allowed, false if throttled.
     */
    private function allowRequest(): bool
    {
        // Without Redis we do a best-effort file-based counter
        if (!$this->redisOk) {
            return $this->fileRateLimit();
        }

        $ip        = $this->clientIp();
        $now       = microtime(true);
        $ipKey     = self::RL_PREFIX . 'ip:'     . md5($ip);
        $globalKey = self::RL_PREFIX . 'global';

        try {
            // Sliding window via sorted set: score = timestamp, member = unique id
            $member = uniqid('', true);

            // — Per-IP check —
            $winStart = $now - self::RL_IP_WINDOW;
            $this->redis->zRemRangeByScore($ipKey, '-inf', $winStart);
            $ipCount = $this->redis->zCard($ipKey);
            if ($ipCount >= self::RL_IP_MAX) {
                return false;
            }
            $this->redis->zAdd($ipKey, $now, $member . '_ip');
            $this->redis->expire($ipKey, self::RL_IP_WINDOW + 1);

            // — Global check —
            $winStart2 = $now - self::RL_GLOBAL_WINDOW;
            $this->redis->zRemRangeByScore($globalKey, '-inf', $winStart2);
            $globalCount = $this->redis->zCard($globalKey);
            if ($globalCount >= self::RL_GLOBAL_MAX) {
                return false;
            }
            $this->redis->zAdd($globalKey, $now, $member . '_global');
            $this->redis->expire($globalKey, self::RL_GLOBAL_WINDOW + 1);

            return true;
        } catch (\Exception $e) {
            // Redis blip — allow the request rather than block everything
            return true;
        }
    }

    // ══ Wikipedia HTTP fetch ═══════════════════════════════════════

    private function fetchFromWikipedia(string $title, string $author): ?array
    {
        $candidates = array_filter([
            $title . ($author ? ' ' . $author : '') . ' novel',
            $title . ' novel',
            $title . ' book',
            $title,
        ]);

        foreach ($candidates as $searchQuery) {
            // Step 1: Search for the best matching page title
            $searchUrl = 'https://en.wikipedia.org/w/api.php?' . http_build_query([
                'action'   => 'query',
                'list'     => 'search',
                'srsearch' => $searchQuery,
                'srlimit'  => 1,
                'format'   => 'json',
            ]);

            $ctx = stream_context_create(['http' => [
                'timeout'       => 5,
                'ignore_errors' => true,
                'header'        => implode("\r\n", [
                    'User-Agent: BookstoreApp/1.0 (educational project)',
                    'Accept: application/json',
                ]),
            ]]);

            $raw = @file_get_contents($searchUrl, false, $ctx);
            if (!$raw) continue;

            $searchData = json_decode($raw, true);
            $pageTitle  = $searchData['query']['search'][0]['title'] ?? null;
            if (!$pageTitle) continue;

            // Step 2: Fetch full intro extract for that page
            $extractUrl = 'https://en.wikipedia.org/w/api.php?' . http_build_query([
                'action'        => 'query',
                'prop'          => 'extracts|info',
                'exintro'       => true,       // intro section only (before first heading)
                'explaintext'   => true,       // plain text, no HTML
                'titles'        => $pageTitle,
                'inprop'        => 'url',
                'format'        => 'json',
                'redirects'     => true,
            ]);

            $raw2 = @file_get_contents($extractUrl, false, $ctx);
            if (!$raw2) continue;

            $extractData = json_decode($raw2, true);
            $pages       = $extractData['query']['pages'] ?? [];
            $page        = reset($pages);

            if (empty($page) || isset($page['missing'])) continue;

            $extract = trim($page['extract'] ?? '');
            if (strlen($extract) < 30) continue;

            $pageUrl = $page['fullurl'] ?? ('https://en.wikipedia.org/wiki/' . rawurlencode(str_replace(' ', '_', $pageTitle)));

            return $this->buildResult(
                $extract,
                'wikipedia',
                $pageUrl,
                $page['title'] ?? $pageTitle
            );
        }

        return null;
    }

    // ══ Helpers ════════════════════════════════════════════════════

    private function buildResult(
        string $text,
        string $source,
        string $pageUrl,
        string $pageTitle,
        bool   $cached      = false,
        bool   $rateLimited = false
    ): array {
        return compact('text', 'source', 'pageUrl', 'pageTitle', 'cached', 'rateLimited');
    }

    private function env($keys, $default = null)
    {
        foreach ((array) $keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') return $value;
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        }

        return $default;
    }

    private function redisUrlConfig($url): array
    {
        if (empty($url)) return [];

        $parts = parse_url($url);
        if ($parts === false) return [];

        $database = 0;
        if (!empty($parts['path'])) {
            $path = ltrim($parts['path'], '/');
            $database = ctype_digit($path) ? (int) $path : 0;
        }

        return [
            'host' => $parts['host'] ?? null,
            'port' => $parts['port'] ?? null,
            'username' => isset($parts['user']) ? rawurldecode($parts['user']) : null,
            'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : null,
            'database' => $database,
        ];
    }

    private function connectRedis(): void
    {
        if (!extension_loaded('redis')) return;

        $urlConfig = $this->redisUrlConfig($this->env('REDIS_URL', ''));
        $host     = $this->env(['REDIS_HOST', 'REDISHOST'], $urlConfig['host'] ?? 'localhost');
        $port     = (int) $this->env(['REDIS_PORT', 'REDISPORT'], $urlConfig['port'] ?? 6379);
        $username = $this->env(['REDIS_USER', 'REDISUSER'], $urlConfig['username'] ?? null);
        $password = $this->env(['REDIS_PASSWORD', 'REDISPASSWORD'], $urlConfig['password'] ?? null);
        $db       = (int) $this->env(['REDIS_DATABASE', 'REDISDATABASE'], $urlConfig['database'] ?? 0);

        try {
            /** @phpstan-ignore-next-line (Redis is a PHP extension class) */
        $r = new \Redis();
            if (!$r->connect($host, $port, 2.0)) return;
            if ($password) $username ? $r->auth([$username, $password]) : $r->auth($password);
            if ($db > 0)   $r->select($db);
            $this->redis  = $r;
            $this->redisOk = true;
        } catch (\Exception $e) {
            error_log('[WikipediaService] Redis connect failed: ' . $e->getMessage());
        }
    }

    private function clientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) return $_SERVER[$h];
        }
        return '0.0.0.0';
    }

    // ── File-based fallbacks (when Redis not available) ───────────

    private function fileCacheDir(): string
    {
        $dir = dirname(__DIR__) . '/storage/wiki_cache';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
    }

    private function fileCacheGet(string $key): ?array
    {
        $file = $this->fileCacheDir() . '/' . md5($key) . '.json';
        if (!file_exists($file)) return null;
        $raw  = @file_get_contents($file);
        if (!$raw) return null;
        $obj  = json_decode($raw, true);
        if (!$obj || $obj['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $obj['data'];
    }

    private function fileCachePut(string $key, array $data, int $ttl): void
    {
        $file    = $this->fileCacheDir() . '/' . md5($key) . '.json';
        $payload = json_encode(['expires' => time() + $ttl, 'data' => $data]);
        @file_put_contents($file, $payload, LOCK_EX);
    }

    private function fileRateLimit(): bool
    {
        $file = dirname(__DIR__) . '/storage/wiki_rl.json';
        $now  = time();

        $raw  = @file_get_contents($file) ?: '{}';
        $data = json_decode($raw, true) ?: [];

        $ip  = md5($this->clientIp());
        $win = self::RL_IP_WINDOW;

        // Clean old entries
        $data[$ip] = array_filter($data[$ip] ?? [], fn($t) => $t > $now - $win);

        if (count($data[$ip]) >= self::RL_IP_MAX) return false;

        $data[$ip][] = $now;
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }
}
