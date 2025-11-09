<?php

namespace Anil\Hbl;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Psr\Http\Message\RequestInterface;

abstract class ActionRequest
{
    protected Client $client;

    private readonly JWSCompactSerializer $jwsCompactSerializer;

    private readonly JWSBuilder $jwsBuilder;

    private readonly JWSLoader $jwsLoader;

    private readonly ClaimCheckerManager $claimCheckerManager;

    private readonly JWECompactSerializer $jweCompactSerializer;

    private readonly JWEBuilder $jweBuilder;

    private readonly JWELoader $jweLoader;

    private const KEY_PREFIX_PRIVATE = "-----BEGIN RSA PRIVATE KEY-----\n";

    private const KEY_SUFFIX_PRIVATE = "\n-----END RSA PRIVATE KEY-----";

    private const KEY_PREFIX_PUBLIC = "-----BEGIN PUBLIC KEY-----\n";

    private const KEY_SUFFIX_PUBLIC = "\n-----END PUBLIC KEY-----";

    private const GUID_HYPHEN = '-';

    private const GUID_LENGTHS = [8, 4, 4, 4, 12];

    private const TIME_DRIFT_ALLOWED = 0;

    private const ISSUER = 'PacoIssuer';

    public function __construct()
    {
        $this->client = $this->createHttpClient();
        $this->jwsCompactSerializer = new JWSCompactSerializer;
        $this->jwsBuilder = $this->createJWSBuilder();
        $this->jwsLoader = $this->createJWSLoader();
        $this->claimCheckerManager = $this->createClaimCheckerManager();
        $this->jweCompactSerializer = new JWECompactSerializer;
        $this->jweBuilder = $this->createJWEBuilder();
        $this->jweLoader = $this->createJWELoader();
    }

    /**
     * Creates an HTTP client with custom handler stack.
     */
    private function createHttpClient(): Client
    {
        $handler = HandlerStack::create();
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withoutHeader('User-Agent');
        }));

        return new Client([
            'base_uri' => SecurityData::$EndPoint,
            'handler' => $handler,
        ]);
    }

    /**
     * Creates a JWS (JSON Web Signature) builder.
     */
    private function createJWSBuilder(): JWSBuilder
    {
        return new JWSBuilder(
            signatureAlgorithmManager: new AlgorithmManager(
                algorithms: [new PS256]
            )
        );
    }

    /**
     * Creates a JWS loader with verifier and header checker.
     */
    private function createJWSLoader(): JWSLoader
    {
        return new JWSLoader(
            serializerManager: new JWSSerializerManager(
                serializers: [new JWSCompactSerializer]
            ),
            jwsVerifier: new JWSVerifier(
                signatureAlgorithmManager: new AlgorithmManager(
                    algorithms: [new PS256]
                )
            ),
            headerCheckerManager: new HeaderCheckerManager(
                checkers: [
                    new AlgorithmChecker(
                        supportedAlgorithms: [SecurityData::$JWSAlgorithm],
                        protectedHeader: true
                    ),
                ],
                tokenTypes: [new JWSTokenSupport]
            ),
        );
    }

    /**
     * Creates a claim checker manager with all required checkers.
     */
    private function createClaimCheckerManager(): ClaimCheckerManager
    {
        return new ClaimCheckerManager(
            checkers: [
                new NotBeforeChecker(self::TIME_DRIFT_ALLOWED),
                new ExpirationTimeChecker(self::TIME_DRIFT_ALLOWED),
                new AudienceChecker(SecurityData::$AccessToken),
                new IssuerChecker([self::ISSUER]),
            ]
        );
    }

    /**
     * Creates a JWE (JSON Web Encryption) builder.
     */
    private function createJWEBuilder(): JWEBuilder
    {
        return new JWEBuilder(
            new AlgorithmManager(
                algorithms: [
                    new RSAOAEP,
                    new A128CBCHS256,
                ],
            ),
            null,
            null
        );
    }

    /**
     * Creates a JWE loader with decrypter and header checker.
     */
    private function createJWELoader(): JWELoader
    {
        return new JWELoader(
            serializerManager: new JWESerializerManager(
                serializers: [new JWECompactSerializer]
            ),
            jweDecrypter: new JWEDecrypter(
                new AlgorithmManager(
                    algorithms: [
                        new RSAOAEP,
                        new A128CBCHS256,
                    ]
                ),
                null,
                null
            ),
            headerCheckerManager: new HeaderCheckerManager(
                checkers: [
                    new AlgorithmChecker(
                        supportedAlgorithms: [SecurityData::$JWEAlgorithm],
                        protectedHeader: true
                    ),
                ],
                tokenTypes: [new JWETokenSupport]
            )
        );
    }

    /**
     * Creates a JWK Private Key from PKCS#8 Encoded Private Key.
     *
     * @param  string  $key  The base64-encoded private key
     * @param  string|null  $password  Optional password for encrypted keys
     * @param  array<string, mixed>  $additionalValues  Additional key values
     * @return JWK The created JWK private key
     */
    protected function GetPrivateKey(string $key, ?string $password = null, array $additionalValues = []): JWK
    {
        $privateKey = self::KEY_PREFIX_PRIVATE.$key.self::KEY_SUFFIX_PRIVATE;

        return JWKFactory::createFromKey($privateKey, $password, $additionalValues);
    }

    /**
     * Creates a JWK Public Key from PKCS#8 Encoded Public Key.
     *
     * @param  string  $key  The base64-encoded public key
     * @param  string|null  $password  Optional password for encrypted keys
     * @param  array<string, mixed>  $additionalValues  Additional key values
     * @return JWK The created JWK public key
     */
    protected function GetPublicKey(string $key, ?string $password = null, array $additionalValues = []): JWK
    {
        $publicKey = self::KEY_PREFIX_PUBLIC.$key.self::KEY_SUFFIX_PUBLIC;

        return JWKFactory::createFromKey($publicKey, $password, $additionalValues);
    }

    /**
     * Creates an encrypted JOSE Token from given payload.
     *
     * @param  string  $payload  The JSON payload to encrypt
     * @param  JWK  $signingKey  The key for signing the JWS
     * @param  JWK  $encryptingKey  The key for encrypting the JWE
     * @return string The serialized encrypted token
     */
    protected function EncryptPayload(string $payload, JWK $signingKey, JWK $encryptingKey): string
    {
        // Create JWS (JSON Web Signature)
        $jws = $this->jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($signingKey, [
                'alg' => SecurityData::$JWSAlgorithm,
                'typ' => SecurityData::$TokenType,
            ])
            ->build();

        // Create JWE (JSON Web Encryption) wrapping the JWS
        $jwe = $this->jweBuilder
            ->create()
            ->withPayload($this->jwsCompactSerializer->serialize($jws))
            ->withSharedProtectedHeader([
                'alg' => SecurityData::$JWEAlgorithm,
                'enc' => SecurityData::$JWEEncryptionAlgorithm,
                'kid' => SecurityData::$EncryptionKeyId,
                'typ' => SecurityData::$TokenType,
            ])
            ->addRecipient($encryptingKey)
            ->build();

        return $this->jweCompactSerializer->serialize($jwe, 0);
    }

    /**
     * Decrypts a JOSE Token and returns plain text payload.
     *
     * @param  string  $token  The encrypted token to decrypt
     * @param  JWK  $decryptingKey  The key for decrypting the JWE
     * @param  JWK  $signatureVerificationKey  The key for verifying the JWS signature
     * @return string The decrypted payload
     *
     * @throws Exception If decryption or verification fails
     */
    protected function DecryptToken(string $token, JWK $decryptingKey, JWK $signatureVerificationKey): string
    {
        $recipient = 0;
        $jwe = $this->jweLoader->loadAndDecryptWithKey($token, $decryptingKey, $recipient);

        $signature = 0;
        $jws = $this->jwsLoader->loadAndVerifyWithKey($jwe->getPayload(), $signatureVerificationKey, $signature);

        $decryptedPayload = $jws->getPayload();
        $claims = json_decode($decryptedPayload, true);

        if (! is_array($claims)) {
            throw new Exception('Invalid token payload: expected JSON object');
        }

        $this->claimCheckerManager->check($claims);

        return $decryptedPayload;
    }

    /**
     * Generates a UUID v4 compatible GUID.
     *
     * @return string The generated GUID in lowercase
     */
    protected function Guid(): string
    {
        $charId = strtoupper(md5(uniqid((string) rand(), true)));
        $guidParts = [];
        $offset = 0;

        foreach (self::GUID_LENGTHS as $length) {
            $guidParts[] = substr($charId, $offset, $length);
            $offset += $length;
        }

        return strtolower(implode(self::GUID_HYPHEN, $guidParts));
    }
}
