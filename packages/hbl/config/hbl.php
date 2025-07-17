<?php

return [

    'OfficeId' => env('HBL_OFFICE_ID', '9104137120'),

    /**
     * payment end point
     */
    'EndPoint' => 'https://core.demo-paco.2c2p.com',

    /**
     * JWE Key Id.
     */
    'EncryptionKeyId' => '7664a2ed0dee4879bdfca0e8ce1ac313',

    /**
     * Access Token.
     */
    'AccessToken' => env('HBL_ACCESS_TOKEN', '65805a1636c74b8e8ac81a991da80be4'),

    /**
     * Token Type - Used in JWS and JWE header.
     */
    'TokenType' => 'JWT',

    /**
     * JWS (JSON Web Signature) Signature Algorithm - This parameter identifies the cryptographic algorithm used to
     * secure the JWS.
     */
    'JWSAlgorithm' => 'PS256',

    /**
     * JWE (JSON Web Encryption) Key Encryption Algorithm - This parameter identifies the cryptographic algorithm
     * used to secure the JWE.
     */
    'JWEAlgorithm' => 'RSA-OAEP',

    /**
     * JWE (JSON Web Encryption) Content Encryption Algorithm - This parameter identifies the content encryption
     * algorithm used on the plaintext to produce the encrypted ciphertext.
     */
    'JWEEncryptionAlgorithm' => 'A128CBC-HS256',

    /**
     * PACO Encryption Public Key is used to cryptographically encrypt and create the request JWE.
     */
    'PacoEncryptionPublicKey' => 'MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAviq4wrTmVMkRHouiHLUonJ1d6ss6nNreJ0JWpLwmTwAM7l35g8AFIvE8PqwWevtjuil9JZ1T1zwQTP8aM3s5/RzX5yFIhec/O14jib7Nmi4jACeJqDlHsnYzeCPw8WOhgmxWKHcORNLpn68jgnhLrKwh3Mooz/hXtIwGuNe/pYU7i/QaiuOjtmIcQ3yxJWjiHsllaogobZjbwMzwhp1fJ6ELmZp0FJvDrE8dn4UU9yzPFNzQ4gJzJAS/JKLXjfDw5mDQdw80vbzYuxksU0bc/3+DwY6hqaVJsP2AST7dCTR1wYzevzPxp0HMDmz1Ia/hSrmTPRhSa0qvxHMriVHUJvJeLTNI3cWM0RI9ukR7/v6vcf8ZwOZ+u7w4YfLpPCQFN7zGUN9Hho0pWBVYOstqsF5h/ZgBOlEHgSYY3CJdscV1+vKUvmFPiwkOdVxhc571RX56o+V71ZIGjXeYeqd3KNnND1JNsOn4hRPbk8Cl0e8CfZnEePfqtbFQGrzRU3GvSXscMb51TlvZu9i0toJdIJ4DiOCkUlB2sDI4x7N9ROOEbAD8uv68/jZqTM2paUNRN7Xvaa2LUCis3acadiyLt0tpuOT0sY2OejhLJshwNfTfc67gdtCJ3diddZWkXYpBgkMhuVj3TSx85sUklbGGJkzkwNsC0JhMSo7ZqbYxczECAwEAAQ==',

    /**
     * PACO Signing Public Key is used to cryptographically verify the response JWS signature.
     */
    'PacoSigningPublicKey' => 'MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkEOCDQxCbyv/n1jadyDDL9KLRddF7W2eVNf7GwVeqlq3CVor0QHiU+yweO3b622NZAPDBy/GFeJJH5lwdJUbYojFWtHUqYN7/HoTHF50KhAbLMhnllsULuyVgG1l3m9xSjRJtQSaIZP5jF4LSM+m69Xd7U2qoTczMOaNZ36yWZzxN/OUQMjb2cWeZCLhVPf6zJwA35kC57NK2n1DDvvyFvLnh9gBd8EOkJuT9us1r01Ya3XpFHhXy1fTg9bmWXDMwMm5stnhmGOF2d6Uv4rYGqk67nRzX0ZEGrWW6X0tzeQESkQShx0algKIXeM/2RBfit1QHDHhI70CYTqt1eG05Cpr5u7FdvD4pk8fqfW8xJsmoZisQNQnov0oriUqrB1wZvWL8+calfoX0nxWMVlP37LspA6O2+dlnjFxpDQSjnfWVFyS6fKvr8jXWI6KG6L11J+yAXY4KjqGK+wEnH2yf8tK8NLkIAWNstlUQrycEkk4mP6ElKwkOMpRND0ArG1cG0uMx+VXd1vrWG6UePa+GHmgHbgLSkjI3hpz3wbpE5cbp73dbIgryeC0AeLY7kKDt7pMQpkg3gNxcvTGXjZYc1TQ5siuD1RBJUR5Lv/P8jjyQnB4D67AEuL1pw5acKQ3tfOEF+iuzzzV5zeSj5T5rYR1GpuPOqTz97AWSxawDUsCAwEAAQ==',

    /**
     * Merchant Decryption Private Key used to cryptographically decrypt the response JWE.
     */
    'MerchantDecryptionPrivateKey' => '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',

    /**
     * Input Currency for example USD or NRP.
     */
    'InputCurrency' => 'NPR',

    /**
     * Input 3DS should be Y for production and N for development
     */
    'Input3DS' => 'Y',
];
