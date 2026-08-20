import { Checkbox, Scrollable, Spinner, Text } from "@shopify/polaris";
import React from "react";

export function SelectVendor({
    productsLoading,
    vendorsList,
    selectedVendorsIDs,
    handleVendorSelect,
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
                ) : vendorsList?.length ? (
                    vendorsList?.map((collection, index) => {
                        const isSelectedId =
                            selectedVendorsIDs?.includes(collection);
                        return (
                            <div className="product-list-item" key={index}>
                                <Checkbox
                                    labelHidden
                                    checked={isSelectedId}
                                    onChange={() =>
                                        handleVendorSelect(collection)
                                    }
                                />
                                <div className="product-list-item-product-title">
                                    <div className="product-list-item-product-title-inner">
                                        <div className="product-list-item-product-title-text">
                                            <div className="ExJYf">
                                                <div className="K2zxu">
                                                    <span>{collection}</span>
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
