import {
    Box,
    Button,
    Checkbox,
    Divider,
    Icon,
    Scrollable,
    Spinner,
    Text,
    TextField,
    Thumbnail,
} from "@shopify/polaris";
import { SearchIcon } from "@shopify/polaris-icons";
import React from "react";

export function SelectProduct({
    textFieldValue,
    handleTextFieldChange,
    productsLoading,
    productsList,
    selectedProductsIDs,
    handleProductSelect,
    entireStore,
    handleSaveEntireStore,
    handleResetSelection,
}) {
    return (
        <>
            <Box
                paddingBlockStart={3}
                paddingBlockEnd={3}
                paddingInlineStart={4}
                paddingInlineEnd={4}
            >
                <TextField
                    labelHidden
                    type="text"
                    placeholder="Search products"
                    value={textFieldValue}
                    prefix={<Icon source={SearchIcon} tone="base" />}
                    onChange={handleTextFieldChange}
                    autoComplete="off"
                />
            </Box>
            <Divider borderWidth={1} />
            <Box
                paddingBlockStart={3}
                paddingBlockEnd={3}
                paddingInlineStart={4}
                paddingInlineEnd={4}
            >
                <div
                    style={{
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                    }}
                >
                    <Checkbox
                        label="Save Entire Store"
                        checked={entireStore}
                        onChange={handleSaveEntireStore}
                    />
                    {/* <Button size="large" onClick={handleSaveEntireStore}>
                        Save Entire Store
                    </Button> */}
                    {/* <Button size="large" onClick={handleResetSelection}>
                        Reset Selection
                    </Button> */}
                </div>
            </Box>
            <Divider borderWidth={1} />
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
                    ) : productsList?.length ? (
                        productsList?.map((product, i) => {
                            const isSelectedId = selectedProductsIDs?.includes(
                                product.id
                            );
                            return (
                                <div
                                    className="product-list-item"
                                    key={i}
                                    onClick={() =>
                                        handleProductSelect(product.id)
                                    }
                                >
                                    <Checkbox
                                        labelHidden
                                        checked={isSelectedId}
                                        onChange={() =>
                                            handleProductSelect(product.id)
                                        }
                                    />
                                    <div className="product-list-item-product-title">
                                        <div className="product-list-item-product-title-inner">
                                            <div className="product-list-item-product-title-thumbnail">
                                                <Thumbnail
                                                    source={product?.image}
                                                    size="small"
                                                />
                                            </div>
                                            <div className="product-list-item-product-title-text">
                                                <div className="ExJYf">
                                                    <div className="K2zxu">
                                                        <span>
                                                            {product?.title}
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
        </>
    );
}
