<?php

declare(strict_types=1);

namespace App\Lib;

use App\Exceptions\ShopifyProductCreatorException;
use App\Services\ShopifyTokenService;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;

class ProductCreator
{
    private const CREATE_PRODUCTS_MUTATION = <<<'QUERY'
    mutation populateProduct($product: ProductCreateInput!) {
        productCreate(product: $product) {
            product {
                id
                variants(first: 1) {
                    nodes {
                        id
                    }
                }
            }
            userErrors {
                field
                message
            }
        }
    }
    QUERY;

    private const UPDATE_VARIANT_PRICE_MUTATION = <<<'QUERY'
    mutation populateProductVariant($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
        productVariantsBulkUpdate(productId: $productId, variants: $variants) {
            productVariants {
                id
            }
            userErrors {
                field
                message
            }
        }
    }
    QUERY;

    public static function call(Session $session, int $count)
    {
        $client = new Graphql(
            $session->getShop(),
            (new ShopifyTokenService())->getValidAccessToken($session->getShop())
        );

        for ($i = 0; $i < $count; $i++) {
            $response = $client->query(
                [
                    "query" => self::CREATE_PRODUCTS_MUTATION,
                    "variables" => [
                        "product" => [
                            "title" => self::randomTitle(),
                        ],
                    ],
                ],
            );

            if ($response->getStatusCode() !== 200) {
                throw new ShopifyProductCreatorException($response->getBody()->__toString(), $response);
            }

            $product = $response->getDecodedBody()['data']['productCreate']['product'] ?? null;
            $variantId = $product['variants']['nodes'][0]['id'] ?? null;
            if ($product && $variantId) {
                $variantResponse = $client->query(
                    [
                        "query" => self::UPDATE_VARIANT_PRICE_MUTATION,
                        "variables" => [
                            "productId" => $product['id'],
                            "variants" => [
                                [
                                    "id" => $variantId,
                                    "price" => (string) self::randomPrice(),
                                ],
                            ],
                        ],
                    ],
                );

                if ($variantResponse->getStatusCode() !== 200) {
                    throw new ShopifyProductCreatorException($variantResponse->getBody()->__toString(), $variantResponse);
                }
            }
        }
    }

    private static function randomTitle()
    {
        $adjective = self::ADJECTIVES[mt_rand(0, count(self::ADJECTIVES) - 1)];
        $noun = self::NOUNS[mt_rand(0, count(self::NOUNS) - 1)];

        return "$adjective $noun";
    }

    private static function randomPrice()
    {

        return (100.0 + mt_rand(0, 1000)) / 100;
    }

    private const ADJECTIVES = [
        "autumn",
        "hidden",
        "bitter",
        "misty",
        "silent",
        "empty",
        "dry",
        "dark",
        "summer",
        "icy",
        "delicate",
        "quiet",
        "white",
        "cool",
        "spring",
        "winter",
        "patient",
        "twilight",
        "dawn",
        "crimson",
        "wispy",
        "weathered",
        "blue",
        "billowing",
        "broken",
        "cold",
        "damp",
        "falling",
        "frosty",
        "green",
        "long",
    ];

    private const NOUNS = [
        "waterfall",
        "river",
        "breeze",
        "moon",
        "rain",
        "wind",
        "sea",
        "morning",
        "snow",
        "lake",
        "sunset",
        "pine",
        "shadow",
        "leaf",
        "dawn",
        "glitter",
        "forest",
        "hill",
        "cloud",
        "meadow",
        "sun",
        "glade",
        "bird",
        "brook",
        "butterfly",
        "bush",
        "dew",
        "dust",
        "field",
        "fire",
        "flower",
    ];
}
