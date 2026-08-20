import { Checkbox, Scrollable, Spinner, Text } from "@shopify/polaris";
import React from "react";

export function SelectProductType({
    productsLoading,
    productTypesList,
    selectedProductTypesIDs,
    handleProductTypeSelect,
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
                ) : productTypesList?.length ? (
                    productTypesList?.map((type, index) => {
                        const isSelectedId =
                            selectedProductTypesIDs?.includes(type);
                        return (
                            <div className="product-list-item" key={index}>
                                <Checkbox
                                    labelHidden
                                    checked={isSelectedId}
                                    onChange={() =>
                                        handleProductTypeSelect(type)
                                    }
                                />
                                <div className="product-list-item-product-title">
                                    <div className="product-list-item-product-title-inner">
                                        <div className="product-list-item-product-title-text">
                                            <div className="ExJYf">
                                                <div className="K2zxu">
                                                    <span>{type}</span>
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
                            No Product Type Found
                        </Text>
                    </div>
                )}
            </Scrollable>
        </div>
    );
}
