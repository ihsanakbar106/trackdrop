import {
    Box,
    Checkbox,
    Scrollable,
    Spinner,
    Text,
    Thumbnail,
} from "@shopify/polaris";
import React from "react";

export function SelectCollection({
    productsLoading,
    collectionsList,
    selectedCollectionsIDs,
    handleCollectionSelect,
}) {
    return (
        <div className="product-lists">
            <Scrollable horizontal vertical className="yr5fA CyBRb">
                {productsLoading ? (
                    <div
                        style={{
                            height: "100%",
                            display: "flex",
                            justifyContent: "center",
                            alignItems: "center",
                        }}
                    >
                        <Spinner size="large" />
                    </div>
                ) : collectionsList?.length ? (
                    collectionsList?.map((collection, i) => {
                        const isSelectedId = selectedCollectionsIDs?.includes(
                            collection.id
                        );
                        return (
                            <div className="product-list-item" key={i}>
                                <Checkbox
                                    labelHidden
                                    checked={isSelectedId}
                                    onChange={() =>
                                        handleCollectionSelect(collection.id)
                                    }
                                />
                                <div className="product-list-item-product-title">
                                    <div className="product-list-item-product-title-inner">
                                        <div className="product-list-item-product-title-thumbnail">
                                            <Thumbnail
                                                source={collection?.image || ""}
                                                size="small"
                                            />
                                        </div>
                                        <div className="product-list-item-product-title-text">
                                            <div className="ExJYf">
                                                <div className="K2zxu">
                                                    <span>
                                                        {collection?.title}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <div
                        style={{
                            height: "100%",
                            display: "flex",
                            justifyContent: "center",
                            alignItems: "center",
                        }}
                    >
                        <Text as="h2" variant="headingMd">
                            No Product Found
                        </Text>
                    </div>
                )}
            </Scrollable>
        </div>
    );
}
