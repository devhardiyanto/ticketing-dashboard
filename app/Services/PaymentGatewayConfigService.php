<?php

namespace App\Services;

use App\Models\Core\PaymentGateway;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Encryption\Encrypter;

/**
 * Payment Gateway Config Service
 *
 * Manages payment gateway configurations with encrypted credentials
 * Uses Laravel's built-in AES-256-CBC encryption (Crypt facade)
 *
 * Security Notes:
 * - Master key stored in APP_KEY environment variable
 * - Laravel automatically handles encryption/decryption
 * - Keys should be rotated every 90 days
 */
class PaymentGatewayConfigService
{
    /**
     * Get all active payment gateways (without decrypting config)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllActive()
    {
        return PaymentGateway::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get gateway by code (without decrypting config)
     *
     * @param string $code Gateway code ('midtrans', 'xendit', 'nicepay')
     * @return PaymentGateway|null
     */
    public static function getByCode(string $code): ?PaymentGateway
    {
        return PaymentGateway::where('code', $code)->first();
    }

    /**
     * Get gateway config with decrypted credentials
     *
     * @param string $code Gateway code
     * @return array Gateway data with decrypted config
     * @throws \Exception if gateway not found or inactive
     */
    public static function getConfig(string $code): array
    {
        $gateway = self::getByCode($code);

        if (!$gateway) {
            throw new \Exception("Payment gateway '{$code}' not found");
        }

        if (!$gateway->is_active) {
            throw new \Exception("Payment gateway '{$code}' is not active");
        }

        // Decrypt config
        try {
            // Check if config is placeholder (not yet encrypted)
            if ($gateway->encrypted_config === 'PLACEHOLDER_CONFIG') {
                throw new \Exception(
                    "Payment gateway '{$code}' config not yet set. Please configure credentials via admin panel."
                );
            }

            $decryptedConfig = json_decode(
                Crypt::decryptString($gateway->encrypted_config),
                true
            );

            if ($decryptedConfig === null) {
                throw new \Exception('Failed to parse decrypted config as JSON');
            }
        } catch (\Exception $e) {
            Log::error("Failed to decrypt config for gateway '{$code}'", [
                'error' => $e->getMessage(),
            ]);

            throw new \Exception(
                "Failed to decrypt config for gateway '{$code}': " . $e->getMessage()
            );
        }

        return [
            'id' => $gateway->id,
            'code' => $gateway->code,
            'name' => $gateway->name,
            'description' => $gateway->description,
            'is_active' => $gateway->is_active,
            'is_sandbox' => $gateway->is_sandbox,
            'config' => $decryptedConfig,
            'settings' => $gateway->settings,
            'supported_methods' => $gateway->supported_methods,
            'fee_config' => $gateway->fee_config,
            'priority' => $gateway->priority,
        ];
    }

    /**
     * Update gateway config (encrypts before saving)
     *
     * @param string $code Gateway code
     * @param array $config Gateway configuration array
     * @return PaymentGateway Updated gateway
     * @throws \Exception if gateway not found
     *
     * @example
     * PaymentGatewayConfigService::updateConfig('midtrans', [
     *     'server_key' => 'SB-Mid-server-xxx',
     *     'client_key' => 'SB-Mid-client-xxx',
     *     'merchant_id' => 'G123456789'
     * ]);
     */
    public static function updateConfig(string $code, array $config): PaymentGateway
    {
        $gateway = self::getByCode($code);

        if (!$gateway) {
            throw new \Exception("Payment gateway '{$code}' not found");
        }

        // Validate config
        $validation = self::validateConfig($code, $config);

        if (!$validation['valid']) {
            throw new \Exception(
                "Invalid config for gateway '{$code}'. Missing fields: " .
                implode(', ', $validation['missing'])
            );
        }

        // Encrypt config
        $encryptedConfig = Crypt::encryptString(json_encode($config));

        // Update database
        $gateway->encrypted_config = $encryptedConfig;
        $gateway->updated_at = now();
        $gateway->save();

        Log::info("Updated config for payment gateway '{$code}'");

        return $gateway;
    }

    /**
     * Set gateway active/inactive status
     *
     * @param string $code Gateway code
     * @param bool $isActive Active status
     * @return PaymentGateway Updated gateway
     */
    public static function setActiveStatus(string $code, bool $isActive): PaymentGateway
    {
        $gateway = self::getByCode($code);

        if (!$gateway) {
            throw new \Exception("Payment gateway '{$code}' not found");
        }

        $gateway->is_active = $isActive;
        $gateway->updated_at = now();
        $gateway->save();

        Log::info("Set payment gateway '{$code}' active status to: " . ($isActive ? 'true' : 'false'));

        return $gateway;
    }

    /**
     * Set gateway sandbox mode
     *
     * @param string $code Gateway code
     * @param bool $isSandbox Sandbox mode status
     * @return PaymentGateway Updated gateway
     */
    public static function setSandboxMode(string $code, bool $isSandbox): PaymentGateway
    {
        $gateway = self::getByCode($code);

        if (!$gateway) {
            throw new \Exception("Payment gateway '{$code}' not found");
        }

        $gateway->is_sandbox = $isSandbox;
        $gateway->updated_at = now();
        $gateway->save();

        Log::info("Set payment gateway '{$code}' sandbox mode to: " . ($isSandbox ? 'true' : 'false'));

        return $gateway;
    }

    /**
     * Validate gateway config (check if all required fields are present)
     *
     * @param string $code Gateway code
     * @param array $config Config to validate
     * @return array Validation result ['valid' => bool, 'missing' => array]
     */
    public static function validateConfig(string $code, array $config): array
    {
        $requiredFields = [
            'midtrans' => ['server_key', 'client_key'],
            'xendit' => ['api_key'],
            'nicepay' => ['merchant_key', 'merchant_id'],
        ];

        $required = $requiredFields[$code] ?? [];
        $missing = [];

        foreach ($required as $field) {
            if (empty($config[$field])) {
                $missing[] = $field;
            }
        }

        return [
            'valid' => count($missing) === 0,
            'missing' => $missing,
        ];
    }

    /**
     * Get gateway base URL (production or sandbox)
     *
     * @param string $code Gateway code
     * @param bool $isSandbox Use sandbox URL
     * @return string Base API URL
     */
    public static function getBaseUrl(string $code, bool $isSandbox): string
    {
        $urls = [
            'midtrans' => [
                'production' => 'https://api.midtrans.com',
                'sandbox' => 'https://api.sandbox.midtrans.com',
            ],
            'xendit' => [
                'production' => 'https://api.xendit.co',
                'sandbox' => 'https://api.xendit.co', // Xendit uses same URL
            ],
            'nicepay' => [
                'production' => 'https://api.nicepay.co.id',
                'sandbox' => 'https://dev.nicepay.co.id',
            ],
        ];

        if (!isset($urls[$code])) {
            throw new \Exception("Unknown gateway code: {$code}");
        }

        return $isSandbox ? $urls[$code]['sandbox'] : $urls[$code]['production'];
    }

    /**
     * Rotate encryption key for all gateways
     *
     * This method re-encrypts all gateway configs with a new APP_KEY
     * Should be run when rotating encryption keys
     *
     * @param string $oldKey Old APP_KEY (base64 encoded)
     * @param string $newKey New APP_KEY (base64 encoded)
     * @return int Number of gateways re-encrypted
     */
    public static function rotateEncryptionKey(string $oldKey, string $newKey): int
    {
        $allGateways = PaymentGateway::all();
        $rotatedCount = 0;

        // Create encrypter instances with old and new keys
        $oldEncrypter = new Encrypter(base64_decode(substr($oldKey, 7)), 'AES-256-CBC');
        $newEncrypter = new Encrypter(base64_decode(substr($newKey, 7)), 'AES-256-CBC');

        foreach ($allGateways as $gateway) {
            // Skip placeholder configs
            if ($gateway->encrypted_config === 'PLACEHOLDER_CONFIG') {
                continue;
            }

            try {
                // Decrypt with old key
                $decrypted = $oldEncrypter->decryptString($gateway->encrypted_config);

                // Re-encrypt with new key
                $reencrypted = $newEncrypter->encryptString($decrypted);

                // Update database
                $gateway->encrypted_config = $reencrypted;
                $gateway->updated_at = now();
                $gateway->save();

                $rotatedCount++;
            } catch (\Exception $e) {
                Log::error("Failed to rotate key for gateway {$gateway->code}", [
                    'error' => $e->getMessage(),
                ]);
                // Continue with other gateways
            }
        }

        Log::info("Rotated encryption key for {$rotatedCount} gateways");

        return $rotatedCount;
    }

    /**
     * Test if gateway config can be decrypted successfully
     *
     * @param string $code Gateway code
     * @return array Test result ['success' => bool, 'message' => string]
     */
    public static function testDecryption(string $code): array
    {
        try {
            $gateway = self::getByCode($code);

            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => "Gateway '{$code}' not found",
                ];
            }

            if ($gateway->encrypted_config === 'PLACEHOLDER_CONFIG') {
                return [
                    'success' => false,
                    'message' => 'Config not yet set (placeholder)',
                ];
            }

            // Try to decrypt
            $decrypted = Crypt::decryptString($gateway->encrypted_config);
            $config = json_decode($decrypted, true);

            if ($config === null) {
                return [
                    'success' => false,
                    'message' => 'Decrypted data is not valid JSON',
                ];
            }

            return [
                'success' => true,
                'message' => 'Config decrypted successfully',
                'fields' => array_keys($config),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Decryption failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get supported payment methods for a gateway
     *
     * @param string $code Gateway code
     * @return array List of supported payment methods
     */
    public static function getSupportedMethods(string $code): array
    {
        $gateway = self::getByCode($code);

        if (!$gateway) {
            return [];
        }

        return $gateway->supported_methods ?? [];
    }

    /**
     * Check if gateway supports a specific payment method
     *
     * @param string $code Gateway code
     * @param string $method Payment method
     * @return bool True if supported
     */
    public static function supportsMethod(string $code, string $method): bool
    {
        $supportedMethods = self::getSupportedMethods($code);

        return in_array($method, $supportedMethods);
    }
}
